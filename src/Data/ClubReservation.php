<?php

namespace AustinW\Usagym\Data;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ClubReservation extends FlexibleDataTransferObject
{
    public string $ClubID;

    public string $ClubAbbrev;

    public string $ClubName;

    public string $ClubCity;

    public string $ClubState;

    public ?string $ClubContactID;

    public string $ClubContactName;

    public string $ClubContactEmail;

    public ?string $ClubContactPhone;

    public string $MeetContactID;

    public ?string $MeetContactName;

    public string $MeetContactEmail;

    public ?string $MeetContactPhone;

    public string $InternationalClub;
}
