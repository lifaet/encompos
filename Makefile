setup:
#	@make docker-stop
	@make setup-env
	@make docker-up-build
	@make create-databases
	@make composer-install
	@make set-permissions
	@make generate-key
	@make migrate-fresh-seed-all
# 	@make restore-database

docker-stop:
	docker compose stop

setup-env:
	cp .env.example .env

docker-up-build:
	docker compose up -d --build

create-databases:
	@DB_CONTAINER=$$(grep '^DB_HOST' .env | cut -d '=' -f2); \
	DB_USER=$$(grep '^DB_USERNAME' .env | cut -d '=' -f2); \
	DB_PASS=$$(grep '^DB_PASSWORD' .env | cut -d '=' -f2); \
	DBS=$$(grep '^DB_DATABASE' .env | cut -d '=' -f2); \
	echo "→ Creating databases inside container $$DB_CONTAINER..."; \
	for db in $$DBS; do \
		echo "   - $$db"; \
		docker exec -i $$DB_CONTAINER \
			mysql -u$$DB_USER -p$$DB_PASS \
			-e "CREATE DATABASE IF NOT EXISTS \`$$db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; \
	done; \
	echo "✓ All databases created!"

composer-install:
	docker exec encompos-app bash -c "composer install"

set-permissions:
	docker exec encompos-app bash -c "chmod -R 777 /var/www/storage"
	docker exec encompos-app bash -c "chmod -R 777 /var/www/bootstrap"
	docker exec encompos-app bash -c "chmod -R 777 /var/www/config"

generate-key:
	docker exec encompos-app bash -c "php artisan key:generate"

migrate-fresh-seed-all:
	@DB_CONTAINER=$$(grep '^DB_HOST' .env | cut -d '=' -f2); \
	echo "→ Running migrations & seeds for all databases..."; \
	DBS=$$(grep '^DB_DATABASE' .env | cut -d '=' -f2); \
	for db in $$DBS; do \
		echo "   → Migrating $$db"; \
		docker exec -i encompos-app bash -c "export DB_DATABASE=$$db && php artisan migrate:fresh --seed"; \
	done; \
	echo "✓ Migrations & seeds completed for all databases!"

restore-database:
	cd personal && bash -c "bash restore.sh"
