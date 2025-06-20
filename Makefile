DOCKER_COMPOSE=docker compose
APP_NAME=app

.DEFAULT_GOAL := help

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: build-app build-vendor

build-app:
	$(DOCKER_COMPOSE) build

build-vendor:  ## Build vendor directory from composer
	$(DOCKER_COMPOSE) run --entrypoint "composer install" $(APP_NAME)

mrproper: clean ## Cleaning all and vendor directory
	rm -rf vendor/

clean: ## Cleaning all but vendor directory
	rm -f .phpunit.result.cache
	rm -rf coverage

bash: ## Obtain a shell console on the app container
	$(DOCKER_COMPOSE) run --remove-orphans $(APP_NAME) bash

fix-cs: ## Fix the project style
	$(DOCKER_COMPOSE) run --entrypoint 'vendor/bin/php-cs-fixer fix' $(APP_NAME)