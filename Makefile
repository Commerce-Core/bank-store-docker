DOCKER_COMPOSE := docker compose
PHP_CONTAINER := ecomm-php
PHP_SERVICE := php
NGINX_CONTAINER := ecomm-nginx
WORDPRESS_DB_HOST := mysql

composer_install = cd ..; composer install
docker_php_run = $(DOCKER_COMPOSE) run --rm $(PHP_SERVICE)

.PHONY: all

all: composer-install start

start:
	$(DOCKER_COMPOSE) up -d

start-alt:
	$(DOCKER_COMPOSE) -f docker-compose-alt.yml up -d

stop:
	docker compose stop

composer-install:
	$(docker_php_run) $(composer_install)

phprun:
	$(docker_php_run) $(CMD)

delete-symlinks:
	find public/wp-content/plugins public/wp-content/themes -maxdepth 1 -type l -delete

nginx-reload:
	docker exec $(NGINX_CONTAINER) nginx -s reload

phpdoc:
	docker exec $(PHP_CONTAINER) phpdoc
test:
	@docker exec ecomm-php ./../shell_scripts/install-wp-tests.sh wp_test_db wp_test_user password $(WORDPRESS_DB_HOST) latest
	@docker cp ecomm-php:/root/.composer/vendor ./tests
	@docker cp ecomm-php:/tmp/wordpress-tests-lib ./tests
	@docker exec ecomm-php ./../tests/tests-run /app/public/wp-content/plugins
	@docker exec ecomm-php ./../tests/tests-run /app/public/wp-content/mu-plugins
	@docker exec ecomm-php ./../tests/tests-run /app/public/wp-content/themes