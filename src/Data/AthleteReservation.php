<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Data;

use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\Gender;
use AustinW\UsaGym\Enums\MemberType;
use AustinW\UsaGym\Enums\MemberStatus;
use DateTimeImmutable;

/**
 * Athlete reservation data from the API
 */
final readonly class AthleteReservation
{
    public function __construct(
        public string $orgId,
        public ?string $clubAbbrev,
        public string $clubName,
        public ?Gender $gender,
        public bool $internationalClub,
        public string $memberId,
        public string $lastName,
        public string $firstName,
        public ?DateTimeImmutable $dateOfBirth,
        public Discipline $discipline,
        public MemberType $memberType,
        public bool $internationalMember,
        public MemberStatus $status,
        public ?DateTimeImmutable $registrationDate,
        public ?string $apparatus,
        public string $level,
        public ?string $ageGroup,
        public bool $scratched,
        public ?DateTimeImmutable $scratchDate,
        public ?DateTimeImmutable $modifiedDate,
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
            gender: isset($data['Gender']) ? Gender::tryFromApi($data['Gender']) : null,
            internationalClub: (bool) ($data['InternationalClub'] ?? false),
            memberId: (string) $data['MemberID'],
            lastName: $data['LastName'],
            firstName: $data['FirstName'],
            dateOfBirth: self::parseDate($data['DOB'] ?? null, 'm/d/Y'),
            discipline: Discipline::fromApi($data['Discipline']),
            memberType: MemberType::from($data['MemberType']),
            internationalMember: (bool) ($data['InternationalMember'] ?? false),
            status: MemberStatus::from($data['Status']),
            registrationDate: self::parseDateTime($data['RegDate'] ?? null),
            apparatus: $data['Apparatus'] ?? null,
            level: $data['Level'],
            ageGroup: $data['AgeGroup'] ?? null,
            scratched: (bool) ($data['Scratched'] ?? false),
            scratchDate: self::parseDateTime($data['ScratchDate'] ?? null),
            modifiedDate: self::parseDateTime($data['ModifiedDate'] ?? null),
        );
    }

    /**
     * Get the full name of the athlete
     */
    public function fullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    /**
     * Check if the athlete can compete
     */
    public function canCompete(): bool
    {
        return $this->status->canParticipate() && !$this->scratched;
    }

    private static function parseDate(?string $value, string $format = 'Y-m-d'): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat($format, $value);
        return $date ?: null;
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
