#!/usr/bin/make

SHELL = /bin/bash

build_image:
	docker build -f docker/Dockerfile ./ -t oauth2tier

checkfiles:
	@if [ ! -f .env ]; then echo "error: no ./.env file"; exit 1; fi
	@if [ ! -f access.log ]; then touch access.log; fi

up: checkfiles
	@cd docker; docker compose up -d

down:
	@cd docker; docker compose down -v

ps:
	@cd docker; docker compose ps

logs:
	@cd docker; docker compose logs

reup: down up

restart:
	@cd docker; docker compose restart
