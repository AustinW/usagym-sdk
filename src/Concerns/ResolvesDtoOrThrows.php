<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Concerns;

use Saloon\Http\Response;
use AustinW\UsaGym\Exceptions\UsaGymException;

/**
 * Turns a failed response into a typed UsaGymException before any DTO work happens.
 *
 * Saloon's dtoOrFail() reports "Unable to create data transfer object as the response
 * has failed", which names the wrong layer: it reads as a payload-parsing problem when
 * the actual fact is an HTTP error. A live sanction whose judge endpoint returns 500
 * with an empty body cost a consumer a full investigation before the status was even
 * visible — the DTO was never the problem.
 *
 * @internal
 */
trait ResolvesDtoOrThrows
{
    /**
     * @throws UsaGymException when the response failed
     */
    protected function resolveDto(Response $response): mixed
    {
        if ($response->failed()) {
            throw UsaGymException::fromResponse($response);
        }

        return $response->dto();
    }
}
