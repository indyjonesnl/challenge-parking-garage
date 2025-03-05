<?php

namespace App\Model\Vehicle;

use App\Enum\VehicleSize;
use App\Enum\VehicleType;

final class Van implements VehicleInterface
{
    public function getType(): VehicleType
    {
        return VehicleType::Van;
    }

    public function getSize(): VehicleSize
    {
        return VehicleSize::ONE_AND_HALF;
    }
}