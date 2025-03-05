<?php

namespace App\Model\Vehicle;

use App\Enum\VehicleSize;
use App\Enum\VehicleType;

final class Motorcycle implements VehicleInterface
{
    public function getType(): VehicleType
    {
        return VehicleType::Motorcycle;
    }

    public function getSize(): VehicleSize
    {
        return VehicleSize::HALF;
    }
}