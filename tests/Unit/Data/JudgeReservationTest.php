<?php

declare(strict_types=1);

use AustinW\UsaGym\Enums\Discipline;
use AustinW\UsaGym\Enums\MemberType;
use AustinW\UsaGym\Enums\MemberStatus;
use AustinW\UsaGym\Data\JudgeReservation;

describe('JudgeReservation', function () {
    describe('fromArray', function () {
        it('creates from array with all fields', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge)->toBeInstanceOf(JudgeReservation::class);
            expect($judge->memberId)->toBe('666666');
            expect($judge->lastName)->toBe('Davis');
            expect($judge->firstName)->toBe('Judge');
            expect($judge->level)->toBe('National');
            expect($judge->scratched)->toBeFalse();
            expect($judge->internationalMember)->toBeFalse();
        });

        it('parses registration date correctly', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->registrationDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($judge->registrationDate->format('Y-m-d'))->toBe('2024-01-05');
            expect($judge->registrationDate->format('H:i:s'))->toBe('12:00:00');
        });

        it('parses modified date correctly', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->modifiedDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($judge->modifiedDate->format('Y-m-d H:i:s'))->toBe('2024-01-05 12:00:00');
        });

        it('handles null scratch date', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->scratchDate)->toBeNull();
        });

        it('parses certifications correctly', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->certifications)->toBe(['NAT', 'REG']);
            expect($judge->certifications)->toHaveCount(2);
        });

        it('handles empty certifications', function () {
            $data = loadFixture('judge.json');
            unset($data['Certification']);
            $judge = JudgeReservation::fromArray($data);

            expect($judge->certifications)->toBe([]);
        });

        /**
         * Live T&T sanctions send `"Certification": [null, null, null]` — the key is
         * present but carries nothing. Passed through, it made `certifications` report a
         * count of 3 for a judge holding none, and fataled any consumer mapping it through
         * a string-typed callable. The declared array<string> has to be true at runtime or
         * static analysis blesses both bugs.
         */
        it('drops nulls so a placeholder array does not read as held certifications', function () {
            $data = loadFixture('judge.json');
            $data['Certification'] = [null, null, null];
            $judge = JudgeReservation::fromArray($data);

            expect($judge->certifications)->toBe([])
                ->and($judge->certifications)->toHaveCount(0);
        });

        it('keeps the real codes from a partially populated array', function () {
            $data = loadFixture('judge.json');
            $data['Certification'] = ['TRC2', null, 'DMC1'];
            $judge = JudgeReservation::fromArray($data);

            expect($judge->certifications)->toBe(['TRC2', 'DMC1']);
        });

        it('reindexes so the result is a list rather than a sparse array', function () {
            $data = loadFixture('judge.json');
            $data['Certification'] = [null, 'TRC2'];
            $judge = JudgeReservation::fromArray($data);

            expect(array_keys($judge->certifications))->toBe([0]);
        });

        it('drops empty and whitespace-only codes', function () {
            $data = loadFixture('judge.json');
            $data['Certification'] = ['', '   ', 'TRC2'];
            $judge = JudgeReservation::fromArray($data);

            expect($judge->certifications)->toBe(['TRC2']);
        });

        it('drops non-string scalars rather than coercing them', function () {
            $data = loadFixture('judge.json');
            $data['Certification'] = [123, true, 'TRC2'];
            $judge = JudgeReservation::fromArray($data);

            expect($judge->certifications)->toBe(['TRC2']);
        });

        it('tolerates a non-array Certification value', function () {
            $data = loadFixture('judge.json');
            $data['Certification'] = null;
            $judge = JudgeReservation::fromArray($data);

            expect($judge->certifications)->toBe([]);
        });

        it('defaults level to Judge when missing', function () {
            $data = loadFixture('judge.json');
            unset($data['Level']);
            $judge = JudgeReservation::fromArray($data);

            expect($judge->level)->toBe('Judge');
        });
    });

    describe('enum mapping', function () {
        it('maps discipline correctly', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->discipline)->toBe(Discipline::WomensArtistic);
            expect($judge->discipline->value)->toBe('WAG');
        });

        it('maps member type correctly', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->memberType)->toBe(MemberType::Judge);
            expect($judge->memberType->value)->toBe('JUDGE');
        });

        it('maps member status correctly', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->status)->toBe(MemberStatus::Active);
            expect($judge->status->value)->toBe('Active');
        });

        it('degrades an unrecognized member type to null while preserving the raw code', function () {
            $data = loadFixture('judge.json');
            $data['MemberType'] = 'SOMENEWMEMBERTYPE';
            $judge = JudgeReservation::fromArray($data);

            expect($judge->memberType)->toBeNull();
            expect($judge->memberTypeRaw)->toBe('SOMENEWMEMBERTYPE');
            expect($judge->lastName)->toBe('Davis');
        });

        it('degrades an unrecognized status to null while preserving the raw code', function () {
            $data = loadFixture('judge.json');
            $data['Status'] = 'Deceased';
            $judge = JudgeReservation::fromArray($data);

            expect($judge->status)->toBeNull();
            expect($judge->statusRaw)->toBe('Deceased');
            expect($judge->lastName)->toBe('Davis');
        });

        it('preserves the raw member type and status even when recognized', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->memberTypeRaw)->toBe('JUDGE');
            expect($judge->statusRaw)->toBe('Active');
        });

        it('handles different disciplines', function () {
            $disciplines = [
                'Women' => Discipline::WomensArtistic,
                'Men' => Discipline::MensArtistic,
                'Rhythmic' => Discipline::Rhythmic,
                'Acro' => Discipline::Acrobatic,
                'TT' => Discipline::Trampoline,
            ];

            $data = loadFixture('judge.json');

            foreach ($disciplines as $value => $expectedEnum) {
                $data['Discipline'] = $value;
                $judge = JudgeReservation::fromArray($data);

                expect($judge->discipline)->toBe($expectedEnum);
            }
        });
    });

    describe('fullName', function () {
        it('returns full name', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->fullName())->toBe('Judge Davis');
        });

        it('returns full name with different names', function () {
            $data = loadFixture('judge.json');
            $data['FirstName'] = 'Patricia';
            $data['LastName'] = 'Anderson';
            $judge = JudgeReservation::fromArray($data);

            expect($judge->fullName())->toBe('Patricia Anderson');
        });
    });

    describe('hasCertification', function () {
        it('returns true when certification exists', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->hasCertification('NAT'))->toBeTrue();
            expect($judge->hasCertification('REG'))->toBeTrue();
        });

        it('returns false when certification does not exist', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->hasCertification('INT'))->toBeFalse();
            expect($judge->hasCertification('BREVET'))->toBeFalse();
        });

        it('returns false for empty certifications', function () {
            $data = loadFixture('judge.json');
            $data['Certification'] = [];
            $judge = JudgeReservation::fromArray($data);

            expect($judge->hasCertification('NAT'))->toBeFalse();
        });

        it('is case sensitive', function () {
            $data = loadFixture('judge.json');
            $judge = JudgeReservation::fromArray($data);

            expect($judge->hasCertification('nat'))->toBeFalse();
            expect($judge->hasCertification('Nat'))->toBeFalse();
            expect($judge->hasCertification('NAT'))->toBeTrue();
        });
    });

    describe('international member flag', function () {
        it('handles international member flag true', function () {
            $data = loadFixture('judge.json');
            $data['InternationalMember'] = true;
            $judge = JudgeReservation::fromArray($data);

            expect($judge->internationalMember)->toBeTrue();
        });

        it('handles international member flag false', function () {
            $data = loadFixture('judge.json');
            $data['InternationalMember'] = false;
            $judge = JudgeReservation::fromArray($data);

            expect($judge->internationalMember)->toBeFalse();
        });

        it('defaults international member to false when missing', function () {
            $data = loadFixture('judge.json');
            unset($data['InternationalMember']);
            $judge = JudgeReservation::fromArray($data);

            expect($judge->internationalMember)->toBeFalse();
        });
    });

    describe('scratched status', function () {
        it('handles scratched judge with scratch date', function () {
            $data = loadFixture('judge.json');
            $data['Scratched'] = true;
            $data['ScratchDate'] = '2024-02-20T11:15:00';
            $judge = JudgeReservation::fromArray($data);

            expect($judge->scratched)->toBeTrue();
            expect($judge->scratchDate)->toBeInstanceOf(DateTimeImmutable::class);
            expect($judge->scratchDate->format('Y-m-d'))->toBe('2024-02-20');
        });

        it('defaults scratched to false when missing', function () {
            $data = loadFixture('judge.json');
            unset($data['Scratched']);
            $judge = JudgeReservation::fromArray($data);

            expect($judge->scratched)->toBeFalse();
        });
    });

    describe('date parsing edge cases', function () {
        it('handles empty registration date', function () {
            $data = loadFixture('judge.json');
            $data['RegDate'] = '';
            $judge = JudgeReservation::fromArray($data);

            expect($judge->registrationDate)->toBeNull();
        });

        it('handles missing registration date', function () {
            $data = loadFixture('judge.json');
            unset($data['RegDate']);
            $judge = JudgeReservation::fromArray($data);

            expect($judge->registrationDate)->toBeNull();
        });

        it('handles empty modified date', function () {
            $data = loadFixture('judge.json');
            $data['ModifiedDate'] = '';
            $judge = JudgeReservation::fromArray($data);

            expect($judge->modifiedDate)->toBeNull();
        });
    });
});
