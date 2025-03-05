<?php

namespace App\Model\Vehicle;

use App\Enum\VehicleSize;
use App\Enum\VehicleType;

interface VehicleInterface
{
    public function getType(): VehicleType;
    public function getSize(): VehicleSize;
}