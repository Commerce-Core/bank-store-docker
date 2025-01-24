# bank-store

New WP bank-store with WordPress files

## Project details

Uses custom php `docker/php/Dockerfile` image based on top of official `php:8.1-fpm`
Uses latest `nginx:latest` as web server,
Uses `mysql:8.0.37` official docker image for DB, `3306` MySql server port is mapped to `4306` on host machine to avoid conflicts

Website url is `http://localhost`, see `docker/nginx/default.conf`

PhpMyAdmin instance is under `http://localhost:8180` (uses root account)

Uses submodules for our plugins and theme listed in `.gitmodules`, submodules storage folder is `/submodules`

Has latest WordPress installed to `/public` (`/app/public` inside php service container) folder

Has `Advanced Custom Fields PRO 6.3.0.1` plugin in `public/wp-content/plugins/advanced-custom-fields-pro`

Has `Timber 1.23.1` plugin in `public/wp-content/plugins/timber-library`

## Project init

1. Create env file

   `cp .env.dist .env`

2. Create wp-config.php

   `cp public/wp-config.php.dist public/wp-config.php`

3. Run docker compose

   `docker compose up`

   Use `docker compose up -d` to execute it in background.

   To stop Docker containers use:

   `docker compose stop`

   3.1 `docker-compose-alt.yml`

   - submodules are not mounted to containers as volumes
   - CC_DEV environment is not set
   - WP-CLI cc-install-theme command is allowed consequently

4. Download submodules

   `git submodule update --init`

   Theme and plugins submodules are mounted as volumes in [docker-compose.yml](docker-compose.yml)

   With alternative [docker-compose-alt.yml](docker-compose-alt.yml) configuration – submodules are not mounted

5. Databases

   By default three databases are created in `docker/mysql/init/init.sql` during init: `exampledb, exampledb2, exampledb3`.
   You can easily switch them in `.env`.

6. Database import into actual DB (`MYSQL_DATABASE` env variable)
   - put DB SQL dump file into docker/mysql/backups folder
   - get inside MySql container: `docker exec -it bank-store-mysql bash`
   - provide execution rights for `import.sh`: `chmod +x import.sh`
   - execute import script: `./import.sh  exampledb2.sql` where `exampledb2.sql` is dump file in `docker/mysql/backups`
7. Replace WP host in imported DB:

   - ensure that project is executed
   - ex. replace https://getcamtrix.com to http://localhost:
     - dry run to check for occurencies: `docker exec bank-store-php wp-cli --allow-root search-replace 'https://getcamtrix.com' 'http://localhost' --dry-run`
     - execute replacements: `docker exec bank-store-php wp-cli --allow-root search-replace 'https://getcamtrix.com' 'http://localhost'`

8. WP-CLI commands:

   - create WP admin user:
     - ensure that project is executed
     - create admin user: `docker exec bank-store-php wp-cli --allow-root user create wpadmin wpadmindev@commercecore.com --role=administrator --send-email=false --user_pass=wpadmindev`

