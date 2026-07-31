<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Resources;

use AustinW\UsaGym\Concerns\ResolvesDtoOrThrows;

use AustinW\UsaGym\UsaGym;
use AustinW\UsaGym\Data\DisciplineData;
use AustinW\UsaGym\Requests\GetDisciplinesRequest;

/**
 * Resource for discipline-related operations
 */
class DisciplineResource
{
    use ResolvesDtoOrThrows;

    public function __construct(
        protected readonly UsaGym $connector,
    ) {}

    /**
     * Get all active disciplines
     *
     * @return array<DisciplineData>
     */
    public function all(): array
    {
        $response = $this->connector->send(new GetDisciplinesRequest());

        return $this->resolveDto($response);
    }
}
