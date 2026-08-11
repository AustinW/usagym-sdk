<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Data;

use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\MemberStatus;
use DateTimeImmutable;

/**
 * Group/Pair reservation data from the API
 */
final readonly class GroupReservation
{
    /**
     * @param array<GroupAthlete> $athletes
     */
    public function __construct(
        public string $orgId,
        public ?string $clubAbbrev,
        public string $clubName,
        public bool $internationalClub,
        public string $groupId,
        public string $groupName,
        public string $groupType,
        public Discipline $discipline,
        public ?MemberStatus $status,
        public ?DateTimeImmutable $registrationDate,
        public ?string $apparatus,
        public string $level,
        public ?string $ageGroup,
        public array $athletes,
        public bool $scratched,
        public ?DateTimeImmutable $scratchDate,
        public ?DateTimeImmutable $modifiedDate,
        public ?string $statusRaw,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $athletes = array_map(
            fn(array $athlete) => GroupAthlete::fromArray($athlete),
            $data['Athletes'] ?? []
        );

        return new self(
            orgId: (string) $data['OrgID'],
            clubAbbrev: $data['ClubAbbrev'] ?: null,
            clubName: $data['ClubName'],
            internationalClub: (bool) ($data['InternationalClub'] ?? false),
            groupId: (string) $data['GroupID'],
            groupName: $data['GroupName'],
            groupType: $data['GroupType'],
            discipline: Discipline::fromApi($data['Discipline']),
            status: MemberStatus::tryFrom($data['Status']),
            registrationDate: self::parseDateTime($data['RegDate'] ?? null),
            apparatus: $data['Apparatus'] ?? null,
            level: $data['Level'],
            ageGroup: $data['AgeGroup'] ?? null,
            athletes: $athletes,
            scratched: (bool) ($data['Scratched'] ?? false),
            scratchDate: self::parseDateTime($data['ScratchDate'] ?? null),
            modifiedDate: self::parseDateTime($data['ModifiedDate'] ?? null),
            statusRaw: $data['Status'],
        );
    }

    /**
     * Get the number of athletes in the group
     */
    public function athleteCount(): int
    {
        return count($this->athletes);
    }

    /**
     * Check if the group can compete
     */
    public function canCompete(): bool
    {
        return ($this->status?->canParticipate() ?? false) && !$this->scratched;
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
