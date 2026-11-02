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
        return new self(
            clubId: (string) $data['ClubID'],
            clubAbbrev: ($data['ClubAbbrev'] ?? null) ?: null,
            clubName: $data['ClubName'],
            clubCity: ($data['ClubCity'] ?? null) ?: null,
            clubState: ($data['ClubState'] ?? null) ?: null,
            clubContactId: self::nullableId($data['ClubContactID'] ?? null),
            clubContactName: $data['ClubContactName'] ?? $data['ClubContact'] ?? null,
            clubContactEmail: ($data['ClubContactEmail'] ?? null) ?: null,
            clubContactPhone: ($data['ClubContactPhone'] ?? null) ?: null,
            meetContactId: self::nullableId($data['MeetContactID'] ?? null),
            meetContactName: ($data['MeetContactName'] ?? null) ?: null,
            meetContactEmail: ($data['MeetContactEmail'] ?? null) ?: null,
            meetContactPhone: ($data['MeetContactPhone'] ?? null) ?: null,
            internationalClub: (bool) ($data['InternationalClub'] ?? false),
        );
    }

    /**
     * USAG returns contact IDs as either strings or integers depending on the endpoint.
     */
    private static function nullableId(mixed $value): ?string
    {
        return $value ? (string) $value : null;
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
