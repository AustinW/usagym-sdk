<?php

declare(strict_types=1);

use AustinW\UsaGym\Enums\Gender;

describe('Gender', function () {
    describe('enum cases', function () {
        it('has exactly 2 cases', function () {
            expect(Gender::cases())->toHaveCount(2);
        });

        it('has Male case with correct value', function () {
            expect(Gender::Male->value)->toBe('male');
        });

        it('has Female case with correct value', function () {
            expect(Gender::Female->value)->toBe('female');
        });
    });

    describe('fromApi()', function () {
        it('parses male', function () {
            expect(Gender::fromApi('male'))->toBe(Gender::Male);
        });

        it('parses female', function () {
            expect(Gender::fromApi('female'))->toBe(Gender::Female);
        });

        it('parses Male (capitalized)', function () {
            expect(Gender::fromApi('Male'))->toBe(Gender::Male);
        });

        it('parses Female (capitalized)', function () {
            expect(Gender::fromApi('Female'))->toBe(Gender::Female);
        });

        it('parses MALE (uppercase)', function () {
            expect(Gender::fromApi('MALE'))->toBe(Gender::Male);
        });

        it('parses FEMALE (uppercase)', function () {
            expect(Gender::fromApi('FEMALE'))->toBe(Gender::Female);
        });

        it('parses m shorthand', function () {
            expect(Gender::fromApi('m'))->toBe(Gender::Male);
        });

        it('parses f shorthand', function () {
            expect(Gender::fromApi('f'))->toBe(Gender::Female);
        });

        it('parses M shorthand (uppercase)', function () {
            expect(Gender::fromApi('M'))->toBe(Gender::Male);
        });

        it('parses F shorthand (uppercase)', function () {
            expect(Gender::fromApi('F'))->toBe(Gender::Female);
        });

        it('throws ValueError for invalid value', function () {
            expect(fn () => Gender::fromApi('invalid'))->toThrow(ValueError::class);
        });
    });

    describe('tryFromApi()', function () {
        it('returns Male for male', function () {
            expect(Gender::tryFromApi('male'))->toBe(Gender::Male);
        });

        it('returns Female for female', function () {
            expect(Gender::tryFromApi('female'))->toBe(Gender::Female);
        });

        it('returns null for invalid value', function () {
            expect(Gender::tryFromApi('invalid'))->toBeNull();
        });
    });

    describe('label()', function () {
        it('returns Male for Male case', function () {
            expect(Gender::Male->label())->toBe('Male');
        });

        it('returns Female for Female case', function () {
            expect(Gender::Female->label())->toBe('Female');
        });
    });

    describe('backed enum functionality', function () {
        it('can be created from value using tryFrom', function () {
            expect(Gender::tryFrom('male'))->toBe(Gender::Male);
            expect(Gender::tryFrom('female'))->toBe(Gender::Female);
        });

        it('returns null for invalid value using tryFrom', function () {
            expect(Gender::tryFrom('invalid'))->toBeNull();
        });

        it('is case-sensitive for value matching with tryFrom', function () {
            expect(Gender::tryFrom('Male'))->toBeNull();
            expect(Gender::tryFrom('MALE'))->toBeNull();
        });
    });
});
