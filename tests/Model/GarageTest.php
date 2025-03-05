<?php

namespace Model;

use App\Enum\ParkingSpotStatus;
use App\Exception\DuplicateFloorIdsException;
use App\Exception\FirstFloorException;
use App\Exception\NonSequentialIdsException;
use App\Model\Garage;
use App\Model\GarageFloor;
use App\Model\ParkingStatus;
use App\Model\ParkingSpot;
use App\Model\Vehicle\Car;
use App\Model\Vehicle\Motorcycle;
use App\Model\Vehicle\Van;
use App\Model\Vehicle\VehicleInterface;
use PHPUnit\Framework\TestCase;

final class GarageTest extends TestCase
{
    /** @dataProvider provideHasParkingSpotData */
    public function testHasParkingSpot(bool $expectation, Garage $garage, VehicleInterface $vehicle): void
    {
        self::assertSame($expectation, $garage->hasParkingSpot($vehicle));
    }

    public function provideHasParkingSpotData(): array
    {
        $car = new Car();
        $van = new Van();
        $motorCycle = new Motorcycle();

        $emptyGarage = new Garage(
            GarageFloor::create(1, 2),
            GarageFloor::create(2, 2),
        );

        return [
            'car and empty garage' => [
                true,
                $emptyGarage,
                $car,
            ],
            'van and empty garage' => [
                true,
                $emptyGarage,
                $van,
            ],
            'motorcycle and empty garage' => [
                true,
                $emptyGarage,
                $motorCycle,
            ],
            'van is allowed on first floor' => [
                true,
                new Garage(
                    // Empty first floor
                    GarageFloor::create(1, 2),
                    // Full second floor
                    GarageFloor::create(2, 2, ParkingSpotStatus::FULLY_OCCUPIED),
                ),
                $van,
            ],
            'vans are not allowed on second floor' => [
                false,
                new Garage(
                    // Full first floor
                    GarageFloor::create(1, 2, ParkingSpotStatus::FULLY_OCCUPIED),
                    // Empty second floor
                    GarageFloor::create(2, 2),
                ),
                $van,
            ],
            'car with full first floor 1 space left' => [
                true,
                new Garage(
                    GarageFloor::create(1, 2, ParkingSpotStatus::FULLY_OCCUPIED),
                    new GarageFloor(
                        2,
                        2,
                        new ParkingSpot(1, ParkingSpotStatus::FULLY_OCCUPIED),
                        new ParkingSpot(2),
                    ),
                ),
                $car,
            ],
            'car with full first floor 1,5 spaces left' => [
                true,
                new Garage(
                    GarageFloor::create(1, 2, ParkingSpotStatus::FULLY_OCCUPIED),
                    new GarageFloor(
                        2,
                        2,
                        new ParkingSpot(1, ParkingSpotStatus::HALF_OCCUPIED),
                        new ParkingSpot(2),
                    ),
                ),
                $car,
            ],
            'car and only half spot available' => [
                false,
                new Garage(
                    GarageFloor::create(1, 2, ParkingSpotStatus::FULLY_OCCUPIED),
                    new GarageFloor(
                        2,
                        2,
                        new ParkingSpot(1, ParkingSpotStatus::FULLY_OCCUPIED),
                        new ParkingSpot(2, ParkingSpotStatus::HALF_OCCUPIED),
                    ),
                ),
                $car,
            ],
            'motorcycle and only half spot available' => [
                true,
                new Garage(
                    GarageFloor::create(1, 2, ParkingSpotStatus::FULLY_OCCUPIED),
                    new GarageFloor(
                        2,
                        2,
                        new ParkingSpot(1, ParkingSpotStatus::FULLY_OCCUPIED),
                        new ParkingSpot(2, ParkingSpotStatus::HALF_OCCUPIED),
                    ),
                ),
                $motorCycle,
            ],
        ];
    }

    /** @dataProvider provideFloorData */
    public function testGarageValidation(?string $exceptionFqn, GarageFloor ...$floors): void
    {
        if ($exceptionFqn) {
            self::expectException($exceptionFqn);
        }

        $garage = new Garage(...$floors);

        self::assertInstanceOf(Garage::class, $garage);
    }

    public function provideFloorData(): array
    {
        return [
            'valid floors' => [
                null,
                GarageFloor::create(1, 2),
                GarageFloor::create(2, 2),
            ],
            'non-sequential floor ids' => [
                NonSequentialIdsException::class,
                GarageFloor::create(1, 2),
                GarageFloor::create(3, 2),
            ],
            'floorids dont start at 1' => [
                FirstFloorException::class,
                GarageFloor::create(2, 2),
                GarageFloor::create(3, 2),
            ],
            'duplicate floorids' => [
                DuplicateFloorIdsException::class,
                GarageFloor::create(1, 2),
                GarageFloor::create(1, 2),
            ],
            'skipping 1 floorid' => [
                NonSequentialIdsException::class,
                GarageFloor::create(1, 2),
                GarageFloor::create(3, 2),
            ],
            'skipping 2 floorids' => [
                NonSequentialIdsException::class,
                GarageFloor::create(1, 2),
                GarageFloor::create(4, 2),
            ],
        ];
    }

    public function testGetFloor(): void
    {
        $floor1 = GarageFloor::create(1, 5);
        $floor2 = GarageFloor::create(2, 4);
        $floor3 = GarageFloor::create(3, 3);
        $floors = [$floor1, $floor2, $floor3];

        $garage = new Garage(
            $floor1,
            $floor2,
            $floor3,
        );

        for($i = 1; $i < 4; $i++) {
            self::assertSame($garage->getFloor($i), $floors[$i-1]);
        }
    }

    public function testParkingSpotCount(): void
    {
        $garage = new Garage(
            GarageFloor::create(1, 1),
            GarageFloor::create(2, 2),
            GarageFloor::create(3, 3),
        );

        self::assertSame(6, $garage->getParkingSpotCount());
    }

    public function testParkVehicle(): void
    {
        $van = new Van();
        $car = new Car();
        $motorCycle = new Motorcycle();

        $garage = new Garage(
            GarageFloor::create(1, 3),
            GarageFloor::create(2, 2),
            GarageFloor::create(3,1),
        );

        $garage->parkVehicle($van);
        self::assertEquals(new ParkingStatus(4, 1, 1), $garage->getStatus());
        $garage->parkVehicle($van);
        self::assertEquals(new ParkingStatus(3, 0, 3), $garage->getStatus());

        $garage->parkVehicle($car);
        self::assertEquals(new ParkingStatus(2, 0, 4), $garage->getStatus());
        $garage->parkVehicle($car);
        self::assertEquals(new ParkingStatus(1, 0, 5), $garage->getStatus());

        $garage->parkVehicle($motorCycle);
        self::assertEquals(new ParkingStatus(0, 1, 5), $garage->getStatus());
        $garage->parkVehicle($motorCycle);
        self::assertEquals(new ParkingStatus(0, 0, 6), $garage->getStatus());
    }
}