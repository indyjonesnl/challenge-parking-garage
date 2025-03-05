<?php

namespace App\Model\Vehicle;

use App\Enum\VehicleSize;
use App\Enum\VehicleType;

final class Car implements VehicleInterface
{
    public function getType(): VehicleType
    {
        return VehicleType::Car;
    }

    public function getSize(): VehicleSize
    {
        return VehicleSize::FULL;
    }
}