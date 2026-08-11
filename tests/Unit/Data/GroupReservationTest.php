<?php

declare(strict_types=1);

use AustinW\UsaGym\Data\GroupReservation;
use AustinW\UsaGym\Data\GroupAthlete;
use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\MemberStatus;

describe('GroupReservation', function () {
    describe('fromArray', function () {
        it('creates from array with all fields', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group)->toBeInstanceOf(GroupReservation::class);
            expect($group->orgId)->toBe('12345');
            expect($group->clubAbbrev)->toBe('ABC');
            expect($group->clubName)->toBe('ABC Gymnastics');
            expect($group->internationalClub)->toBeFalse();
            expect($group->groupId)->toBe('GRP001');
            expect($group->groupName)->toBe('ABC Junior Group');
            expect($group->groupType)->toBe('Group');
            expect($group->level)->toBe('Level 7');
            expect($group->ageGroup)->toBe('Junior');
            expect($group->apparatus)->toBe('5 Balls');
            expect($group->scratched)->toBeFalse();
        });

        it('parses registration date correctly', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->registrationDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($group->registrationDate->format('Y-m-d'))->toBe('2024-01-20');
            expect($group->registrationDate->format('H:i:s'))->toBe('14:00:00');
        });

        it('parses modified date correctly', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->modifiedDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($group->modifiedDate->format('Y-m-d H:i:s'))->toBe('2024-01-20 14:00:00');
        });

        it('handles null scratch date', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->scratchDate)->toBeNull();
        });

        it('parses athletes array correctly', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->athletes)->toHaveCount(5);
            expect($group->athletes[0])->toBeInstanceOf(GroupAthlete::class);
        });

        it('handles empty athletes array', function () {
            $data = loadFixture('group.json');
            $data['Athletes'] = [];
            $group = GroupReservation::fromArray($data);

            expect($group->athletes)->toBe([]);
            expect($group->athletes)->toHaveCount(0);
        });

        it('handles missing athletes array', function () {
            $data = loadFixture('group.json');
            unset($data['Athletes']);
            $group = GroupReservation::fromArray($data);

            expect($group->athletes)->toBe([]);
        });

        it('handles null club abbreviation', function () {
            $data = loadFixture('group.json');
            $data['ClubAbbrev'] = '';
            $group = GroupReservation::fromArray($data);

            expect($group->clubAbbrev)->toBeNull();
        });

        it('handles null age group', function () {
            $data = loadFixture('group.json');
            unset($data['AgeGroup']);
            $group = GroupReservation::fromArray($data);

            expect($group->ageGroup)->toBeNull();
        });

        it('handles null apparatus', function () {
            $data = loadFixture('group.json');
            unset($data['Apparatus']);
            $group = GroupReservation::fromArray($data);

            expect($group->apparatus)->toBeNull();
        });
    });

    describe('enum mapping', function () {
        it('maps discipline correctly', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->discipline)->toBe(Discipline::Rhythmic);
            expect($group->discipline->value)->toBe('RG');
        });

        it('maps member status correctly', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->status)->toBe(MemberStatus::Active);
            expect($group->status->value)->toBe('Active');
        });

        it('degrades an unrecognized status to null while preserving the raw code', function () {
            $data = loadFixture('group.json');
            $data['Status'] = 'Deceased';
            $group = GroupReservation::fromArray($data);

            expect($group->status)->toBeNull();
            expect($group->statusRaw)->toBe('Deceased');
            expect($group->groupName)->toBe('ABC Junior Group');
        });

        it('preserves the raw status even when recognized', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->statusRaw)->toBe('Active');
        });

        it('handles discipline from display name', function () {
            $data = loadFixture('group.json');
            $data['Discipline'] = 'Acro';
            $group = GroupReservation::fromArray($data);

            expect($group->discipline)->toBe(Discipline::Acrobatic);
        });

        it('handles discipline from code', function () {
            $data = loadFixture('group.json');
            $data['Discipline'] = 'ACRO';
            $group = GroupReservation::fromArray($data);

            expect($group->discipline)->toBe(Discipline::Acrobatic);
        });
    });

    describe('athleteCount', function () {
        it('returns correct count for group with athletes', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->athleteCount())->toBe(5);
        });

        it('returns zero for group with no athletes', function () {
            $data = loadFixture('group.json');
            $data['Athletes'] = [];
            $group = GroupReservation::fromArray($data);

            expect($group->athleteCount())->toBe(0);
        });

        it('returns correct count for pair', function () {
            $data = loadFixture('group.json');
            $data['GroupType'] = 'Pair';
            $data['Athletes'] = [
                ['MemberID' => '111111', 'LastName' => 'Smith', 'FirstName' => 'Alice'],
                ['MemberID' => '222222', 'LastName' => 'Jones', 'FirstName' => 'Betty'],
            ];
            $group = GroupReservation::fromArray($data);

            expect($group->athleteCount())->toBe(2);
        });
    });

    describe('canCompete', function () {
        it('returns true for active non-scratched group', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->canCompete())->toBeTrue();
        });

        it('returns false when scratched', function () {
            $data = loadFixture('group.json');
            $data['Scratched'] = true;
            $group = GroupReservation::fromArray($data);

            expect($group->canCompete())->toBeFalse();
        });

        it('returns false when status is expired', function () {
            $data = loadFixture('group.json');
            $data['Status'] = 'Expired';
            $group = GroupReservation::fromArray($data);

            expect($group->canCompete())->toBeFalse();
        });

        it('returns false when status is banned', function () {
            $data = loadFixture('group.json');
            $data['Status'] = 'Banned';
            $group = GroupReservation::fromArray($data);

            expect($group->canCompete())->toBeFalse();
        });

        it('returns false when status is suspended', function () {
            $data = loadFixture('group.json');
            $data['Status'] = 'Suspended';
            $group = GroupReservation::fromArray($data);

            expect($group->canCompete())->toBeFalse();
        });

        it('returns true when status is pending', function () {
            $data = loadFixture('group.json');
            $data['Status'] = 'Pending';
            $group = GroupReservation::fromArray($data);

            expect($group->canCompete())->toBeTrue();
        });

        it('returns false when scratched and pending', function () {
            $data = loadFixture('group.json');
            $data['Status'] = 'Pending';
            $data['Scratched'] = true;
            $group = GroupReservation::fromArray($data);

            expect($group->canCompete())->toBeFalse();
        });

        it('returns false when status is unrecognized', function () {
            $data = loadFixture('group.json');
            $data['Status'] = 'SomeNewStatusUsagAddedLater';
            $group = GroupReservation::fromArray($data);

            expect($group->status)->toBeNull();
            expect($group->canCompete())->toBeFalse();
        });
    });

    describe('international club flag', function () {
        it('handles international club flag true', function () {
            $data = loadFixture('group.json');
            $data['InternationalClub'] = true;
            $group = GroupReservation::fromArray($data);

            expect($group->internationalClub)->toBeTrue();
        });

        it('defaults international club to false when missing', function () {
            $data = loadFixture('group.json');
            unset($data['InternationalClub']);
            $group = GroupReservation::fromArray($data);

            expect($group->internationalClub)->toBeFalse();
        });
    });

    describe('scratched status', function () {
        it('handles scratched group with scratch date', function () {
            $data = loadFixture('group.json');
            $data['Scratched'] = true;
            $data['ScratchDate'] = '2024-02-25T16:00:00';
            $group = GroupReservation::fromArray($data);

            expect($group->scratched)->toBeTrue();
            expect($group->scratchDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($group->scratchDate->format('Y-m-d'))->toBe('2024-02-25');
        });

        it('defaults scratched to false when missing', function () {
            $data = loadFixture('group.json');
            unset($data['Scratched']);
            $group = GroupReservation::fromArray($data);

            expect($group->scratched)->toBeFalse();
        });
    });

    describe('group types', function () {
        it('handles Group type', function () {
            $data = loadFixture('group.json');
            $data['GroupType'] = 'Group';
            $group = GroupReservation::fromArray($data);

            expect($group->groupType)->toBe('Group');
        });

        it('handles Pair type', function () {
            $data = loadFixture('group.json');
            $data['GroupType'] = 'Pair';
            $group = GroupReservation::fromArray($data);

            expect($group->groupType)->toBe('Pair');
        });

        it('handles Trio type', function () {
            $data = loadFixture('group.json');
            $data['GroupType'] = 'Trio';
            $group = GroupReservation::fromArray($data);

            expect($group->groupType)->toBe('Trio');
        });
    });

    describe('athletes data integrity', function () {
        it('preserves athlete order', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->athletes[0]->firstName)->toBe('Alice');
            expect($group->athletes[1]->firstName)->toBe('Betty');
            expect($group->athletes[2]->firstName)->toBe('Carol');
            expect($group->athletes[3]->firstName)->toBe('Diana');
            expect($group->athletes[4]->firstName)->toBe('Eve');
        });

        it('preserves athlete member IDs', function () {
            $data = loadFixture('group.json');
            $group = GroupReservation::fromArray($data);

            expect($group->athletes[0]->memberId)->toBe('111111');
            expect($group->athletes[1]->memberId)->toBe('222222');
            expect($group->athletes[2]->memberId)->toBe('333333');
            expect($group->athletes[3]->memberId)->toBe('444444');
            expect($group->athletes[4]->memberId)->toBe('555555');
        });
    });

    describe('date parsing edge cases', function () {
        it('handles empty registration date', function () {
            $data = loadFixture('group.json');
            $data['RegDate'] = '';
            $group = GroupReservation::fromArray($data);

            expect($group->registrationDate)->toBeNull();
        });

        it('handles missing registration date', function () {
            $data = loadFixture('group.json');
            unset($data['RegDate']);
            $group = GroupReservation::fromArray($data);

            expect($group->registrationDate)->toBeNull();
        });
    });
});
