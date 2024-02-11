init: build-env restart

restart: down build up

up:
	docker-compose -p drom-test -f ./docker/docker-compose.yaml up -d

build:
	docker-compose -p drom-test -f ./docker/docker-compose.yaml build --pull

down:
	docker-compose -p drom-test -f ./docker/docker-compose.yaml down --remove-orphans

build-env:
	@echo "UID=$$(id -u)" > ./docker/.env

