<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Resources;

use AustinW\UsaGym\Concerns\ResolvesDtoOrThrows;

use DateTimeInterface;
use AustinW\UsaGym\UsaGym;
use AustinW\UsaGym\Requests\Person\PersonExistsRequest;

/**
 * Resource for person-related operations
 */
class PersonResource
{
    use ResolvesDtoOrThrows;

    public function __construct(
        protected readonly UsaGym $connector,
    ) {}

    /**
     * Verify a person exists with the given credentials
     *
     * @param string $memberId USA Gymnastics Member ID
     * @param string $lastName Member's last name
     * @param string|DateTimeInterface $dateOfBirth Date of birth (YYYY-MM-DD or DateTime)
     * @return bool True if the person exists
     */
    public function exists(
        string $memberId,
        string $lastName,
        string|DateTimeInterface $dateOfBirth,
    ): bool {
        $response = $this->connector->send(
            new PersonExistsRequest($memberId, $lastName, $dateOfBirth)
        );

        return $this->resolveDto($response);
    }
}
