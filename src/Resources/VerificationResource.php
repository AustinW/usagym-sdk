<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Resources;

use AustinW\UsaGym\Concerns\ResolvesDtoOrThrows;

use AustinW\UsaGym\UsaGym;
use AustinW\UsaGym\Data\VerificationResult;
use AustinW\UsaGym\Requests\Verification\PersonVerificationRequest;
use AustinW\UsaGym\Requests\Verification\LegalContactEmailRequest;
use AustinW\UsaGym\Requests\Verification\CoachEmailRequest;

/**
 * Resource for verification operations
 */
class VerificationResource
{
    use ResolvesDtoOrThrows;

    public function __construct(
        protected readonly UsaGym $connector,
        protected readonly int $sanctionId,
    ) {}

    /**
     * Verify athlete(s) for this sanction
     *
     * @param array<string|int> $memberIds Member IDs to verify
     * @return array<VerificationResult>
     */
    public function athletes(array $memberIds): array
    {
        $response = $this->connector->send(
            new PersonVerificationRequest($this->sanctionId, 'athlete', $memberIds)
        );

        return $this->resolveDto($response);
    }

    /**
     * Verify coach(es) for this sanction
     *
     * @param array<string|int> $memberIds Member IDs to verify
     * @return array<VerificationResult>
     */
    public function coaches(array $memberIds): array
    {
        $response = $this->connector->send(
            new PersonVerificationRequest($this->sanctionId, 'coach', $memberIds)
        );

        return $this->resolveDto($response);
    }

    /**
     * Verify judge(s) for this sanction
     *
     * @param array<string|int> $memberIds Member IDs to verify
     * @return array<VerificationResult>
     */
    public function judges(array $memberIds): array
    {
        $response = $this->connector->send(
            new PersonVerificationRequest($this->sanctionId, 'judge', $memberIds)
        );

        return $this->resolveDto($response);
    }

    /**
     * Verify a single athlete by member ID
     *
     * @param string|int $memberId
     * @return VerificationResult|null
     */
    public function athlete(string|int $memberId): ?VerificationResult
    {
        $results = $this->athletes([(string) $memberId]);

        return $results[0] ?? null;
    }

    /**
     * Verify a single coach by member ID
     *
     * @param string|int $memberId
     * @return VerificationResult|null
     */
    public function coach(string|int $memberId): ?VerificationResult
    {
        $results = $this->coaches([(string) $memberId]);

        return $results[0] ?? null;
    }

    /**
     * Verify a single judge by member ID
     *
     * @param string|int $memberId
     * @return VerificationResult|null
     */
    public function judge(string|int $memberId): ?VerificationResult
    {
        $results = $this->judges([(string) $memberId]);

        return $results[0] ?? null;
    }

    /**
     * Verify that an email is a coach's email for a person at this sanction
     *
     * @param string $refType "person" or "group"
     * @param string|int $refTypeId Person ID or Group ID
     * @param string $email Email address to verify
     * @return bool
     */
    public function coachEmail(string $refType, string|int $refTypeId, string $email): bool
    {
        $response = $this->connector->send(
            new CoachEmailRequest($this->sanctionId, $refType, $refTypeId, $email)
        );

        return $this->resolveDto($response);
    }

    /**
     * Verify that an email is a legal contact email for a person
     *
     * Note: This endpoint is not sanction-specific
     *
     * @param string $refType "person" or "group"
     * @param string|int $refTypeId Person ID or Group ID
     * @param string $email Email address to verify
     * @return bool
     */
    public function legalContactEmail(string $refType, string|int $refTypeId, string $email): bool
    {
        $response = $this->connector->send(
            new LegalContactEmailRequest($refType, $refTypeId, $email)
        );

        return $this->resolveDto($response);
    }
}
