<?php

namespace App\Controller;

use App\Enum\VehicleType;
use App\Exception\NoAvailableParkingSpotException;
use App\Model\DTO\NewGarageRequest;
use App\Model\Garage;
use App\Model\Vehicle\Car;
use App\Model\Vehicle\Motorcycle;
use App\Model\Vehicle\Van;
use App\Model\Vehicle\VehicleInterface;
use App\Service\GarageFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;

final class ParkingGarageController extends AbstractController
{
    private const GARAGE = 'garage';
    private const INDEX_ROUTE = 'app_parking_index';

    #[Route('/', name: self::INDEX_ROUTE, methods: [Request::METHOD_GET])]
    public function index(Request $request): Response
    {
        return $this->render('index.html.twig', ['garage' => $request->getSession()->get(self::GARAGE)]);
    }

    #[Route(path: '/new', name: 'app_parking_new', methods: [Request::METHOD_POST])]
    public function new(#[MapRequestPayload] NewGarageRequest $garageRequest, Request $request): Response
    {
        $garage = GarageFactory::create($garageRequest->floors, $garageRequest->spots);
        $request->getSession()->set(self::GARAGE, $garage);

        return $this->redirectToRoute(self::INDEX_ROUTE);
    }

    #[Route(
        '/park/{vehicleType}',
        name: 'app_parking_park',
        requirements: ['vehicleType' => new EnumRequirement(VehicleType::class)],
    )]
    public function parkVehicle(VehicleType $vehicleType, Request $request): Response
    {
        $vehicle = $this->getVehicle($vehicleType);

        /** @var Garage $garage */
        $garage = $request->getSession()->get(self::GARAGE);
        try {
            $garage->parkVehicle($vehicle);
            $this->addFlash('success', 'Succesfully parked a ' . $vehicleType->value . '.');
        } catch (NoAvailableParkingSpotException) {
            $this->addFlash('danger', 'Could not park a ' . $vehicleType->value . '.');
        }

        return $this->redirectToRoute(self::INDEX_ROUTE);
    }

    #[Route(
        '/check/{vehicleType}',
        name: 'app_parking_check',
        requirements: ['vehicleType' => new EnumRequirement(VehicleType::class)],
    )]
    public function hasParkingSpot(VehicleType $vehicleType, Request $request): Response
    {
        $vehicle = $this->getVehicle($vehicleType);

        /** @var Garage $garage */
        $garage = $request->getSession()->get(self::GARAGE);
        if ($garage->hasParkingSpot($vehicle)) {
            $this->addFlash('info', 'There is a parking spot for a ' . $vehicleType->value . '. Welcome, please go in');
        } else {
            $this->addFlash('warning', 'Sorry, no spaces left for a ' . $vehicleType->value);
        }

        return $this->redirectToRoute(self::INDEX_ROUTE);
    }

    #[Route('/clear', name: 'app_parking_clear', methods: [Request::METHOD_GET])]
    public function clear(Request $request): Response
    {
        $request->getSession()->remove(self::GARAGE);
        return $this->redirectToRoute(self::INDEX_ROUTE);
    }

    private function getVehicle(VehicleType $vehicleType): VehicleInterface
    {
        return match ($vehicleType) {
            VehicleType::Van => new Van(),
            VehicleType::Car => new Car(),
            VehicleType::Motorcycle => new Motorcycle(),
        };
    }
}