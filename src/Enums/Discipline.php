<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Enums;

enum Discipline: string
{
    case WomensArtistic = 'WAG';
    case MensArtistic = 'MAG';
    case Rhythmic = 'RG';
    case Acrobatic = 'ACRO';
    case Trampoline = 'TRA';
    case GymnasticsForAll = 'GFA';

    /**
     * Get the short display name.
     */
    public function name(): string
    {
        return match ($this) {
            self::WomensArtistic => 'Women',
            self::MensArtistic => 'Men',
            self::Rhythmic => 'Rhythmic',
            self::Acrobatic => 'Acro',
            self::Trampoline => 'TT',
            self::GymnasticsForAll => 'GFA',
        };
    }

    /**
     * Get the full display name.
     */
    public function fullName(): string
    {
        return match ($this) {
            self::WomensArtistic => "Women's Artistic",
            self::MensArtistic => "Men's Artistic",
            self::Rhythmic => 'Rhythmic',
            self::Acrobatic => 'Acrobatic',
            self::Trampoline => 'Trampoline and Tumbling',
            self::GymnasticsForAll => 'Gymnastics for All',
        };
    }

    /**
     * Create from API response value (handles both code and display name).
     */
    public static function fromApi(string $value): self
    {
        // First try direct code match
        $discipline = self::tryFrom($value);
        if ($discipline !== null) {
            return $discipline;
        }

        // Try matching by display name
        return match (strtolower($value)) {
            'women', "women's artistic", 'wag' => self::WomensArtistic,
            'men', "men's artistic", 'mag' => self::MensArtistic,
            'rhythmic', 'rg' => self::Rhythmic,
            'acro', 'acrobatic', 'acrobatics' => self::Acrobatic,
            'tt', 'trampoline', 'trampoline and tumbling', 'tra' => self::Trampoline,
            'gfa', 'gymnastics for all' => self::GymnasticsForAll,
            default => throw new \ValueError("Unknown discipline: {$value}"),
        };
    }

    /**
     * Get the level enum class for this discipline.
     */
    public function levelEnumClass(): string
    {
        return match ($this) {
            self::WomensArtistic => Levels\WomensArtisticLevel::class,
            self::MensArtistic => Levels\MensArtisticLevel::class,
            self::Rhythmic => Levels\RhythmicLevel::class,
            self::Acrobatic => Levels\AcrobaticLevel::class,
            self::Trampoline => Levels\TrampolineLevel::class,
            self::GymnasticsForAll => Levels\GfaLevel::class,
        };
    }

    /**
     * Get the apparatus enum class for this discipline, or null where the API's
     * apparatus vocabulary is not a closed set we can model.
     *
     * @return class-string|null
     */
    public function apparatusEnumClass(): ?string
    {
        return match ($this) {
            self::Trampoline => Apparatus\TrampolineApparatus::class,
            default => null,
        };
    }
}
