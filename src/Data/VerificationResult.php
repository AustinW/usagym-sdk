<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Data;

use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\MemberType;
use AustinW\UsaGym\Enums\MemberStatus;

/**
 * Person verification result from the API
 */
final readonly class VerificationResult
{
    /**
     * @param array<string> $clubIds
     * @param array<string|null> $clubAbbrevs
     * @param array<string> $clubNames
     * @param array<string> $clubStatuses
     * @param array<string> $internationalClubs
     * @param array<string> $disciplines
     * @param array<string>|null $certificationLevels
     */
    public function __construct(
        public string $memberId,
        public string $lastName,
        public string $firstName,
        public ?string $dateOfBirth,
        public ?bool $usCitizen,
        public array $clubIds,
        public array $clubAbbrevs,
        public array $clubNames,
        public array $clubStatuses,
        public array $internationalClubs,
        public ?MemberType $memberType,
        public array $disciplines,
        public ?string $level,
        public bool $internationalMember,
        public bool $eligible,
        public ?string $ineligibleReason,
        public ?bool $certificationValid,
        public ?array $certificationLevels,
        public ?string $memberTypeRaw,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            memberId: (string) $data['MemberID'],
            lastName: $data['LastName'],
            firstName: $data['FirstName'],
            dateOfBirth: $data['DOB'] ?? null,
            usCitizen: isset($data['USCitizen']) ? $data['USCitizen'] === 'Yes' : null,
            clubIds: $data['ClubID'] ?? [],
            clubAbbrevs: $data['ClubAbbrev'] ?? [],
            clubNames: $data['ClubName'] ?? [],
            clubStatuses: $data['ClubStatus'] ?? [],
            internationalClubs: $data['InternationalClub'] ?? [],
            memberType: MemberType::tryFrom($data['MemberType']),
            disciplines: $data['Discipline'] ?? [],
            level: $data['Level'] ?? null,
            internationalMember: ($data['InternationalMember'] ?? 'No') === 'Yes',
            eligible: (bool) ($data['Eligible'] ?? false),
            ineligibleReason: $data['IneligibleReason'] ?? null,
            certificationValid: isset($data['Certification']['valid'])
                ? (bool) $data['Certification']['valid']
                : null,
            certificationLevels: $data['Certification']['levels'] ?? null,
            memberTypeRaw: $data['MemberType'],
        );
    }

    /**
     * Get the full name
     */
    public function fullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    /**
     * Get the primary club ID (first in the array)
     */
    public function primaryClubId(): ?string
    {
        return $this->clubIds[0] ?? null;
    }

    /**
     * Get the primary club name
     */
    public function primaryClubName(): ?string
    {
        return $this->clubNames[0] ?? null;
    }

    /**
     * Check if the member is eligible to participate
     */
    public function canParticipate(): bool
    {
        return $this->eligible;
    }
}
