<?php

namespace App\Service;

use App\Model\Garage;
use App\Model\GarageFloor;

final class GarageFactory
{
    public static function create(int $nrOfFloors, int $highestNrOfParkingSpots): Garage
    {
        $floors = [];
        for ($i = 1; $i <= $nrOfFloors; $i++) {
            $floors[] = GarageFloor::create($i, max(2, $highestNrOfParkingSpots - ($i - 1)));
        }

        return new Garage(...$floors);
    }
}