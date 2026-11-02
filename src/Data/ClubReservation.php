<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Data;

/**
 * Club reservation data from the API
 */
final readonly class ClubReservation
{
    public function __construct(
        public string $clubId,
        public ?string $clubAbbrev,
        public string $clubName,
        public ?string $clubCity,
        public ?string $clubState,
        public ?string $clubContactId,
        public ?string $clubContactName,
        public ?string $clubContactEmail,
        public ?string $clubContactPhone,
        public ?string $meetContactId,
        public ?string $meetContactName,
        public ?string $meetContactEmail,
        public ?string $meetContactPhone,
        public bool $internationalClub,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $clubContactName = $data['ClubContactName'] ?? $data['ClubContact'] ?? null;

        return new self(
            clubId: (string) $data['ClubID'],
            clubAbbrev: self::nullableString($data['ClubAbbrev'] ?? null),
            clubName: (string) $data['ClubName'],
            clubCity: self::nullableString($data['ClubCity'] ?? null),
            clubState: self::nullableString($data['ClubState'] ?? null),
            clubContactId: self::nullableString($data['ClubContactID'] ?? null),
            clubContactName: is_scalar($clubContactName) ? (string) $clubContactName : null,
            clubContactEmail: self::nullableString($data['ClubContactEmail'] ?? null),
            clubContactPhone: self::nullableString($data['ClubContactPhone'] ?? null),
            meetContactId: self::nullableString($data['MeetContactID'] ?? null),
            meetContactName: self::nullableString($data['MeetContactName'] ?? null),
            meetContactEmail: self::nullableString($data['MeetContactEmail'] ?? null),
            meetContactPhone: self::nullableString($data['MeetContactPhone'] ?? null),
            internationalClub: (bool) ($data['InternationalClub'] ?? false),
        );
    }

    /**
     * USAG returns IDs and phone numbers as either strings or JSON numbers, so cast any
     * scalar to string. Empty values ('', null, 0) stay null, as before.
     */
    private static function nullableString(mixed $value): ?string
    {
        return $value && is_scalar($value) ? (string) $value : null;
    }

    /**
     * Get the display name for the club
     */
    public function displayName(): string
    {
        return $this->clubAbbrev ?? $this->clubName;
    }

    /**
     * Get the full location string
     */
    public function location(): ?string
    {
        if ($this->clubCity && $this->clubState) {
            return "{$this->clubCity}, {$this->clubState}";
        }

        return $this->clubCity ?? $this->clubState;
    }
}
