<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Enums\Apparatus;

/**
 * Apparatus contested under the Trampoline & Tumbling discipline.
 *
 * The API returns the two-letter code for these on reservation endpoints. Consumers
 * should never match on the raw string: use {@see self::fromApi()}, which also accepts
 * the display form the API uses elsewhere.
 */
enum TrampolineApparatus: string
{
    case Trampoline = 'TR';
    case DoubleMini = 'DM';
    case Tumbling = 'TU';

    /**
     * Get the display value returned by the API.
     */
    public function displayValue(): string
    {
        return match ($this) {
            self::Trampoline => 'Trampoline',
            self::DoubleMini => 'Double Mini',
            self::Tumbling => 'Tumbling',
        };
    }

    /**
     * Get the short display name.
     */
    public function name(): string
    {
        return match ($this) {
            self::Trampoline => 'TRA',
            self::DoubleMini => 'DMT',
            self::Tumbling => 'TUM',
        };
    }

    /**
     * Create from API response value (handles both code and display name).
     *
     * @throws \ValueError when the value is not a recognised trampoline apparatus
     */
    public static function fromApi(string $value): self
    {
        $trimmed = trim($value);

        // First try direct code match
        $apparatus = self::tryFrom(strtoupper($trimmed));
        if ($apparatus !== null) {
            return $apparatus;
        }

        // Try matching by display name
        return match (strtolower($trimmed)) {
            'trampoline', 'tra', 'tramp' => self::Trampoline,
            'double mini', 'double-mini', 'doublemini', 'dmt', 'double mini trampoline' => self::DoubleMini,
            'tumbling', 'tum', 'tumble' => self::Tumbling,
            default => throw new \ValueError("Unknown trampoline apparatus: {$value}"),
        };
    }

    /**
     * Create from an API response value, returning null instead of throwing.
     */
    public static function tryFromApi(?string $value): ?self
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return self::fromApi($value);
        } catch (\ValueError) {
            return null;
        }
    }
}
