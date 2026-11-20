DOCKER_COMPOSE=docker compose -f compose.dev.yaml
APP_NAME=app
RUN=$(DOCKER_COMPOSE) run --rm $(APP_NAME)

.DEFAULT_GOAL := help

.PHONY: help build build-app build-vendor mrproper clean bash test coverage phpstan rector ecs fix-cs audit qa
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: build-app build-vendor ## Build the image and the vendor directory

build-app:
	$(DOCKER_COMPOSE) build

build-vendor:  ## Build vendor directory from composer
	$(RUN) composer install

mrproper: clean ## Cleaning all and vendor directory
	rm -rf vendor/

clean: ## Cleaning all but vendor directory
	rm -f .phpunit.result.cache
	rm -rf coverage var

bash: ## Obtain a shell console on the app container
	$(DOCKER_COMPOSE) run --rm --remove-orphans $(APP_NAME) bash

test: ## Run the unit tests
	$(RUN) vendor/bin/phpunit

coverage: ## Show line, branch and path coverage (Xdebug)
	$(DOCKER_COMPOSE) run --rm -e XDEBUG_MODE=coverage $(APP_NAME) php -d memory_limit=-1 -d pcov.enabled=0 vendor/bin/phpunit --path-coverage --coverage-text

phpstan: ## Run the static analysis
	$(RUN) vendor/bin/phpstan analyse

rector: ## Check the code with Rector (dry run)
	$(RUN) vendor/bin/rector process --dry-run

ecs: ## Check the coding standard
	$(RUN) vendor/bin/ecs

fix-cs: ## Fix the project style (ECS then Rector)
	$(RUN) vendor/bin/ecs --fix
	$(RUN) vendor/bin/rector process

audit: ## Check the dependencies for known vulnerabilities
	$(RUN) composer audit --locked --abandoned=report

qa: ecs rector phpstan test audit ## Run every check run by the CI
