<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Data;

use AustinW\UsaGym\Enums\Discipline;

/**
 * Discipline data from the API
 */
final readonly class DisciplineData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $fullName,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['Code'],
            name: $data['Name'],
            fullName: $data['FullName'],
        );
    }

    /**
     * Get the corresponding Discipline enum, or null if the API returned a
     * code this SDK doesn't model.
     */
    public function toEnum(): ?Discipline
    {
        return Discipline::tryFrom($this->code);
    }
}
