<?php

namespace AustinW\Usagym\Data;

use Spatie\DataTransferObject\DataTransferObject;

class AthleteReservation extends DataTransferObject
{
    public string $OrgID;

    public string $ClubAbbrev;

    public string $ClubName;

    public string $InternationalClub;

    public string $MemberID;

    public string $LastName;

    public string $FirstName;

    public string $DOB;

    public string $Discipline;

    public string $MemberType;

    public int $InternationalMember;

    public string $Status;

    public string $RegDate;

    public string $Apparatus;

    public string $Level;

    public string $LevelCode;

    public ?string $AgeGroup;

    public int $Scratched;

    public $ScratchDate;

    public string $ModifiedDate;
}
