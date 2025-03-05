<?php

namespace Service;

use App\Model\Garage;
use App\Model\GarageFloor;
use App\Service\GarageFactory;
use PHPUnit\Framework\TestCase;

final class GarageFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $expectation = new Garage(
           GarageFloor::create(1, 5),
           GarageFloor::create(2, 4),
           GarageFloor::create(3, 3),
           // A garage floor will always have at least 2 parking spots, when using the factory.
           GarageFloor::create(4, 2),
           GarageFloor::create(5, 2),
           GarageFloor::create(6, 2),
        );

        $garage = GarageFactory::create(6, 5);

        self::assertEquals($expectation, $garage);
    }
}