<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Data;

use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\MemberType;
use AustinW\UsaGym\Enums\MemberStatus;
use DateTimeImmutable;

/**
 * Coach reservation data from the API
 */
final readonly class CoachReservation
{
    public function __construct(
        public string $orgId,
        public ?string $clubAbbrev,
        public string $clubName,
        public bool $internationalClub,
        public string $memberId,
        public string $lastName,
        public string $firstName,
        public Discipline $discipline,
        public ?MemberType $memberType,
        public bool $internationalMember,
        public ?MemberStatus $status,
        public ?DateTimeImmutable $registrationDate,
        public bool $scratched,
        public ?DateTimeImmutable $scratchDate,
        public ?DateTimeImmutable $modifiedDate,
        public ?string $memberTypeRaw,
        public ?string $statusRaw,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            orgId: (string) $data['OrgID'],
            clubAbbrev: $data['ClubAbbrev'] ?: null,
            clubName: $data['ClubName'],
            internationalClub: (bool) ($data['InternationalClub'] ?? false),
            memberId: (string) $data['MemberID'],
            lastName: $data['LastName'],
            firstName: $data['FirstName'],
            discipline: Discipline::fromApi($data['Discipline']),
            memberType: MemberType::tryFrom($data['MemberType']),
            internationalMember: (bool) ($data['InternationalMember'] ?? false),
            status: MemberStatus::tryFrom($data['Status']),
            registrationDate: self::parseDateTime($data['RegDate'] ?? null),
            scratched: (bool) ($data['Scratched'] ?? false),
            scratchDate: self::parseDateTime($data['ScratchDate'] ?? null),
            modifiedDate: self::parseDateTime($data['ModifiedDate'] ?? null),
            memberTypeRaw: $data['MemberType'],
            statusRaw: $data['Status'],
        );
    }

    /**
     * Get the full name of the coach
     */
    public function fullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    private static function parseDateTime(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
