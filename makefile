init: build-env restart composer-deps

restart: down build up

up:
	docker-compose -p drom-test -f ./docker/docker-compose.yaml up -d

build:
	docker-compose -p drom-test -f ./docker/docker-compose.yaml build --pull

down:
	docker-compose -p drom-test -f ./docker/docker-compose.yaml down --remove-orphans

build-env:
	@echo "UID=$$(id -u)" > ./docker/.env

composer-deps:
	docker exec -it drom-php composer install --working-dir=task-2

shell:
	docker exec -it drom-php sh

