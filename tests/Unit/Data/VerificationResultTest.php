<?php

declare(strict_types=1);

use AustinW\UsaGym\Data\VerificationResult;
use AustinW\UsaGym\Enums\MemberType;

describe('VerificationResult', function () {
    describe('fromArray', function () {
        it('creates from array with all fields', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result)->toBeInstanceOf(VerificationResult::class);
            expect($result->memberId)->toBe('987654');
            expect($result->lastName)->toBe('Smith');
            expect($result->firstName)->toBe('Jane');
            expect($result->dateOfBirth)->toBe('05/15/2010');
            expect($result->level)->toBe('WLEVEL04');
            expect($result->eligible)->toBeTrue();
            expect($result->internationalMember)->toBeFalse();
        });

        it('parses US citizen flag correctly', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->usCitizen)->toBeTrue();
        });

        it('parses club arrays correctly', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->clubIds)->toBe(['12345']);
            expect($result->clubAbbrevs)->toBe(['ABC']);
            expect($result->clubNames)->toBe(['ABC Gymnastics']);
            expect($result->clubStatuses)->toBe(['ACTIVE']);
            expect($result->internationalClubs)->toBe(['No']);
        });

        it('parses disciplines array correctly', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->disciplines)->toBe(['WAG']);
        });

        it('handles null certification', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->certificationValid)->toBeNull();
            expect($result->certificationLevels)->toBeNull();
        });

        it('handles certification with valid and levels', function () {
            $data = loadFixture('verification.json');
            $data['Certification'] = [
                'valid' => true,
                'levels' => ['NAT', 'REG'],
            ];
            $result = VerificationResult::fromArray($data);

            expect($result->certificationValid)->toBeTrue();
            expect($result->certificationLevels)->toBe(['NAT', 'REG']);
        });

        it('handles null ineligible reason when eligible', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->ineligibleReason)->toBeNull();
        });

        it('handles ineligible reason when not eligible', function () {
            $data = loadFixture('verification.json');
            $data['Eligible'] = false;
            $data['IneligibleReason'] = 'Membership expired';
            $result = VerificationResult::fromArray($data);

            expect($result->eligible)->toBeFalse();
            expect($result->ineligibleReason)->toBe('Membership expired');
        });

        it('handles empty club arrays', function () {
            $data = loadFixture('verification.json');
            unset($data['ClubID']);
            unset($data['ClubAbbrev']);
            unset($data['ClubName']);
            unset($data['ClubStatus']);
            unset($data['InternationalClub']);
            $result = VerificationResult::fromArray($data);

            expect($result->clubIds)->toBe([]);
            expect($result->clubAbbrevs)->toBe([]);
            expect($result->clubNames)->toBe([]);
            expect($result->clubStatuses)->toBe([]);
            expect($result->internationalClubs)->toBe([]);
        });

        it('handles empty disciplines array', function () {
            $data = loadFixture('verification.json');
            unset($data['Discipline']);
            $result = VerificationResult::fromArray($data);

            expect($result->disciplines)->toBe([]);
        });

        it('handles missing DOB', function () {
            $data = loadFixture('verification.json');
            unset($data['DOB']);
            $result = VerificationResult::fromArray($data);

            expect($result->dateOfBirth)->toBeNull();
        });

        it('handles missing level', function () {
            $data = loadFixture('verification.json');
            unset($data['Level']);
            $result = VerificationResult::fromArray($data);

            expect($result->level)->toBeNull();
        });

        it('handles missing US citizen', function () {
            $data = loadFixture('verification.json');
            unset($data['USCitizen']);
            $result = VerificationResult::fromArray($data);

            expect($result->usCitizen)->toBeNull();
        });

        it('handles US citizen No value', function () {
            $data = loadFixture('verification.json');
            $data['USCitizen'] = 'No';
            $result = VerificationResult::fromArray($data);

            expect($result->usCitizen)->toBeFalse();
        });

        it('handles international member Yes value', function () {
            $data = loadFixture('verification.json');
            $data['InternationalMember'] = 'Yes';
            $result = VerificationResult::fromArray($data);

            expect($result->internationalMember)->toBeTrue();
        });

        it('defaults eligible to false when missing', function () {
            $data = loadFixture('verification.json');
            unset($data['Eligible']);
            $result = VerificationResult::fromArray($data);

            expect($result->eligible)->toBeFalse();
        });
    });

    describe('enum mapping', function () {
        it('maps member type correctly for athlete', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->memberType)->toBe(MemberType::Athlete);
            expect($result->memberType->value)->toBe('ATHL');
        });

        it('maps member type correctly for coach', function () {
            $data = loadFixture('verification.json');
            $data['MemberType'] = 'CCOACH';
            $result = VerificationResult::fromArray($data);

            expect($result->memberType)->toBe(MemberType::CompetitiveCoach);
        });

        it('maps member type correctly for judge', function () {
            $data = loadFixture('verification.json');
            $data['MemberType'] = 'JUDGE';
            $result = VerificationResult::fromArray($data);

            expect($result->memberType)->toBe(MemberType::Judge);
        });

        it('degrades an unrecognized member type to null while preserving the raw code', function () {
            $data = loadFixture('verification.json');
            $data['MemberType'] = 'SOMENEWMEMBERTYPE';
            $result = VerificationResult::fromArray($data);

            expect($result->memberType)->toBeNull();
            expect($result->memberTypeRaw)->toBe('SOMENEWMEMBERTYPE');
            expect($result->lastName)->toBe('Smith');
        });

        it('preserves the raw member type even when recognized', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->memberTypeRaw)->toBe('ATHL');
        });
    });

    describe('fullName', function () {
        it('returns full name', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->fullName())->toBe('Jane Smith');
        });

        it('returns full name with different names', function () {
            $data = loadFixture('verification.json');
            $data['FirstName'] = 'Emily';
            $data['LastName'] = 'Johnson';
            $result = VerificationResult::fromArray($data);

            expect($result->fullName())->toBe('Emily Johnson');
        });
    });

    describe('primaryClubId', function () {
        it('returns first club ID', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->primaryClubId())->toBe('12345');
        });

        it('returns null when no clubs', function () {
            $data = loadFixture('verification.json');
            $data['ClubID'] = [];
            $result = VerificationResult::fromArray($data);

            expect($result->primaryClubId())->toBeNull();
        });

        it('returns first when multiple clubs', function () {
            $data = loadFixture('verification.json');
            $data['ClubID'] = ['11111', '22222', '33333'];
            $result = VerificationResult::fromArray($data);

            expect($result->primaryClubId())->toBe('11111');
        });
    });

    describe('primaryClubName', function () {
        it('returns first club name', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->primaryClubName())->toBe('ABC Gymnastics');
        });

        it('returns null when no clubs', function () {
            $data = loadFixture('verification.json');
            $data['ClubName'] = [];
            $result = VerificationResult::fromArray($data);

            expect($result->primaryClubName())->toBeNull();
        });

        it('returns first when multiple clubs', function () {
            $data = loadFixture('verification.json');
            $data['ClubName'] = ['First Club', 'Second Club', 'Third Club'];
            $result = VerificationResult::fromArray($data);

            expect($result->primaryClubName())->toBe('First Club');
        });
    });

    describe('canParticipate', function () {
        it('returns true when eligible', function () {
            $data = loadFixture('verification.json');
            $result = VerificationResult::fromArray($data);

            expect($result->canParticipate())->toBeTrue();
        });

        it('returns false when not eligible', function () {
            $data = loadFixture('verification.json');
            $data['Eligible'] = false;
            $result = VerificationResult::fromArray($data);

            expect($result->canParticipate())->toBeFalse();
        });

        it('returns same value as eligible property', function () {
            $data = loadFixture('verification.json');

            $data['Eligible'] = true;
            $result = VerificationResult::fromArray($data);
            expect($result->canParticipate())->toBe($result->eligible);

            $data['Eligible'] = false;
            $result = VerificationResult::fromArray($data);
            expect($result->canParticipate())->toBe($result->eligible);
        });
    });

    describe('multiple clubs', function () {
        it('handles member with multiple clubs', function () {
            $data = loadFixture('verification.json');
            $data['ClubID'] = ['11111', '22222'];
            $data['ClubAbbrev'] = ['ABC', 'XYZ'];
            $data['ClubName'] = ['ABC Gymnastics', 'XYZ Sports'];
            $data['ClubStatus'] = ['ACTIVE', 'ACTIVE'];
            $data['InternationalClub'] = ['No', 'Yes'];
            $result = VerificationResult::fromArray($data);

            expect($result->clubIds)->toHaveCount(2);
            expect($result->clubAbbrevs)->toHaveCount(2);
            expect($result->clubNames)->toHaveCount(2);
            expect($result->clubStatuses)->toHaveCount(2);
            expect($result->internationalClubs)->toHaveCount(2);

            expect($result->primaryClubId())->toBe('11111');
            expect($result->primaryClubName())->toBe('ABC Gymnastics');
        });
    });

    describe('multiple disciplines', function () {
        it('handles member with multiple disciplines', function () {
            $data = loadFixture('verification.json');
            $data['Discipline'] = ['WAG', 'RG'];
            $result = VerificationResult::fromArray($data);

            expect($result->disciplines)->toHaveCount(2);
            expect($result->disciplines)->toBe(['WAG', 'RG']);
        });
    });

    describe('certification data', function () {
        it('handles certification with valid false', function () {
            $data = loadFixture('verification.json');
            $data['Certification'] = [
                'valid' => false,
                'levels' => [],
            ];
            $result = VerificationResult::fromArray($data);

            expect($result->certificationValid)->toBeFalse();
            expect($result->certificationLevels)->toBe([]);
        });

        it('handles certification with multiple levels', function () {
            $data = loadFixture('verification.json');
            $data['Certification'] = [
                'valid' => true,
                'levels' => ['NAT', 'REG', 'STATE'],
            ];
            $result = VerificationResult::fromArray($data);

            expect($result->certificationValid)->toBeTrue();
            expect($result->certificationLevels)->toHaveCount(3);
            expect($result->certificationLevels)->toBe(['NAT', 'REG', 'STATE']);
        });

        it('handles certification without levels key', function () {
            $data = loadFixture('verification.json');
            $data['Certification'] = [
                'valid' => true,
            ];
            $result = VerificationResult::fromArray($data);

            expect($result->certificationValid)->toBeTrue();
            expect($result->certificationLevels)->toBeNull();
        });

        it('handles certification without valid key', function () {
            $data = loadFixture('verification.json');
            $data['Certification'] = [
                'levels' => ['NAT'],
            ];
            $result = VerificationResult::fromArray($data);

            expect($result->certificationValid)->toBeNull();
            expect($result->certificationLevels)->toBe(['NAT']);
        });
    });
});
