setup:
#	@make docker-stop
	@make setup-env
	@make docker-up-build
	@make composer-install
	@make set-permissions
	@make generate-key
# 	@make create-databases
# 	@make migrate-fresh-seed
#	@make drop-databases
# 	@make restore-database

docker-stop:
	docker stop $(docker ps -aq) 2>/dev/null && \
	docker rm $(docker ps -aq) 2>/dev/null && \
	docker rmi -f $(docker images -q) 2>/dev/null && \
	docker volume rm $(docker volume ls -q) 2>/dev/null && \
	docker network rm $(docker network ls -q) 2>/dev/null

setup-env:
	cp -n .env.example .env || true

docker-up-build:
	docker compose up -d --build

composer-install:
	docker exec encompos-app bash -c "composer install --no-interaction --optimize-autoloader"

set-permissions:
	docker exec encompos-app bash -c "chmod -R 777 /var/www/storage"
	docker exec encompos-app bash -c "chmod -R 777 /var/www/bootstrap"
	docker exec encompos-app bash -c "chmod -R 777 /var/www/config"

generate-key:
	docker exec encompos-app bash -c "php artisan key:generate"

create-databases:
	@DB_CONTAINER=$$(grep '^DB_HOST' .env | cut -d '=' -f2); \
	DB_USER=$$(grep '^DB_USERNAME' .env | cut -d '=' -f2); \
	DB_PASS=$$(grep '^DB_PASSWORD' .env | cut -d '=' -f2); \
	DBS=$$(grep '^DB_DATABASE' .env | cut -E -d '=' -f2); \
	DBS=$$(grep '^DB_DATABASE' .env | cut -d '=' -f2); \
	DB_LIST=$$(grep '^DB_DATABASE' .env | cut -d '=' -f2); \
	DB_LIST=$$(grep -E '^DB_DATABASE[0-9]*' .env | cut -d '=' -f2); \
	echo "→ Creating databases inside container $$DB_CONTAINER..."; \
	for db in $$DB_LIST; do \
		echo "   - $$db"; \
		docker exec -i $$DB_CONTAINER \
			mysql -u$$DB_USER -p$$DB_PASS \
			-e "CREATE DATABASE IF NOT EXISTS \`$$db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; \
	done; \
	echo "✓ All databases created!"

migrate-fresh-seed:
	@DB_CONTAINER=$$(grep '^DB_HOST' .env | cut -d '=' -f2); \
	DB_LIST=$$(grep -E '^DB_DATABASE[0-9]*' .env | cut -d '=' -f2); \
	echo "→ Running migrations & seeds for all databases..."; \
	for db in $$DB_LIST; do \
		echo "   → Migrating $$db"; \
		docker exec -i encompos-app bash -c "export DB_DATABASE=$$db && php artisan migrate:fresh --seed --force"; \
	done; \
	echo "✓ Migrations & seeds completed for all databases!"

drop-databases:
	@DB_CONTAINER=$$(grep '^DB_HOST' .env | cut -d '=' -f2); \
	DB_USER=$$(grep '^DB_USERNAME' .env | cut -d '=' -f2); \
	DB_PASS=$$(grep '^DB_PASSWORD' .env | cut -d '=' -f2); \
	DB_LIST=$$(grep -E '^DB_DATABASE[0-9]*' .env | cut -d '=' -f2); \
	if [[ -z "$$DB_LIST" ]]; then \
		echo "❌ No databases found in .env to drop!"; \
		exit 1; \
	fi; \
	echo "→ Dropping databases inside container $$DB_CONTAINER..."; \
	for db in $$DB_LIST; do \
		echo "   - Dropping $$db"; \
		docker exec -i $$DB_CONTAINER \
			mysql -u$$DB_USER -p$$DB_PASS -e "DROP DATABASE IF EXISTS \`$$db\`;"; \
	done; \
	echo "✓ All databases dropped successfully!"

restore-database:
	cd personal && bash -c "bash restore.sh"
