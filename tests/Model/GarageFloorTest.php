<?php

namespace Model;

use App\Enum\ParkingSpotStatus;
use App\Exception\NoAvailableParkingSpotException;
use App\Model\GarageFloor;
use App\Model\ParkingStatus;
use App\Model\ParkingSpot;
use App\Model\Vehicle\Car;
use App\Model\Vehicle\Motorcycle;
use App\Model\Vehicle\Van;
use App\Model\Vehicle\VehicleInterface;
use PHPUnit\Framework\TestCase;

final class GarageFloorTest extends TestCase
{
    /** @dataProvider provideHasParkingSpotData */
    public function testHasParkingSpot(bool $expectation, GarageFloor $floor, VehicleInterface $vehicle): void
    {
        self::assertSame($expectation, $floor->hasParkingSpot($vehicle));
    }

    public function provideHasParkingSpotData(): array
    {
        $motorCycle = new Motorcycle();
        $car = new Car();
        $van = new Van();

        return [
            'empty ground floor' => [
                true,
                new GarageFloor(1, 1, new ParkingSpot(1)),
                $car,
            ],
            'full ground floor' => [
                false,
                new GarageFloor(1, 1, new ParkingSpot(1, ParkingSpotStatus::FULLY_OCCUPIED)),
                $car,
            ],
            'could fit van' => [
                true,
                new GarageFloor(
                    1,
                    2,
                    new ParkingSpot(1),
                    new ParkingSpot(2, ParkingSpotStatus::HALF_OCCUPIED),
                ),
                $van,
            ],
            'could fit car' => [
                true,
                new GarageFloor(
                    1,
                    2,
                    new ParkingSpot(1, ParkingSpotStatus::FULLY_OCCUPIED),
                    new ParkingSpot(2),
                ),
                $car,
            ],
            'could fit motorcycle' => [
                true,
                new GarageFloor(
                    1,
                    2,
                    new ParkingSpot(1, ParkingSpotStatus::FULLY_OCCUPIED),
                    new ParkingSpot(2, ParkingSpotStatus::HALF_OCCUPIED),
                ),
                $motorCycle,
            ],
            'cannot fit van' => [
                false,
                new GarageFloor(
                    1,
                    1,
                    new ParkingSpot(1, ParkingSpotStatus::EMPTY),
                ),
                $van,
            ],
            'van only half spaces' => [
                false,
                new GarageFloor(
                    1,
                    3,
                    new ParkingSpot(1, ParkingSpotStatus::HALF_OCCUPIED),
                    new ParkingSpot(2, ParkingSpotStatus::HALF_OCCUPIED),
                    new ParkingSpot(3, ParkingSpotStatus::HALF_OCCUPIED),
                ),
                $van,
            ],
            'car only half spaces' => [
                false,
                new GarageFloor(
                    1,
                    2,
                    new ParkingSpot(1, ParkingSpotStatus::HALF_OCCUPIED),
                    new ParkingSpot(2, ParkingSpotStatus::HALF_OCCUPIED),
                ),
                $car,
            ],
            'van with empty secondfloor' => [
                false,
                new GarageFloor(
                    2,
                    2,
                    new ParkingSpot(1),
                    new ParkingSpot(2),
                ),
                $van,
            ],
            'car with empty secondfloor' => [
                true,
                new GarageFloor(
                    2,
                    1,
                    new ParkingSpot(1),
                ),
                $car,
            ],
        ];
    }

    /** @dataProvider provideCountData */
    public function testGetParkingSpotCount(int $expectedCount, GarageFloor $garageFloor): void
    {
        self::assertSame($expectedCount, $garageFloor->getParkingSpotCount());
    }

    public function provideCountData(): array
    {
        return [
            [6, GarageFloor::create(1, 6)],
            [9, GarageFloor::create(1, 9)],
        ];
    }

    /** @dataProvider provideParkVehicleData */
    public function testParkVehicle(
        ParkingStatus $expectedStatusAfterParking,
        GarageFloor $garageFloor,
        VehicleInterface $vehicle,
    ): void
    {
        $garageFloor->parkVehicle($vehicle);
        self::assertEquals($expectedStatusAfterParking, $garageFloor->getStatus());
    }

    public function provideParkVehicleData(): array
    {
        $motorCycle = new Motorcycle();
        $car = new Car();
        $van = new Van();

        return [
            'car and only 1 spot' => [
                new ParkingStatus(0, 0, 1),
                GarageFloor::create(1, 1),
                $car,
            ],
            'car and two spots' => [
                new ParkingStatus(1, 0, 1),
                GarageFloor::create(1, 2),
                $car,
            ],
            'motorcycle and two spots' => [
                new ParkingStatus(1, 1, 0),
                GarageFloor::create(1, 2),
                $motorCycle,
            ],
            'van and two spots' => [
                new ParkingStatus(0, 1, 1),
                GarageFloor::create(1, 2),
                $van,
            ],
        ];
    }

    /** @dataProvider provideParkVehicleExceptionData */
    public function testParkVehicleException(GarageFloor $floor, VehicleInterface $vehicle): void
    {
        self::expectException(NoAvailableParkingSpotException::class);

        $floor->parkVehicle($vehicle);
    }

    public function provideParkVehicleExceptionData(): array
    {
        $car = new Car();
        $van = new Van();
        $motorcycle = new Motorcycle();

        return [
            'van but only half spaces' => [
                GarageFloor::create(1, 2, ParkingSpotStatus::HALF_OCCUPIED),
                $van,
            ],
            'car but only half spaces' => [
                GarageFloor::create(1, 2, ParkingSpotStatus::HALF_OCCUPIED),
                $car,
            ],
            'motorcycle but garage is full' => [
                GarageFloor::create(2, 2, ParkingSpotStatus::FULLY_OCCUPIED),
                $motorcycle,
            ]
        ];
    }

    public function testParkVanPrioritizesHalfSpaces(): void
    {
        $van = new Van();
        $floor = GarageFloor::create(1, 4);
        $floor->parkVehicle($van);
        self::assertEquals(new ParkingStatus(2, 1, 1), $floor->getStatus());

        $floor->parkVehicle($van);
        self::assertEquals(new ParkingStatus(1, 0, 3), $floor->getStatus());
    }

    public function testParkMotorcyclePrioritizesHalfSpaces(): void
    {
        $motorcycle = new Motorcycle();

        $floor = GarageFloor::create(1, 2);
        $floor->parkVehicle($motorcycle);
        self::assertEquals(new ParkingStatus(1, 1, 0), $floor->getStatus());

        $floor->parkVehicle($motorcycle);
        self::assertEquals(new ParkingStatus(1, 0, 1), $floor->getStatus());
    }
}