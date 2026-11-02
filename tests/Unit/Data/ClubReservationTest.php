<?php

declare(strict_types=1);

use AustinW\UsaGym\Data\ClubReservation;

describe('ClubReservation', function () {
    describe('fromArray', function () {
        it('creates from array with all fields', function () {
            $data = loadFixture('club.json');
            $club = ClubReservation::fromArray($data);

            expect($club)->toBeInstanceOf(ClubReservation::class);
            expect($club->clubId)->toBe('12345');
            expect($club->clubAbbrev)->toBe('ABC');
            expect($club->clubName)->toBe('ABC Gymnastics');
            expect($club->clubCity)->toBe('Los Angeles');
            expect($club->clubState)->toBe('CA');
            expect($club->internationalClub)->toBeFalse();
        });

        it('parses club contact information', function () {
            $data = loadFixture('club.json');
            $club = ClubReservation::fromArray($data);

            expect($club->clubContactId)->toBe('111');
            expect($club->clubContactName)->toBe('John Manager');
            expect($club->clubContactEmail)->toBe('manager@abcgym.com');
            expect($club->clubContactPhone)->toBe('555-123-4567');
        });

        it('parses meet contact information', function () {
            $data = loadFixture('club.json');
            $club = ClubReservation::fromArray($data);

            expect($club->meetContactId)->toBe('222');
            expect($club->meetContactName)->toBe('Sarah Coordinator');
            expect($club->meetContactEmail)->toBe('meets@abcgym.com');
            expect($club->meetContactPhone)->toBe('555-987-6543');
        });

        it('handles null club abbreviation', function () {
            $data = loadFixture('club.json');
            $data['ClubAbbrev'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->clubAbbrev)->toBeNull();
        });

        it('handles null club city', function () {
            $data = loadFixture('club.json');
            $data['ClubCity'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->clubCity)->toBeNull();
        });

        it('handles null club state', function () {
            $data = loadFixture('club.json');
            $data['ClubState'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->clubState)->toBeNull();
        });

        it('handles empty contact fields', function () {
            $data = loadFixture('club.json');
            $data['ClubContactID'] = '';
            $data['ClubContactName'] = '';
            $data['ClubContactEmail'] = '';
            $data['ClubContactPhone'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->clubContactId)->toBeNull();
            // clubContactName uses ?? operator, so empty string is preserved
            expect($club->clubContactName)->toBe('');
            expect($club->clubContactEmail)->toBeNull();
            expect($club->clubContactPhone)->toBeNull();
        });

        it('handles empty meet contact fields', function () {
            $data = loadFixture('club.json');
            $data['MeetContactID'] = '';
            $data['MeetContactName'] = '';
            $data['MeetContactEmail'] = '';
            $data['MeetContactPhone'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->meetContactId)->toBeNull();
            expect($club->meetContactName)->toBeNull();
            expect($club->meetContactEmail)->toBeNull();
            expect($club->meetContactPhone)->toBeNull();
        });

        it('casts integer contact IDs to strings', function () {
            $data = loadFixture('club.json');
            $data['ClubContactID'] = 111;
            $data['MeetContactID'] = 222;
            $club = ClubReservation::fromArray($data);

            expect($club->clubContactId)->toBe('111');
            expect($club->meetContactId)->toBe('222');
        });

        it('handles ClubContact as fallback for ClubContactName', function () {
            $data = loadFixture('club.json');
            unset($data['ClubContactName']);
            $data['ClubContact'] = 'Legacy Contact Name';
            $club = ClubReservation::fromArray($data);

            expect($club->clubContactName)->toBe('Legacy Contact Name');
        });

        it('prefers ClubContactName over ClubContact', function () {
            $data = loadFixture('club.json');
            $data['ClubContactName'] = 'Primary Contact';
            $data['ClubContact'] = 'Legacy Contact';
            $club = ClubReservation::fromArray($data);

            expect($club->clubContactName)->toBe('Primary Contact');
        });
    });

    describe('displayName', function () {
        it('returns club abbreviation when available', function () {
            $data = loadFixture('club.json');
            $club = ClubReservation::fromArray($data);

            expect($club->displayName())->toBe('ABC');
        });

        it('returns club name when abbreviation is null', function () {
            $data = loadFixture('club.json');
            $data['ClubAbbrev'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->displayName())->toBe('ABC Gymnastics');
        });

        it('returns club name for club without abbreviation', function () {
            $data = loadFixture('club.json');
            $data['ClubAbbrev'] = null;
            $data['ClubName'] = 'Long Club Name Without Abbreviation';
            $club = ClubReservation::fromArray($data);

            expect($club->displayName())->toBe('Long Club Name Without Abbreviation');
        });
    });

    describe('location', function () {
        it('returns city and state when both available', function () {
            $data = loadFixture('club.json');
            $club = ClubReservation::fromArray($data);

            expect($club->location())->toBe('Los Angeles, CA');
        });

        it('returns city only when state is null', function () {
            $data = loadFixture('club.json');
            $data['ClubState'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->location())->toBe('Los Angeles');
        });

        it('returns state only when city is null', function () {
            $data = loadFixture('club.json');
            $data['ClubCity'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->location())->toBe('CA');
        });

        it('returns null when both city and state are null', function () {
            $data = loadFixture('club.json');
            $data['ClubCity'] = '';
            $data['ClubState'] = '';
            $club = ClubReservation::fromArray($data);

            expect($club->location())->toBeNull();
        });

        it('formats various city and state combinations', function () {
            $data = loadFixture('club.json');

            // Test with different cities
            $data['ClubCity'] = 'New York';
            $data['ClubState'] = 'NY';
            $club = ClubReservation::fromArray($data);
            expect($club->location())->toBe('New York, NY');

            // Test with full state name
            $data['ClubCity'] = 'Houston';
            $data['ClubState'] = 'Texas';
            $club = ClubReservation::fromArray($data);
            expect($club->location())->toBe('Houston, Texas');
        });
    });

    describe('international club flag', function () {
        it('handles international club flag true', function () {
            $data = loadFixture('club.json');
            $data['InternationalClub'] = true;
            $club = ClubReservation::fromArray($data);

            expect($club->internationalClub)->toBeTrue();
        });

        it('handles international club flag false', function () {
            $data = loadFixture('club.json');
            $data['InternationalClub'] = false;
            $club = ClubReservation::fromArray($data);

            expect($club->internationalClub)->toBeFalse();
        });

        it('defaults international club to false when missing', function () {
            $data = loadFixture('club.json');
            unset($data['InternationalClub']);
            $club = ClubReservation::fromArray($data);

            expect($club->internationalClub)->toBeFalse();
        });

        it('handles integer values for international club', function () {
            $data = loadFixture('club.json');
            $data['InternationalClub'] = 1;
            $club = ClubReservation::fromArray($data);

            expect($club->internationalClub)->toBeTrue();

            $data['InternationalClub'] = 0;
            $club = ClubReservation::fromArray($data);

            expect($club->internationalClub)->toBeFalse();
        });
    });

    describe('minimal data', function () {
        it('creates with minimum required fields', function () {
            $data = [
                'ClubID' => '99999',
                'ClubAbbrev' => '',
                'ClubName' => 'Minimal Club',
                'ClubCity' => '',
                'ClubState' => '',
                'ClubContactID' => '',
                'ClubContactEmail' => '',
                'ClubContactPhone' => '',
                'MeetContactID' => '',
                'MeetContactName' => '',
                'MeetContactEmail' => '',
                'MeetContactPhone' => '',
                'InternationalClub' => false,
            ];
            $club = ClubReservation::fromArray($data);

            expect($club->clubId)->toBe('99999');
            expect($club->clubName)->toBe('Minimal Club');
            expect($club->clubAbbrev)->toBeNull();
            expect($club->clubCity)->toBeNull();
            expect($club->clubState)->toBeNull();
            expect($club->location())->toBeNull();
            expect($club->displayName())->toBe('Minimal Club');
        });
    });
});
