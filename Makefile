setup:
#	@make docker-stop
# 	@make setup-env
# 	@make docker-up-build
# 	@make composer-install
# 	@make set-permissions
# 	@make gen-key
# 	@make create-db
# 	@make fresh-db
#	@make drop-db
# 	@make restore-db
#	@make backup-bd
#	@$(MAKE) undoc-setup-env
# 	@$(MAKE) undoc-composer-install
# 	@$(MAKE) undoc-set-permissions
# 	@$(MAKE) undoc-gen-key
# 	@$(MAKE) undoc-create-db
# 	@$(MAKE) undoc-drop-db
# 	@$(MAKE) undoc-freshseed-db

docker-stop:
	@read -p ""Are you sure you want to stop and remove any container with all its volumes and networks!!! (y/n) " answer; \
	if [ "$$answer" != "y" ]; then \
		echo "Aborted."; \
		exit 1; \
	fi
	-docker stop $$(docker ps -aq)
	-docker rm $$(docker ps -aq)
	-docker rmi -f $$(docker images -q)
	-docker volume rm $$(docker volume ls -q)
	-docker network rm $$(docker network ls -q)

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

gen-key:
	docker exec encompos-app bash -c "php artisan key:generate"

create-db:
	@read -p "Create databases using .env name? (y/n) " answer; \
	if [ "$$answer" != "y" ]; then \
		echo "Aborted."; \
		exit 1; \
	fi
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

fresh-db:
	@read -p "Are you sure you want to Drop All Database and Create Fresh seed? (y/n) " answer; \
	if [ "$$answer" != "y" ]; then \
		echo "Aborted."; \
		exit 1; \
	fi
	@DB_CONTAINER=$$(grep '^DB_HOST' .env | cut -d '=' -f2); \
	DB_LIST=$$(grep -E '^DB_DATABASE[0-9]*' .env | cut -d '=' -f2); \
	echo "→ Running migrations & seeds for all databases..."; \
	for db in $$DB_LIST; do \
		echo "   → Migrating $$db"; \
		docker exec -i encompos-app bash -c "export DB_DATABASE=$$db && php artisan migrate:fresh --seed --force"; \
	done; \
	echo "✓ Migrations & seeds completed for all databases!"

drop-db:
	@read -p "Are you sure you want to Drop all databases? (y/n) " answer; \
	if [ "$$answer" != "y" ]; then \
		echo "Aborted."; \
		exit 1; \
	fi
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

restore-db:
	cd personal && bash -c "bash restore.sh"
backup-db:
	cd personal && bash -c "bash restore.sh"
clear-cache:
	docker compose exec app bash -c "\
	php artisan cache:clear && \
	php artisan config:clear && \
	php artisan route:clear && \
	php artisan view:clear && \
	php artisan event:clear && \
	composer dump-autoload && \
	php -r 'opcache_reset();'\
	"






#For Without Docker
undoc-setup-env:
	cp .env.example .env || true

undoc-composer-install:
	composer install --no-interaction --optimize-autoloader

undoc-set-permissions:
	chmod -R 777 storage
	chmod -R 777 bootstrap
	chmod -R 777 config

undoc-gen-key:
	php artisan key:generate

undoc-create-db:
	@echo "→ Creating all databases..."
	@DB_USER=$$(grep '^DB_USERNAME' .env | cut -d '=' -f2); \
	DB_PASS=$$(grep '^DB_PASSWORD' .env | cut -d '=' -f2); \
	grep -E '^DB_DATABASE' .env | while IFS='=' read -r var db; do \
		db=$$(echo $$db | tr -d '[:space:]'); \
		if [ -n "$$db" ]; then \
			echo "   → Creating database $$db if not exists..."; \
			mysql -u"$$DB_USER" -p"$$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$$db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; \
		fi; \
	done
	@echo "✓ All databases created."


undoc-drop-db:
	@read -p "Are you sure you want to drop all databases? (y/n) " answer; \
	if [ "$$answer" != "y" ]; then \
		echo "Aborted."; \
		exit 1; \
	fi; \
	echo "→ Dropping all databases..."; \
	DB_USER=$$(grep '^DB_USERNAME' .env | cut -d '=' -f2); \
	DB_PASS=$$(grep '^DB_PASSWORD' .env | cut -d '=' -f2); \
	grep -E '^DB_DATABASE' .env | while IFS='=' read -r var db; do \
		db=$$(echo $$db | tr -d '[:space:]'); \
		if [ -n "$$db" ]; then \
			echo "   → Dropping database $$db..."; \
			mysql -u"$$DB_USER" -p"$$DB_PASS" -e "DROP DATABASE IF EXISTS \`$$db\`;"; \
		fi; \
	done; \
	echo "✓ All databases dropped."


undoc-freshseed-db:
	@echo "→ Running fresh migrations and seed for all databases..."
	DB_USER=$$(grep '^DB_USERNAME' .env | cut -d '=' -f2); \
	DB_PASS=$$(grep '^DB_PASSWORD' .env | cut -d '=' -f2); \
	grep -E '^DB_DATABASE' .env | while IFS='=' read -r var db; do \
		db=$$(echo $$db | tr -d '[:space:]'); \
		if [ -n "$$db" ]; then \
			echo "   → Dropping database $$db if exists..."; \
			mysql -u"$$DB_USER" -p"$$DB_PASS" -e "DROP DATABASE IF EXISTS \`$$db\`;"; \
			echo "   → Creating database $$db..."; \
			mysql -u"$$DB_USER" -p"$$DB_PASS" -e "CREATE DATABASE \`$$db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; \
			echo "   → Migrating & seeding $$db..."; \
			DB_DATABASE="$$db" php artisan migrate:fresh --seed --force; \
		fi; \
	done; \
	echo "✓ All databases migrated & seeded."

undoc-clear-cache:
	@echo "→ Clearing Laravel caches..."
	php artisan cache:clear && \
	php artisan config:clear && \
	php artisan route:clear && \
	php artisan view:clear && \
	php artisan event:clear && \
	composer dump-autoload && \
	php -r 'opcache_reset();'
	@echo "✓ All caches cleared."
