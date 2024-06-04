<?php

namespace AustinW\Usagym\Actions;

use GuzzleHttp\Pool;
use Illuminate\Support\Arr;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use AustinW\Usagym\Data\ClubReservation;
use AustinW\Usagym\Data\GroupReservation;
use AustinW\Usagym\Data\AthleteReservation;

trait Reservations
{
    public function clubs($sanctionId)
    {
        $response = $this->get("sanction/$sanctionId/reservations/clubs");

        return collect($response['data']['reservations'])->mapInto(ClubReservation::class);
    }

    public function athletes($sanctionId, $clubId)
    {
        $response = $this->get("sanction/$sanctionId/reservations/athlete?clubs=$clubId");

        return collect($response['data']['reservations'])->mapInto(AthleteReservation::class);
    }

    public function groups($sanctionId, $groupIds = [])
    {
        $response = $this->get("sanction/$sanctionId/reservations/group?clubs=".implode(',', Arr::wrap($groupIds)));

        dump($response['data']['reservations']);

        return collect($response['data']['reservations'])->mapInto(GroupReservation::class);
    }

    public function verify($sanctionId, $reservationType, $id)
    {
        $response = $this->get("sanction/$sanctionId/reservations/individual?people=$id");

        return $response['data']['reservations'];
    }

    public function athleteCount($sanctionId, $clubId)
    {
        $athletes = $this->athletes($sanctionId, $clubId);

        return $athletes->unique('MemberID')->count();
    }

    public function pooledAthleteCount($sanctionId)
    {
        $clubs = $this->clubs($sanctionId);

        $requests = function () use ($sanctionId, $clubs) {
            foreach ($clubs as $club) {
                $uri = "sanction/$sanctionId/reservations/athlete?clubs=$club->ClubID";

                yield new Request('GET', $uri);
            }
        };

        $totalAthletes = 0;

        (new Pool(
            $this->guzzle,
            $requests(),
            [
                'concurrency' => 10,
                'fulfilled' => function (ResponseInterface $response) use ($sanctionId, &$totalAthletes) {
                    $json = json_decode($response->getBody(), true);

                    if (! Arr::has($json, 'data.reservations')) {
                        return;
                    }

                    Log::info('Athletes for sanction #'.$sanctionId, $json);

                    $totalAthletes += collect($json['data']['reservations'])->unique('MemberID')->count();
                },
                'rejected' => function ($reason, $index) {
                    dump($reason);
                },
            ]
        ))->promise()->wait();

        return $totalAthletes;
    }
}
