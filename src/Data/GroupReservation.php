<?php

namespace AustinW\Usagym\Data;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class GroupReservation extends FlexibleDataTransferObject
{
    public string $OrgID;

    public string $ClubAbbrev;

    public string $ClubName;

    public string $InternationalClub;

    public string $GroupID;

    public string $GroupName;

    public string $GroupType;

    public string $Discipline;

    public string $Status;

    public string $RegDate;

    public string $Apparatus;

    public string $Level;

    public string $LevelCode;

    public string $AgeGroup;

    public array $Athletes;

    public int $Scratched;

    public $ScratchDate;

    public string $ModifiedDate;

    public ?string $AthleteID1;

    public ?string $AthleteID2;
}
