<?php

declare(strict_types=1);

use AustinW\UsaGym\Data\AthleteReservation;
use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\Gender;
use AustinW\UsaGym\Enums\MemberStatus;
use AustinW\UsaGym\Enums\MemberType;

describe('AthleteReservation', function () {
    describe('fromArray', function () {
        it('creates from array with all fields', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete)->toBeInstanceOf(AthleteReservation::class);
            expect($athlete->orgId)->toBe('12345');
            expect($athlete->clubAbbrev)->toBe('ABC');
            expect($athlete->clubName)->toBe('ABC Gymnastics');
            expect($athlete->internationalClub)->toBeFalse();
            expect($athlete->memberId)->toBe('987654');
            expect($athlete->lastName)->toBe('Smith');
            expect($athlete->firstName)->toBe('Jane');
            expect($athlete->level)->toBe('WLEVEL04');
            expect($athlete->ageGroup)->toBe('Junior');
            expect($athlete->scratched)->toBeFalse();
            expect($athlete->apparatus)->toBeNull();
            expect($athlete->internationalMember)->toBeFalse();
        });

        it('parses date of birth correctly', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->dateOfBirth)->toBeInstanceOf(DateTimeImmutable::class);
            expect($athlete->dateOfBirth->format('m/d/Y'))->toBe('05/15/2010');
            expect($athlete->dateOfBirth->format('Y-m-d'))->toBe('2010-05-15');
        });

        it('parses registration date correctly', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->registrationDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($athlete->registrationDate->format('Y-m-d'))->toBe('2024-01-15');
        });

        it('parses modified date correctly', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->modifiedDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($athlete->modifiedDate->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        it('handles null scratch date', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->scratchDate)->toBeNull();
        });

        it('handles null club abbreviation', function () {
            $data = loadFixture('athlete.json');
            $data['ClubAbbrev'] = '';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->clubAbbrev)->toBeNull();
        });

        it('handles null date of birth', function () {
            $data = loadFixture('athlete.json');
            unset($data['DOB']);
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->dateOfBirth)->toBeNull();
        });

        it('handles empty date of birth string', function () {
            $data = loadFixture('athlete.json');
            $data['DOB'] = '';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->dateOfBirth)->toBeNull();
        });
    });

    describe('enum mapping', function () {
        it('maps discipline correctly', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->discipline)->toBe(Discipline::WomensArtistic);
            expect($athlete->discipline->value)->toBe('WAG');
        });

        it('maps member type correctly', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->memberType)->toBe(MemberType::Athlete);
            expect($athlete->memberType->value)->toBe('ATHL');
        });

        it('maps member status correctly', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->status)->toBe(MemberStatus::Active);
            expect($athlete->status->value)->toBe('Active');
        });

        it('handles discipline from display name', function () {
            $data = loadFixture('athlete.json');
            $data['Discipline'] = 'Women';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->discipline)->toBe(Discipline::WomensArtistic);
        });

        it('handles discipline from code', function () {
            $data = loadFixture('athlete.json');
            $data['Discipline'] = 'MAG';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->discipline)->toBe(Discipline::MensArtistic);
        });

        it('maps gender correctly', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->gender)->toBe(Gender::Female);
            expect($athlete->gender->value)->toBe('female');
        });

        it('handles male gender', function () {
            $data = loadFixture('athlete.json');
            $data['Gender'] = 'male';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->gender)->toBe(Gender::Male);
        });

        it('handles gender case insensitively', function () {
            $data = loadFixture('athlete.json');
            $data['Gender'] = 'FEMALE';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->gender)->toBe(Gender::Female);
        });

        it('handles null gender', function () {
            $data = loadFixture('athlete.json');
            unset($data['Gender']);
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->gender)->toBeNull();
        });
    });

    describe('fullName', function () {
        it('returns full name', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->fullName())->toBe('Jane Smith');
        });

        it('returns full name with different names', function () {
            $data = loadFixture('athlete.json');
            $data['FirstName'] = 'Alice';
            $data['LastName'] = 'Johnson';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->fullName())->toBe('Alice Johnson');
        });
    });

    describe('canCompete', function () {
        it('returns true for active non-scratched athlete', function () {
            $data = loadFixture('athlete.json');
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->canCompete())->toBeTrue();
        });

        it('returns false when scratched', function () {
            $data = loadFixture('athlete.json');
            $data['Scratched'] = true;
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->canCompete())->toBeFalse();
        });

        it('returns false when status is expired', function () {
            $data = loadFixture('athlete.json');
            $data['Status'] = 'Expired';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->canCompete())->toBeFalse();
        });

        it('returns false when status is banned', function () {
            $data = loadFixture('athlete.json');
            $data['Status'] = 'Banned';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->canCompete())->toBeFalse();
        });

        it('returns false when status is suspended', function () {
            $data = loadFixture('athlete.json');
            $data['Status'] = 'Suspended';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->canCompete())->toBeFalse();
        });

        it('returns true when status is pending', function () {
            $data = loadFixture('athlete.json');
            $data['Status'] = 'Pending';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->canCompete())->toBeTrue();
        });

        it('returns false when scratched and pending', function () {
            $data = loadFixture('athlete.json');
            $data['Status'] = 'Pending';
            $data['Scratched'] = true;
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->canCompete())->toBeFalse();
        });
    });

    describe('international member', function () {
        it('handles international club flag', function () {
            $data = loadFixture('athlete.json');
            $data['InternationalClub'] = true;
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->internationalClub)->toBeTrue();
        });

        it('handles international member flag', function () {
            $data = loadFixture('athlete.json');
            $data['InternationalMember'] = true;
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->internationalMember)->toBeTrue();
        });

        it('defaults international club to false when missing', function () {
            $data = loadFixture('athlete.json');
            unset($data['InternationalClub']);
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->internationalClub)->toBeFalse();
        });

        it('defaults international member to false when missing', function () {
            $data = loadFixture('athlete.json');
            unset($data['InternationalMember']);
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->internationalMember)->toBeFalse();
        });
    });

    describe('scratched status', function () {
        it('handles scratched athlete with scratch date', function () {
            $data = loadFixture('athlete.json');
            $data['Scratched'] = true;
            $data['ScratchDate'] = '2024-02-01T09:00:00';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->scratched)->toBeTrue();
            expect($athlete->scratchDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($athlete->scratchDate->format('Y-m-d'))->toBe('2024-02-01');
        });

        it('defaults scratched to false when missing', function () {
            $data = loadFixture('athlete.json');
            unset($data['Scratched']);
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->scratched)->toBeFalse();
        });
    });

    describe('apparatus and age group', function () {
        it('handles apparatus value', function () {
            $data = loadFixture('athlete.json');
            $data['Apparatus'] = 'Vault';
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->apparatus)->toBe('Vault');
        });

        it('handles null apparatus', function () {
            $data = loadFixture('athlete.json');
            $data['Apparatus'] = null;
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->apparatus)->toBeNull();
        });

        it('handles null age group', function () {
            $data = loadFixture('athlete.json');
            unset($data['AgeGroup']);
            $athlete = AthleteReservation::fromArray($data);

            expect($athlete->ageGroup)->toBeNull();
        });
    });
});
