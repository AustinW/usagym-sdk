<?php

declare(strict_types=1);

use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\Apparatus\TrampolineApparatus;

describe('TrampolineApparatus', function () {
    describe('enum cases', function () {
        it('has exactly 3 cases', function () {
            expect(TrampolineApparatus::cases())->toHaveCount(3);
        });

        it('has Trampoline case with correct value', function () {
            expect(TrampolineApparatus::Trampoline->value)->toBe('TR');
        });

        it('has DoubleMini case with correct value', function () {
            expect(TrampolineApparatus::DoubleMini->value)->toBe('DM');
        });

        it('has Tumbling case with correct value', function () {
            expect(TrampolineApparatus::Tumbling->value)->toBe('TU');
        });
    });

    describe('fromApi', function () {
        it('resolves the two-letter codes the reservation endpoints return', function (
            string $value,
            TrampolineApparatus $expected
        ) {
            expect(TrampolineApparatus::fromApi($value))->toBe($expected);
        })->with([
            ['TR', TrampolineApparatus::Trampoline],
            ['DM', TrampolineApparatus::DoubleMini],
            ['TU', TrampolineApparatus::Tumbling],
        ]);

        it('resolves display names', function (string $value, TrampolineApparatus $expected) {
            expect(TrampolineApparatus::fromApi($value))->toBe($expected);
        })->with([
            ['Trampoline', TrampolineApparatus::Trampoline],
            ['Double Mini', TrampolineApparatus::DoubleMini],
            ['Tumbling', TrampolineApparatus::Tumbling],
        ]);

        it('is case-insensitive and tolerates surrounding whitespace', function () {
            expect(TrampolineApparatus::fromApi(' tr '))->toBe(TrampolineApparatus::Trampoline)
                ->and(TrampolineApparatus::fromApi('DOUBLE MINI'))->toBe(TrampolineApparatus::DoubleMini)
                ->and(TrampolineApparatus::fromApi('tumbling'))->toBe(TrampolineApparatus::Tumbling);
        });

        it('throws on an unknown apparatus rather than guessing', function () {
            TrampolineApparatus::fromApi('Vault');
        })->throws(ValueError::class, 'Unknown trampoline apparatus: Vault');

        it('throws on a rhythmic apparatus', function () {
            TrampolineApparatus::fromApi('5 Balls');
        })->throws(ValueError::class);
    });

    describe('tryFromApi', function () {
        it('returns the case for a known value', function () {
            expect(TrampolineApparatus::tryFromApi('DM'))->toBe(TrampolineApparatus::DoubleMini);
        });

        it('returns null for null, empty and whitespace-only values', function (?string $value) {
            expect(TrampolineApparatus::tryFromApi($value))->toBeNull();
        })->with([[null], [''], ['   ']]);

        it('returns null instead of throwing on an unknown value', function () {
            expect(TrampolineApparatus::tryFromApi('Vault'))->toBeNull();
        });
    });

    describe('display helpers', function () {
        it('exposes a display value', function () {
            expect(TrampolineApparatus::DoubleMini->displayValue())->toBe('Double Mini');
        });

        it('exposes a short name', function () {
            expect(TrampolineApparatus::Tumbling->name())->toBe('TUM');
        });

        it('round-trips every case through its own display value', function () {
            foreach (TrampolineApparatus::cases() as $case) {
                expect(TrampolineApparatus::fromApi($case->displayValue()))->toBe($case);
            }
        });
    });

    describe('Discipline::apparatusEnumClass', function () {
        it('returns the trampoline apparatus enum for the trampoline discipline', function () {
            expect(Discipline::Trampoline->apparatusEnumClass())
                ->toBe(TrampolineApparatus::class);
        });

        it('returns null for disciplines whose apparatus vocabulary is not a closed set', function (
            Discipline $discipline
        ) {
            expect($discipline->apparatusEnumClass())->toBeNull();
        })->with([
            [Discipline::WomensArtistic],
            [Discipline::MensArtistic],
            [Discipline::Rhythmic],
            [Discipline::Acrobatic],
            [Discipline::GymnasticsForAll],
        ]);
    });
});
