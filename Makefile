setup:
# 	@make docker-stop
	@make setup-env
	@make docker-up-build
	@make composer-install
	@make set-permissions
	@make generate-key
	@make migrate-fresh-seed
	@make restore-database

docker-stop:
	docker compose stop

setup-env:
	cp .env.docker .env

docker-up-build:
	docker compose up -d --build

composer-install:
	docker exec encompos-app bash -c "composer install"

set-permissions:
	docker exec encompos-app bash -c "chmod -R 777 /var/www/storage"
	docker exec encompos-app bash -c "chmod -R 777 /var/www/bootstrap"

generate-key:
	docker exec encompos-app bash -c "php artisan key:generate"

migrate-fresh-seed:
	docker exec encompos-app bash -c "php artisan migrate:fresh --seed"

restore-database:
	cd database-manager && bash -c "bash restore.sh"
