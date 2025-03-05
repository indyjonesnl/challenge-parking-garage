<?php

namespace App\Model;

use App\Enum\ParkingSpotStatus;

final class ParkingSpot
{
    public function __construct(
        private readonly int $id,
        private ParkingSpotStatus $parkingSpotStatus = ParkingSpotStatus::EMPTY,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function setParkingSpotStatus(ParkingSpotStatus $parkingSpotStatus): void
    {
        $this->parkingSpotStatus = $parkingSpotStatus;
    }

    public function getParkingSpotStatus(): ParkingSpotStatus
    {
        return $this->parkingSpotStatus;
    }
}