.PHONY: help build up start down destroy stop enter restart test

PROJECT = codechallenge
COMPOSE_CMD = docker compose -p $(PROJECT)

help: ## Show the available commands.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build docker images
	$(COMPOSE_CMD) build --pull

up: ## Build and start docker containers
	$(COMPOSE_CMD) up -d

start: ## Start docker containers
	$(COMPOSE_CMD) start

down: ## Remove docker containers
	$(COMPOSE_CMD) down

stop: ## Stop docker containers
	$(COMPOSE_CMD) stop

destroy: ## Delete docker images
	$(COMPOSE_CMD) down -v --rmi

restart: ## "make restart" restarts all services. "make restart CONTAINER=php" will restart the given service.
	$(COMPOSE_CMD) restart $(CONTAINER)

enter: ## Enter the PHP container
	$(COMPOSE_CMD) exec -it app ash

ps: ## Display Docker containers for this project
	$(COMPOSE_CMD) ps

test: ## Run tests with human readable output
	$(COMPOSE_CMD) exec app bin/phpunit --testdox