<?php

namespace App\Model;

use App\Enum\VehicleType;
use App\Exception\DuplicateFloorIdsException;
use App\Exception\FirstFloorException;
use App\Exception\NoAvailableParkingSpotException;
use App\Exception\NonSequentialIdsException;
use App\Model\Vehicle\VehicleInterface;
use Stringable;

final readonly class Garage implements Stringable, ParkingInterface
{
    /** @var array<int, GarageFloor> $floors */
    private array $floors;

    private int $parkingSpotCount;
    private int $floorCount;

    /**
     * @throws DuplicateFloorIdsException When floors do not have unique IDs.
     * @throws FirstFloorException When the lowest floor does not start at 1.
     * @throws NonSequentialIdsException When floor IDs are not sequential.
     */
    public function __construct(
        GarageFloor ...$floors,
    )
    {
        $floorIds = self::validate($floors);
        // Index each floor with the floorLevel as the index in the array.
        $this->floors = array_combine($floorIds, $floors);
        $this->floorCount = count($floorIds);
        $this->parkingSpotCount = array_sum(array_map(fn(GarageFloor $floor) => $floor->getParkingSpotCount(), $floors));
    }

    public function hasParkingSpot(VehicleInterface $vehicle): bool
    {
        if ($vehicle->getType() === VehicleType::Van) {
            return $this->getFloor(1)->hasParkingSpot($vehicle);
        }

        foreach ($this->floors as $floor) {
            if ($floor->hasParkingSpot($vehicle)) {
                return true;
            }
        }

        return false;
    }

    /** @throws NoAvailableParkingSpotException */
    public function parkVehicle(VehicleInterface $vehicle): void
    {
        if ($vehicle->getType() === VehicleType::Van) {
            $this->getFloor(1)->parkVehicle($vehicle);
            return;
        }

        foreach ($this->floors as $floor) {
            if ($floor->hasParkingSpot($vehicle)) {
                $floor->parkVehicle($vehicle);
                return;
            }
        }

        throw new NoAvailableParkingSpotException();
    }

    public function getFloor(int $id): GarageFloor
    {
        return $this->floors[$id];
    }

    /**
     * @param GarageFloor[] $floors
     * @return int[]
     * @throws FirstFloorException
     * @throws NonSequentialIdsException
     * @throws DuplicateFloorIdsException
     */
    private function validate(array $floors): array
    {
        $floorIds = array_map(fn(GarageFloor $floor) => $floor->getFloorLevel(), $floors);
        $min = $floorIds[array_key_first($floorIds)];

        if ($min !== 1) {
            throw new FirstFloorException();
        }

        if (count(array_unique($floorIds)) != count($floorIds)) {
            throw new DuplicateFloorIdsException();
        }

        $max = $floorIds[array_key_last($floorIds)];
        if ($max !== count($floors)) {
            throw new NonSequentialIdsException();
        }

        $expectedRange = range(1, $max);
        if ($expectedRange !== $floorIds) {
            throw new NonSequentialIdsException();
        }

        return $floorIds;
    }

    public function getParkingSpotCount(): int
    {
        return $this->parkingSpotCount;
    }

    public function __toString(): string
    {
        return $this->floorCount . ' floors with a total of ' . $this->getParkingSpotCount() . ' parking spots.';
    }

    public function getStatus(): ParkingStatus
    {
        $empty = 0;
        $halfOccupied = 0;
        $fullyOccupied = 0;

        foreach ($this->floors as $floor) {
            $status = $floor->getStatus();
            $empty += $status->getEmptyParkingSpots();
            $halfOccupied += $status->getHalfOccupiedParkingSpots();
            $fullyOccupied += $status->getFullyOccupiedParkingSpots();
        }

        return new ParkingStatus(
            $empty,
            $halfOccupied,
            $fullyOccupied,
        );
    }
}