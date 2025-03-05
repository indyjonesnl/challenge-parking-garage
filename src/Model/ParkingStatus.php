<?php

namespace App\Model;

use Stringable;

final readonly class ParkingStatus implements Stringable
{
    public function __construct(
        private int $emptyParkingSpots,
        private int $halfOccupiedParkingSpots,
        private int $fullyOccupiedParkingSpots,
    ) {}

    public function getEmptyParkingSpots(): int
    {
        return $this->emptyParkingSpots;
    }

    public function getHalfOccupiedParkingSpots(): int
    {
        return $this->halfOccupiedParkingSpots;
    }

    public function getFullyOccupiedParkingSpots(): int
    {
        return $this->fullyOccupiedParkingSpots;
    }

    public function __toString(): string
    {
        return $this->emptyParkingSpots . ' empty, ' . $this->halfOccupiedParkingSpots . ' half occupied, ' . $this->fullyOccupiedParkingSpots . ' fully occupied.';
    }
}