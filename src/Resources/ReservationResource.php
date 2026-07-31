<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Resources;

use AustinW\UsaGym\Concerns\ResolvesDtoOrThrows;

use BackedEnum;
use Saloon\Http\Pool;
use AustinW\UsaGym\UsaGym;
use AustinW\UsaGym\Data\AthleteReservation;
use AustinW\UsaGym\Data\CoachReservation;
use AustinW\UsaGym\Data\JudgeReservation;
use AustinW\UsaGym\Data\ClubReservation;
use AustinW\UsaGym\Data\GroupReservation;
use AustinW\UsaGym\Requests\Reservations\GetAthletesRequest;
use AustinW\UsaGym\Requests\Reservations\GetCoachesRequest;
use AustinW\UsaGym\Requests\Reservations\GetJudgesRequest;
use AustinW\UsaGym\Requests\Reservations\GetClubsRequest;
use AustinW\UsaGym\Requests\Reservations\GetGroupsRequest;
use AustinW\UsaGym\Requests\Reservations\GetIndividualsRequest;

/**
 * Resource for reservation operations
 */
class ReservationResource
{
    use ResolvesDtoOrThrows;

    public function __construct(
        protected readonly UsaGym $connector,
        protected readonly int $sanctionId,
    ) {}

    /**
     * Get athlete reservations
     *
     * @param array<int>|null $clubs Filter by club IDs
     * @param array<BackedEnum|string>|null $levels Filter by level codes
     * @return array<AthleteReservation>
     */
    public function athletes(?array $clubs = null, ?array $levels = null): array
    {
        $response = $this->connector->send(
            new GetAthletesRequest($this->sanctionId, $clubs, $levels)
        );

        return $this->resolveDto($response);
    }

    /**
     * Get coach reservations
     *
     * @param array<int>|null $clubs Filter by club IDs
     * @return array<CoachReservation>
     */
    public function coaches(?array $clubs = null): array
    {
        $response = $this->connector->send(
            new GetCoachesRequest($this->sanctionId, $clubs)
        );

        return $this->resolveDto($response);
    }

    /**
     * Get judge reservations
     *
     * @return array<JudgeReservation>
     */
    public function judges(): array
    {
        $response = $this->connector->send(
            new GetJudgesRequest($this->sanctionId)
        );

        return $this->resolveDto($response);
    }

    /**
     * Get club reservations
     *
     * @param array<int>|null $clubs Filter by specific club IDs
     * @return array<ClubReservation>
     */
    public function clubs(?array $clubs = null): array
    {
        $response = $this->connector->send(
            new GetClubsRequest($this->sanctionId, $clubs)
        );

        // Some sanction types (e.g., T&T meets) don't have club endpoints
        // Return empty array for 404s instead of throwing an exception
        if ($response->status() === 404) {
            return [];
        }

        return $this->resolveDto($response);
    }

    /**
     * Get group reservations (for Rhythmic, Acro, T&T, GFA)
     *
     * @param array<int>|null $clubs Filter by club IDs
     * @param array<BackedEnum|string>|null $levels Filter by level codes
     * @return array<GroupReservation>
     */
    public function groups(?array $clubs = null, ?array $levels = null): array
    {
        $response = $this->connector->send(
            new GetGroupsRequest($this->sanctionId, $clubs, $levels)
        );

        return $this->resolveDto($response);
    }

    /**
     * Get all individuals (athletes and coaches)
     *
     * @param array<int>|null $clubs Filter by club IDs
     * @param array<BackedEnum|string>|null $levels Filter by level codes
     * @return array{athletes: array<AthleteReservation>, coaches: array<CoachReservation>}
     */
    public function individuals(?array $clubs = null, ?array $levels = null): array
    {
        $response = $this->connector->send(
            new GetIndividualsRequest($this->sanctionId, $clubs, $levels)
        );

        return $this->resolveDto($response);
    }

    /**
     * Get athlete count for a specific club
     *
     * @param int $clubId
     * @return int
     */
    public function athleteCount(int $clubId): int
    {
        $athletes = $this->athletes(clubs: [$clubId]);

        // Count unique member IDs
        $uniqueIds = array_unique(array_map(
            fn(AthleteReservation $a) => $a->memberId,
            $athletes
        ));

        return count($uniqueIds);
    }

    /**
     * Fetch all athletes concurrently across all clubs
     *
     * @param int $concurrency Maximum concurrent requests
     * @return array<AthleteReservation>
     */
    public function allAthletesConcurrently(int $concurrency = 10): array
    {
        $clubs = $this->clubs();

        if (empty($clubs)) {
            return [];
        }

        // Create request generator
        $requests = function () use ($clubs) {
            foreach ($clubs as $club) {
                yield new GetAthletesRequest($this->sanctionId, [(int) $club->clubId]);
            }
        };

        $allAthletes = [];

        // Use Saloon's pool for concurrent requests
        $pool = $this->connector->pool(
            requests: $requests(),
            concurrency: $concurrency,
            responseHandler: function ($response) use (&$allAthletes) {
                $athletes = $this->resolveDto($response);
                array_push($allAthletes, ...$athletes);
            },
            exceptionHandler: function ($exception, $request) {
                // Log or handle exceptions as needed
                throw $exception;
            },
        );

        $pool->send()->wait();

        return $allAthletes;
    }

    /**
     * Get total athlete count across all clubs using concurrent requests
     *
     * @param int $concurrency Maximum concurrent requests
     * @return int
     */
    public function totalAthleteCount(int $concurrency = 10): int
    {
        $athletes = $this->allAthletesConcurrently($concurrency);

        // Count unique member IDs
        $uniqueIds = array_unique(array_map(
            fn(AthleteReservation $a) => $a->memberId,
            $athletes
        ));

        return count($uniqueIds);
    }
}
