<?php

namespace App\Enum;

enum ParkingSpotStatus
{
    case EMPTY;
    case HALF_OCCUPIED;
    case FULLY_OCCUPIED;
}