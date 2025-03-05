# challenge-parking-garage
An assignment to build a parking garage application in PHP

## Machine Requirements

For this challenge to run, you'll have to have Docker running or have PHP running locally on your machine.

This README assumes you're using Docker to run the challenge.

1. Start the Docker containers; `make up`

2. Run your tests; `make test`

3. Stop the docker containers; `make down`

Without further ado - good luck with the challenge and, even more importantly, HAVE FUN!

## Running this application

1. Start the Docker containers; `make up`.
2. Enter into the running PHP container; `make enter`.
3. Inside the container, run Composer install to install dependencies: `composer install`.
4. Use a browser to navigate to http://localhost:8080/ and review the application.

## Assumptions and design decisions

Assumptions:
- I first made an implementation that searched for empty parking spots and then remembered / reused the location of the parkingspot
  (floor level and parking spot index) to change the state of the parking spot to (half-)occupied. But my assumption is
  this would be a bad choice, when checking for empty parking spots and actually occupying parking spots can be an
  asynchronous process. Especially if there are multiple points of ingress. So I've purposefully rewritten the application
  to check if there are available parking spots and to check again when occupying (changing the state of) the parking spot(s).

Design choices:
- Decoupling of vehicle and vehicle sizes allows for re-use of other vehicles with identical vehicle sizes.
- By ['indexing'](src/Model/GarageFloor.php) the occupied, half-occupied and empty parking spots, the application prevents having to search
  through (possible large amounts) of parking spots to determine free or occupied parking spots.
- Added insights into the state of parking spots (whether they are empty, half-occupied or occupied). This allows easy
  automated testing, human debugging and visualizing the state of the parking garage and/or parking floor in a UI.
- The accompanying document set the expectation to keep things simple by not using any frameworks. I have chosen to use
  a framework in order not to reimplement functionality like dependency injection or HTTP routing.
- I've chosen to accompany the backend with a simple web frontend. This allows for easy testing and demonstration.
- I've added an Nginx Docker service to serve as a reverse proxy so the application can be used by a browser on the
  Docker host.

## Known shortcomings of current implementation

A van can be designated to park on 1 and a half parking spots and the assignment of parking spots does not consider
the physical location of these parking spots. Since location was not a requirement, it is not taken into account.