<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';

    /**
     * Create from API response value (case-insensitive)
     */
    public static function fromApi(string $value): self
    {
        return match (strtolower($value)) {
            'male', 'm' => self::Male,
            'female', 'f' => self::Female,
            default => throw new \ValueError("Unknown gender: {$value}"),
        };
    }

    /**
     * Try to create from API response value, returning null on failure
     */
    public static function tryFromApi(string $value): ?self
    {
        try {
            return self::fromApi($value);
        } catch (\ValueError) {
            return null;
        }
    }

    /**
     * Get the display label
     */
    public function label(): string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
        };
    }
}
