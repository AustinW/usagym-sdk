<?php

declare(strict_types=1);

use AustinW\UsaGym\Data\DisciplineData;
use AustinW\UsaGym\Enums\Discipline;

describe('DisciplineData', function () {
    describe('fromArray', function () {
        it('creates from array with all fields', function () {
            $data = [
                'Code' => 'WAG',
                'Name' => 'Women',
                'FullName' => "Women's Artistic",
            ];
            $discipline = DisciplineData::fromArray($data);

            expect($discipline)->toBeInstanceOf(DisciplineData::class);
            expect($discipline->code)->toBe('WAG');
            expect($discipline->name)->toBe('Women');
            expect($discipline->fullName)->toBe("Women's Artistic");
        });

        it('creates all disciplines from fixture', function () {
            $data = loadFixture('disciplines.json');

            expect($data)->toHaveCount(6);

            foreach ($data as $item) {
                $discipline = DisciplineData::fromArray($item);
                expect($discipline)->toBeInstanceOf(DisciplineData::class);
            }
        });

        it('creates WAG discipline from fixture', function () {
            $data = loadFixture('disciplines.json');
            $wag = DisciplineData::fromArray($data[0]);

            expect($wag->code)->toBe('WAG');
            expect($wag->name)->toBe('Women');
            expect($wag->fullName)->toBe("Women's Artistic");
        });

        it('creates MAG discipline from fixture', function () {
            $data = loadFixture('disciplines.json');
            $mag = DisciplineData::fromArray($data[1]);

            expect($mag->code)->toBe('MAG');
            expect($mag->name)->toBe('Men');
            expect($mag->fullName)->toBe("Men's Artistic");
        });

        it('creates Rhythmic discipline from fixture', function () {
            $data = loadFixture('disciplines.json');
            $rg = DisciplineData::fromArray($data[2]);

            expect($rg->code)->toBe('RG');
            expect($rg->name)->toBe('Rhythmic');
            expect($rg->fullName)->toBe('Rhythmic');
        });

        it('creates Acrobatic discipline from fixture', function () {
            $data = loadFixture('disciplines.json');
            $acro = DisciplineData::fromArray($data[3]);

            expect($acro->code)->toBe('ACRO');
            expect($acro->name)->toBe('Acro');
            expect($acro->fullName)->toBe('Acrobatic');
        });

        it('creates Trampoline discipline from fixture', function () {
            $data = loadFixture('disciplines.json');
            $tra = DisciplineData::fromArray($data[4]);

            expect($tra->code)->toBe('TRA');
            expect($tra->name)->toBe('TT');
            expect($tra->fullName)->toBe('Trampoline and Tumbling');
        });

        it('creates GFA discipline from fixture', function () {
            $data = loadFixture('disciplines.json');
            $gfa = DisciplineData::fromArray($data[5]);

            expect($gfa->code)->toBe('GFA');
            expect($gfa->name)->toBe('GFA');
            expect($gfa->fullName)->toBe('Gymnastics for All');
        });
    });

    describe('toEnum', function () {
        it('converts WAG to enum', function () {
            $data = [
                'Code' => 'WAG',
                'Name' => 'Women',
                'FullName' => "Women's Artistic",
            ];
            $discipline = DisciplineData::fromArray($data);

            expect($discipline->toEnum())->toBe(Discipline::WomensArtistic);
            expect($discipline->toEnum()->value)->toBe('WAG');
        });

        it('converts MAG to enum', function () {
            $data = [
                'Code' => 'MAG',
                'Name' => 'Men',
                'FullName' => "Men's Artistic",
            ];
            $discipline = DisciplineData::fromArray($data);

            expect($discipline->toEnum())->toBe(Discipline::MensArtistic);
        });

        it('converts RG to enum', function () {
            $data = [
                'Code' => 'RG',
                'Name' => 'Rhythmic',
                'FullName' => 'Rhythmic',
            ];
            $discipline = DisciplineData::fromArray($data);

            expect($discipline->toEnum())->toBe(Discipline::Rhythmic);
        });

        it('converts ACRO to enum', function () {
            $data = [
                'Code' => 'ACRO',
                'Name' => 'Acro',
                'FullName' => 'Acrobatic',
            ];
            $discipline = DisciplineData::fromArray($data);

            expect($discipline->toEnum())->toBe(Discipline::Acrobatic);
        });

        it('converts TRA to enum', function () {
            $data = [
                'Code' => 'TRA',
                'Name' => 'TT',
                'FullName' => 'Trampoline and Tumbling',
            ];
            $discipline = DisciplineData::fromArray($data);

            expect($discipline->toEnum())->toBe(Discipline::Trampoline);
        });

        it('converts GFA to enum', function () {
            $data = [
                'Code' => 'GFA',
                'Name' => 'GFA',
                'FullName' => 'Gymnastics for All',
            ];
            $discipline = DisciplineData::fromArray($data);

            expect($discipline->toEnum())->toBe(Discipline::GymnasticsForAll);
        });

        it('converts all fixture disciplines to enums', function () {
            $data = loadFixture('disciplines.json');
            $expectedEnums = [
                Discipline::WomensArtistic,
                Discipline::MensArtistic,
                Discipline::Rhythmic,
                Discipline::Acrobatic,
                Discipline::Trampoline,
                Discipline::GymnasticsForAll,
            ];

            foreach ($data as $index => $item) {
                $discipline = DisciplineData::fromArray($item);
                expect($discipline->toEnum())->toBe($expectedEnums[$index]);
            }
        });

        it('returns null for invalid code instead of throwing', function () {
            $data = [
                'Code' => 'INVALID',
                'Name' => 'Invalid',
                'FullName' => 'Invalid Discipline',
            ];
            $discipline = DisciplineData::fromArray($data);

            expect($discipline->toEnum())->toBeNull();
            expect($discipline->code)->toBe('INVALID');
        });
    });

    describe('readonly properties', function () {
        it('has readonly code', function () {
            $reflection = new ReflectionClass(DisciplineData::class);
            $property = $reflection->getProperty('code');

            expect($property->isReadOnly())->toBeTrue();
        });

        it('has readonly name', function () {
            $reflection = new ReflectionClass(DisciplineData::class);
            $property = $reflection->getProperty('name');

            expect($property->isReadOnly())->toBeTrue();
        });

        it('has readonly fullName', function () {
            $reflection = new ReflectionClass(DisciplineData::class);
            $property = $reflection->getProperty('fullName');

            expect($property->isReadOnly())->toBeTrue();
        });
    });

    describe('data consistency', function () {
        it('code matches enum value', function () {
            $data = loadFixture('disciplines.json');

            foreach ($data as $item) {
                $disciplineData = DisciplineData::fromArray($item);
                $enum = $disciplineData->toEnum();

                expect($disciplineData->code)->toBe($enum->value);
            }
        });

        it('name matches enum short name', function () {
            $data = loadFixture('disciplines.json');

            foreach ($data as $item) {
                $disciplineData = DisciplineData::fromArray($item);
                $enum = $disciplineData->toEnum();

                expect($disciplineData->name)->toBe($enum->name());
            }
        });

        it('fullName matches enum full name', function () {
            $data = loadFixture('disciplines.json');

            foreach ($data as $item) {
                $disciplineData = DisciplineData::fromArray($item);
                $enum = $disciplineData->toEnum();

                expect($disciplineData->fullName)->toBe($enum->fullName());
            }
        });
    });
});
