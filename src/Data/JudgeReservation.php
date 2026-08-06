<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Data;

use DateTimeImmutable;
use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\MemberType;
use AustinW\UsaGym\Enums\MemberStatus;

/**
 * Judge reservation data from the API.
 */
final readonly class JudgeReservation
{
    /**
     * @param  array<string>  $certifications
     */
    public function __construct(
        public string $memberId,
        public string $lastName,
        public string $firstName,
        public Discipline $discipline,
        public MemberType $memberType,
        public bool $internationalMember,
        public MemberStatus $status,
        public ?DateTimeImmutable $registrationDate,
        public string $level,
        public bool $scratched,
        public ?DateTimeImmutable $scratchDate,
        public ?DateTimeImmutable $modifiedDate,
        public array $certifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            memberId: (string) $data['MemberID'],
            lastName: $data['LastName'],
            firstName: $data['FirstName'],
            discipline: Discipline::fromApi($data['Discipline']),
            memberType: MemberType::from($data['MemberType']),
            internationalMember: (bool) ($data['InternationalMember'] ?? false),
            status: MemberStatus::from($data['Status']),
            registrationDate: self::parseDateTime($data['RegDate'] ?? null),
            level: $data['Level'] ?? 'Judge',
            scratched: (bool) ($data['Scratched'] ?? false),
            scratchDate: self::parseDateTime($data['ScratchDate'] ?? null),
            modifiedDate: self::parseDateTime($data['ModifiedDate'] ?? null),
            certifications: self::parseCertifications($data['Certification'] ?? []),
        );
    }

    /**
     * Normalise the `Certification` array so the declared `array<string>` type is true at
     * runtime.
     *
     * The API sends this key in three observed shapes: absent, a list of codes, and — on
     * live T&T sanctions — `[null, null, null]`. Passing that third shape through
     * unfiltered made the declared type a lie, with two distinct consequences:
     *
     *  1. `count($judge->certifications)` reported 3 for a judge holding none, so the
     *     obvious "does this judge hold any certifications?" check silently returned the
     *     wrong answer rather than failing.
     *  2. Any consumer mapping the array through a `string`-typed callable fataled with a
     *     TypeError — including for a PARTIALLY populated array such as `['TRC2', null]`,
     *     so this would not have resolved itself as upstream data improved.
     *
     * Static analysers trust the docblock, so they actively blessed both. Filtering here,
     * where the type is claimed, is the only place that fixes it for every consumer.
     *
     * @return array<string>
     */
    private static function parseCertifications(mixed $certifications): array
    {
        if (! is_array($certifications)) {
            return [];
        }

        return array_values(array_filter(
            $certifications,
            static fn (mixed $code): bool => is_string($code) && trim($code) !== '',
        ));
    }

    /**
     * Get the full name of the judge.
     */
    public function fullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    /**
     * Check if the judge has a specific certification.
     */
    public function hasCertification(string $code): bool
    {
        return in_array($code, $this->certifications, true);
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
