.PHONY: start
start: erase up composer

.PHONY: stop
stop: ## stop environment
		docker-compose stop

.PHONY: dbReset
dbReset: ## stop environment
		docker-compose exec php php bin/db-drop.php
		docker-compose exec php php bin/db-create.php
		docker-compose exec php php bin/schema-create.php

.PHONY: down
down: ## stop environment
		docker-compose down

.PHONY: erase
erase: stop

.PHONY: up
up: ## spin up environment
		docker-compose up -d

.PHONY: composer
composer: ## spin up environment
		docker-compose run composer composer install --ignore-platform-reqs

.PHONY: php
php: ## spin up environment
		docker-compose exec php /bin/bash

.PHONY: style
style: ## executes php analizers
		docker-compose exec php ./vendor/bin/phpstan analyse -l 7 -c phpstan.neon src

.PHONY: cs
cs: ## executes php analizers
		docker-compose exec php ./vendor/bin/php-cs-fixer fix --allow-risky=yes

.PHONY: layer
layer: ## layer
		docker-compose exec php ./vendor/bin/deptrac

.PHONY: phpunit
phpunit: ## layer
		docker-compose exec php ./vendor/bin/phpunit
.PHONY: cover
cover: ## layer
		docker-compose exec php php vendor/bin/php-coveralls -v

.PHONY: test
test: cs layer style phpunit