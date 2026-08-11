<?php

declare(strict_types=1);

use AustinW\UsaGym\Data\CoachReservation;
use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\MemberStatus;
use AustinW\UsaGym\Enums\MemberType;

describe('CoachReservation', function () {
    describe('fromArray', function () {
        it('creates from array with all fields', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach)->toBeInstanceOf(CoachReservation::class);
            expect($coach->orgId)->toBe('12345');
            expect($coach->clubAbbrev)->toBe('ABC');
            expect($coach->clubName)->toBe('ABC Gymnastics');
            expect($coach->internationalClub)->toBeFalse();
            expect($coach->memberId)->toBe('555555');
            expect($coach->lastName)->toBe('Williams');
            expect($coach->firstName)->toBe('Coach');
            expect($coach->scratched)->toBeFalse();
            expect($coach->internationalMember)->toBeFalse();
        });

        it('parses registration date correctly', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach->registrationDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($coach->registrationDate->format('Y-m-d'))->toBe('2024-01-10');
            expect($coach->registrationDate->format('H:i:s'))->toBe('08:00:00');
        });

        it('parses modified date correctly', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach->modifiedDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($coach->modifiedDate->format('Y-m-d H:i:s'))->toBe('2024-01-10 08:00:00');
        });

        it('handles null scratch date', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach->scratchDate)->toBeNull();
        });

        it('handles null club abbreviation', function () {
            $data = loadFixture('coach.json');
            $data['ClubAbbrev'] = '';
            $coach = CoachReservation::fromArray($data);

            expect($coach->clubAbbrev)->toBeNull();
        });

        it('handles empty registration date', function () {
            $data = loadFixture('coach.json');
            $data['RegDate'] = '';
            $coach = CoachReservation::fromArray($data);

            expect($coach->registrationDate)->toBeNull();
        });

        it('handles missing registration date', function () {
            $data = loadFixture('coach.json');
            unset($data['RegDate']);
            $coach = CoachReservation::fromArray($data);

            expect($coach->registrationDate)->toBeNull();
        });
    });

    describe('enum mapping', function () {
        it('maps discipline correctly', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach->discipline)->toBe(Discipline::WomensArtistic);
            expect($coach->discipline->value)->toBe('WAG');
        });

        it('maps member type correctly', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach->memberType)->toBe(MemberType::CompetitiveCoach);
            expect($coach->memberType->value)->toBe('CCOACH');
        });

        it('maps member status correctly', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach->status)->toBe(MemberStatus::Active);
            expect($coach->status->value)->toBe('Active');
        });

        it('handles discipline from display name', function () {
            $data = loadFixture('coach.json');
            $data['Discipline'] = 'Rhythmic';
            $coach = CoachReservation::fromArray($data);

            expect($coach->discipline)->toBe(Discipline::Rhythmic);
        });

        it('handles discipline from code', function () {
            $data = loadFixture('coach.json');
            $data['Discipline'] = 'TRA';
            $coach = CoachReservation::fromArray($data);

            expect($coach->discipline)->toBe(Discipline::Trampoline);
        });

        it('degrades an unrecognized member type to null while preserving the raw code', function () {
            $data = loadFixture('coach.json');
            $data['MemberType'] = 'SOMENEWMEMBERTYPE';
            $coach = CoachReservation::fromArray($data);

            expect($coach->memberType)->toBeNull();
            expect($coach->memberTypeRaw)->toBe('SOMENEWMEMBERTYPE');
            expect($coach->lastName)->toBe('Williams');
        });

        it('degrades an unrecognized status to null while preserving the raw code', function () {
            $data = loadFixture('coach.json');
            $data['Status'] = 'Deceased';
            $coach = CoachReservation::fromArray($data);

            expect($coach->status)->toBeNull();
            expect($coach->statusRaw)->toBe('Deceased');
            expect($coach->lastName)->toBe('Williams');
        });

        it('preserves the raw member type and status even when recognized', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach->memberTypeRaw)->toBe('CCOACH');
            expect($coach->statusRaw)->toBe('Active');
        });

        it('handles different member statuses', function () {
            $statuses = [
                'Active' => MemberStatus::Active,
                'Pending' => MemberStatus::Pending,
                'Expired' => MemberStatus::Expired,
                'Banned' => MemberStatus::Banned,
                'Suspended' => MemberStatus::Suspended,
            ];

            $data = loadFixture('coach.json');

            foreach ($statuses as $statusValue => $expectedEnum) {
                $data['Status'] = $statusValue;
                $coach = CoachReservation::fromArray($data);

                expect($coach->status)->toBe($expectedEnum);
            }
        });
    });

    describe('fullName', function () {
        it('returns full name', function () {
            $data = loadFixture('coach.json');
            $coach = CoachReservation::fromArray($data);

            expect($coach->fullName())->toBe('Coach Williams');
        });

        it('returns full name with different names', function () {
            $data = loadFixture('coach.json');
            $data['FirstName'] = 'Michael';
            $data['LastName'] = 'Thompson';
            $coach = CoachReservation::fromArray($data);

            expect($coach->fullName())->toBe('Michael Thompson');
        });
    });

    describe('international flags', function () {
        it('handles international club flag', function () {
            $data = loadFixture('coach.json');
            $data['InternationalClub'] = true;
            $coach = CoachReservation::fromArray($data);

            expect($coach->internationalClub)->toBeTrue();
        });

        it('handles international member flag', function () {
            $data = loadFixture('coach.json');
            $data['InternationalMember'] = true;
            $coach = CoachReservation::fromArray($data);

            expect($coach->internationalMember)->toBeTrue();
        });

        it('defaults international club to false when missing', function () {
            $data = loadFixture('coach.json');
            unset($data['InternationalClub']);
            $coach = CoachReservation::fromArray($data);

            expect($coach->internationalClub)->toBeFalse();
        });

        it('defaults international member to false when missing', function () {
            $data = loadFixture('coach.json');
            unset($data['InternationalMember']);
            $coach = CoachReservation::fromArray($data);

            expect($coach->internationalMember)->toBeFalse();
        });
    });

    describe('scratched status', function () {
        it('handles scratched coach with scratch date', function () {
            $data = loadFixture('coach.json');
            $data['Scratched'] = true;
            $data['ScratchDate'] = '2024-02-15T14:30:00';
            $coach = CoachReservation::fromArray($data);

            expect($coach->scratched)->toBeTrue();
            expect($coach->scratchDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($coach->scratchDate->format('Y-m-d'))->toBe('2024-02-15');
            expect($coach->scratchDate->format('H:i:s'))->toBe('14:30:00');
        });

        it('defaults scratched to false when missing', function () {
            $data = loadFixture('coach.json');
            unset($data['Scratched']);
            $coach = CoachReservation::fromArray($data);

            expect($coach->scratched)->toBeFalse();
        });

        it('handles empty scratch date', function () {
            $data = loadFixture('coach.json');
            $data['ScratchDate'] = '';
            $coach = CoachReservation::fromArray($data);

            expect($coach->scratchDate)->toBeNull();
        });
    });

    describe('date parsing edge cases', function () {
        it('handles invalid date string gracefully', function () {
            $data = loadFixture('coach.json');
            $data['RegDate'] = 'invalid-date';
            $coach = CoachReservation::fromArray($data);

            // Should return null for invalid dates due to exception handling
            expect($coach->registrationDate)->toBeNull();
        });

        it('handles various datetime formats', function () {
            $data = loadFixture('coach.json');
            $data['ModifiedDate'] = '2024-03-20 16:45:30';
            $coach = CoachReservation::fromArray($data);

            expect($coach->modifiedDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($coach->modifiedDate->format('Y-m-d H:i:s'))->toBe('2024-03-20 16:45:30');
        });
    });
});
