<?php

namespace App\Model;

use App\Enum\ParkingSpotStatus;
use App\Enum\VehicleSize;
use App\Enum\VehicleType;
use App\Exception\NoAvailableParkingSpotException;
use App\Model\Vehicle\VehicleInterface;

final class GarageFloor implements ParkingInterface
{
    /** @var array<string, ParkingSpot> */
    private array $emptyParkingSpots = [];
    /** @var array<string, ParkingSpot> */
    private array $halfParkingSpots = [];
    /** @var array<string, ParkingSpot> */
    private array $occupiedParkingSpots = [];

    private readonly int $parkingSpotCount;

    public function __construct(
        private readonly int $floorLevel,
        ?int $parkingSpotCount = null,
        ParkingSpot ...$parkingSpots,
    )
    {
        $this->parkingSpotCount = $parkingSpotCount ?? count($parkingSpots);
        foreach ($parkingSpots as $parkingSpot) {
            match ($parkingSpot->getParkingSpotStatus()) {
                ParkingSpotStatus::EMPTY => $this->emptyParkingSpots[$parkingSpot->getId()] = $parkingSpot,
                ParkingSpotStatus::HALF_OCCUPIED => $this->halfParkingSpots[$parkingSpot->getId()] = $parkingSpot,
                ParkingSpotStatus::FULLY_OCCUPIED => $this->occupiedParkingSpots[$parkingSpot->getId()] = $parkingSpot,
            };
        }
    }

    public static function create(
        int $floorLevel,
        int $parkingSpotSize,
        ParkingSpotStatus $parkingSpotStatus = ParkingSpotStatus::EMPTY,
    ): self
    {
        $parkingSpots = [];
        for ($i = 0; $i < $parkingSpotSize; $i++) {
            $parkingSpots[] = new ParkingSpot($i, $parkingSpotStatus);
        }
        return new self($floorLevel, $parkingSpotSize, ...$parkingSpots);
    }

    public function hasParkingSpot(VehicleInterface $vehicle): bool
    {
        // Vans can only park on the first floor.
        if ($vehicle->getType() === VehicleType::Van && $this->getFloorLevel() > 1) {
            return false;
        }

        return match ($vehicle->getSize()) {
            VehicleSize::FULL => $this->emptyParkingSpots !== [],
            VehicleSize::HALF => ($this->emptyParkingSpots !== []) || ($this->halfParkingSpots !== []),
            VehicleSize::ONE_AND_HALF => (count($this->emptyParkingSpots) > 1) || (count($this->halfParkingSpots) >= 1 && count($this->emptyParkingSpots) >= 1),
        };
    }

    /** @throws NoAvailableParkingSpotException */
    public function parkVehicle(VehicleInterface $vehicle): void
    {
        if ($vehicle->getSize() === VehicleSize::HALF) {
            if ($this->halfParkingSpots !== []) {
                $parkingSpot = array_pop($this->halfParkingSpots);
                $parkingSpot->setParkingSpotStatus(ParkingSpotStatus::FULLY_OCCUPIED);
                $this->occupiedParkingSpots[] = $parkingSpot;
            } elseif ($this->emptyParkingSpots !== []) {
                $parkingSpot = array_pop($this->emptyParkingSpots);
                $parkingSpot->setParkingSpotStatus(ParkingSpotStatus::HALF_OCCUPIED);
                $this->halfParkingSpots[] = $parkingSpot;
            } else {
                throw new NoAvailableParkingSpotException();
            }
        } elseif ($vehicle->getSize() === VehicleSize::FULL) {
            if ($this->emptyParkingSpots !== []) {
                $parkingSpot = array_pop($this->emptyParkingSpots);
                $parkingSpot->setParkingSpotStatus(ParkingSpotStatus::FULLY_OCCUPIED);
                $this->occupiedParkingSpots[] = $parkingSpot;
            } else {
                throw new NoAvailableParkingSpotException();
            }
        } elseif ($vehicle->getSize() === VehicleSize::ONE_AND_HALF) {
            $emptyParkingSpots = count($this->emptyParkingSpots);
            if ($this->halfParkingSpots !== [] && $emptyParkingSpots > 0) {
                $parkingSpot1 = array_pop($this->emptyParkingSpots);
                $parkingSpot1->setParkingSpotStatus(ParkingSpotStatus::FULLY_OCCUPIED);
                $this->occupiedParkingSpots[] = $parkingSpot1;
                $parkingSpot2 = array_pop($this->halfParkingSpots);
                $parkingSpot2->setParkingSpotStatus(ParkingSpotStatus::FULLY_OCCUPIED);
                $this->occupiedParkingSpots[] = $parkingSpot2;
            } elseif ($emptyParkingSpots > 1) {
                $parkingSpot1 = array_pop($this->emptyParkingSpots);
                $parkingSpot1->setParkingSpotStatus(ParkingSpotStatus::FULLY_OCCUPIED);
                $this->occupiedParkingSpots[] = $parkingSpot1;
                $parkingSpot2 = array_pop($this->emptyParkingSpots);
                $parkingSpot2->setParkingSpotStatus(ParkingSpotStatus::HALF_OCCUPIED);
                $this->halfParkingSpots[] = $parkingSpot2;
            } else {
                throw new NoAvailableParkingSpotException();
            }
        }
    }

    public function getStatus(): ParkingStatus
    {
        return new ParkingStatus(
            count($this->emptyParkingSpots),
            count($this->halfParkingSpots),
            count($this->occupiedParkingSpots),
        );
    }

    public function getParkingSpotCount(): int
    {
        return $this->parkingSpotCount;
    }

    public function getFloorLevel(): int
    {
        return $this->floorLevel;
    }
}