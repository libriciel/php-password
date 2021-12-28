PHPCS=vendor/bin/phpcs
PHPCBF=vendor/bin/phpcbf
PHPCS_ARGS=-ps --standard=PSR12 --ignore=vendor/ ./

PHPCPD=vendor/bin/phpcpd
PHPCPD_ARGS=--exclude vendor/ .

PHPMD=vendor/bin/phpmd
PHPMD_ARGS=--exclude 'vendor/*' . ansi phpmd_rules.xml

PHPUNIT=vendor/bin/phpunit
PHPUNIT_COVERAGE_ARGS=--coverage-text --colors=never --coverage-clover coverage/coverage.xml --log-junit coverage/logfile.xml

COMPOSER=composer

.DEFAULT_GOAL := help

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

review: phpunit phpcs phpcpd phpmd ## Run all reviews tools (PHPUnit, PHP Code Sniffer, Copy/Paste Detector, Mess Detector)

.PHONY: phpcs
phpcs: ## Check code style with PHP Code Sniffer
	$(PHPCS) $(PHPCS_ARGS)

.PHONY: phpcbf
phpcbf: ## Fix all fixable code style errors
	$(PHPCBF) $(PHPCS_ARGS)

.PHONY: phpunit
phpunit: vendor ## Run PHPUnit
	$(PHPUNIT)

.PHONY: coverage
coverage: vendor ## Run PHPUnit with code coverage
	 $(PHPUNIT) $(PHPUNIT_COVERAGE_ARGS)

.PHONY: phpcpd
phpcpd: vendor ## Run Copy/Paste Detector (CPD)
	$(PHPCPD) $(PHPCPD_ARGS)

.PHONY: phpmd
phpmd: vendor ## Run Mess Detector
	$(PHPMD) $(PHPMD_ARGS)

clean: ## Cleaning all but vendor directory
	rm -f .phpunit.result.cache
	rm -rf coverage

mrproper: clean ## Cleaning all and vendor directory
	rm -rf vendor/

install: composer.json composer.lock ## Install composer dependencies
	$(COMPOSER) install