9. Xdebug

   Xdebug is turned off by default, to enable it – uncomment first line `;zend_extension=xdebug.so` in `docker/php/xdebug.ini`
   To make Xdebug work properly with PhpStorm PHP server with following mappings should be added to `.idea/workspace.xml`:

   ```xml
     <component name="PhpServers">
       <servers>
         <server host="localhost" id="1f6f7e52-699b-408a-9ad5-62229b04c3ee" name="localhost" use_path_mappings="true">
           <path_mappings>
             <mapping local-root="$PROJECT_DIR$" remote-root="/app" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-currency-formatter" remote-root="/app/public/wp-content/plugins/cc-currency-formatter" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-posts-resave" remote-root="/app/public/wp-content/plugins/cc-posts-resave" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-timber-v2" remote-root="/app/public/wp-content/plugins/cc-timber-v2" />
             <mapping local-root="$PROJECT_DIR$/submodules/commercecore-ecom" remote-root="/app/public/wp-content/themes/commercecore-ecom" />
             <mapping local-root="$PROJECT_DIR$/submodules/device-info-collect" remote-root="/app/public/wp-content/plugins/device-info-collect" />
             <mapping local-root="$PROJECT_DIR$/submodules/post-translator-plugin" remote-root="/app/public/wp-content/plugins/post-translator-plugin" />
             <mapping local-root="$PROJECT_DIR$/submodules/sentry-plugin" remote-root="/app/public/wp-content/plugins/sentry-plugin" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-checkout-plugin" remote-root="/app/public/wp-content/plugins/wp-checkout-plugin" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-brands-plugin" remote-root="/app/public/wp-content/plugins/wp-brands-plugin" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-ab-tests" remote-root="/app/public/wp-content/plugins/wp-ab-tests" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-commercecore-monitoring" remote-root="/app/public/wp-content/plugins/wp-commercecore-monitoring" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-ecom-cache" remote-root="/app/public/wp-content/plugins/wp-ecom-cache" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-ecom-experiments" remote-root="/app/public/wp-content/plugins/wp-ecom-experiments" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-email-marketing" remote-root="/app/public/wp-content/plugins/wp-email-marketing" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-domain-replacer" remote-root="/app/public/wp-content/plugins/wp-domain-replacer" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-code-obfuscator" remote-root="/app/public/wp-content/plugins/cc-code-obfuscator" />
             <mapping local-root="$PROJECT_DIR$/submodules/custom-css-plugin" remote-root="/app/public/wp-content/plugins/custom-css-plugin" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-maintenance" remote-root="/app/public/wp-content/plugins/cc-maintenance" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-emails-plugin" remote-root="/app/public/wp-content/plugins/wp-emails-plugin" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-gutenberg-blocks-plugin" remote-root="/app/public/wp-content/plugins/wp-gutenberg-blocks-plugin" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-image-optimizer" remote-root="/app/public/wp-content/plugins/cc-image-optimizer" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-local-gtm" remote-root="/app/public/wp-content/plugins/cc-local-gtm" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-logger" remote-root="/app/public/wp-content/plugins/cc-logger" />
             <mapping local-root="$PROJECT_DIR$/submodules/cc-legal-pages" remote-root="/app/public/wp-content/plugins/cc-legal-pages" />
             <mapping local-root="$PROJECT_DIR$/submodules/wp-landers-plugin" remote-root="/app/public/wp-content/plugins/wp-landers-plugin" />
           </path_mappings>
         </server>
       </servers>
     </component>
   ```

10. [Makefile](Makefile) presents, has following targets:

    - start - starts docker compose
    - start-alt - start docker compose with alternative config [docker-compose-alt.yml](docker-compose-alt.yml)
    - stop - stops docker compose
    - composer-install - executes composer install inside php service container
    - phprun - executes provided command inside php docker container, ex.:
      ```shell
      make phprun CMD='php -v'
      ```

    By default `make` without arguments executes composer install and starts docker compose

11. Root composer

    root composer currently has the following packages:

    - https://github.com/kporras07/composer-symlinks
    - https://github.com/php-stubs/wp-cli-stubs

12. Nginx

    current nginx config adopted for static pages cache plugin, see details [here](https://github.com/Commerce-Core/bank-store/pull/2)

13. CommerceCore Essentials Must use plugin `public/wp-content/mu-plugins/cc-essentials`
    - aim to hold essential functions and common logic used in other plugins and theme (feature plan)
    - currently has logic to interact with Github to retrieve theme and plugins
    - `CC_GITHUB_TOKEN` with valid Github token must be defined in [wp-config.php](public%2Fwp-config.php)
    - registers custom WP-CLI command `cc-install-theme` to download and activate [Commerce Core E-com theme](https://github.com/Commerce-Core/commercecore-ecom)
      - works only if `CC_DEV` environment variable is not set and submodules are not mounted as volumes ([docker-compose-alt.yml](docker-compose-alt.yml))

## Migrate website from Cloudways to Laravel Forge steps

See [migrate_from_cw.md](docs%2Fmigrate_from_cw.md)

## Run Tests

Run tests and print results to `test-results.txt`:

```
make test > test-results.txt
```

Run tests and print result on console screen:

```
make test
```

## Code Documentation

Generate codebase documentation with:

```
make phpdoc
```

Open documentation: http://localhost/docs/

## REST API Documentation

Open documentation: http://localhost/rest-api/docs/

See [CommerceCore SwaggerUI Documentation](public%2Fwp-content%2Fmu-plugins%2Fcc-api-swaggerui%2FREADME.md)
