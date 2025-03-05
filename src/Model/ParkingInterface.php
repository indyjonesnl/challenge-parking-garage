<?php

namespace App\Model;

use App\Exception\NoAvailableParkingSpotException;
use App\Model\Vehicle\VehicleInterface;

interface ParkingInterface
{
    public function hasParkingSpot(VehicleInterface $vehicle): bool;

    /** @throws NoAvailableParkingSpotException */
    public function parkVehicle(VehicleInterface $vehicle): void;

    public function getStatus(): ParkingStatus;
}