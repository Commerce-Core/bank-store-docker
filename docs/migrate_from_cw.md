# Migrate website from Cloudways to Laravel Forge

## Website migration steps

* create new Laravel Forge site
  * obtain desired domain (Namecheap)
  * provide appropriate domain and aliases if required
  * choose PHP/Laravel/Symfony 
  * PHP version (use 8.1)
  * Web Directory, use `/public` 
  * choose create Database
  * choose Nginx Template
  * retrieve created database name and ensure user with full access to this DB exist, get username and password
  * install application as Github repository, use `Commerce-Core/ecomm`
  * create DNS records in Cloudflare to point to Laravel Forge server
  * install Let's Encrypt certificate
  * add deployment script like:
      ```bash
      cd $FORGE_SITE_PATH
      touch .env
      mkdir -p tmp
      git pull origin $FORGE_SITE_BRANCH
      composer install
      ```
* get Laravel forge server (where website will be hosted) SSH public key, can be found in server settings, ex. https://forge.laravel.com/servers/789220/settings
* turn on SSH access for Cloudways app and provide connection credentials, add Laravel forge server SSH key from previous step to access CW without password
* fill required variables to `.env` file, keep in mind that all values should be enclosed into double quotes, [ex](https://forge.laravel.com/servers/789220/sites/2397015/environment)
    ```dotenv
    APP_ENV="production"

    WORDPRESS_URL="https://newwebsite.com"
    WORDPRESS_DB_USER="<DB_USER>"
    WORDPRESS_DB_PASSWORD="<DB_PASSWORD>"
    WORDPRESS_DB_NAME="<DB_NAME>"

    CC_ECOM_NEW=1
    CC_GITHUB_TOKEN="<Commercecore Github token>"

    CW_SOURCE_HOST="<Cloudways website host>"
    CW_SOURCE_IP="<Cloudways server IP address>"
    CW_SSH_USER="<Cloudways application SSH username>"
    ```
* deploy your application, [ex](https://forge.laravel.com/servers/789220/sites/2397015/deployments)
* add your PC public SSH key to Laravel forge server, [ex](https://forge.laravel.com/servers/789220/keys) to access it from SSH
* connect to Laravel forge server via SSH like
    ```shell
    ssh forge@<server_ip>
    ```
* enter new website directory
    ```shell
    cd /home/forge/<website_name>/public
    ```
* create wp-config.php file from public wp-config.php.dist
    ```shell
    cp wp-config.php.dist wp-config.php
    ```

* import DB and files using shell scripts, ensure you are in `/home/forge/<website_name>` directory
    * give execute permission for all shell scripts
    ```shell
    chmod +x shell_scripts/*
    ```
    * ensure you have an access to Cloudways app server, use values from `.env` file,
    you should see something like `/home/1234567.cloudwaysapps.com/something`
    ```shell
    SSH_USER_FROM_ENV=$(grep "CW_SSH_USER" ".env" | cut -d "=" -f 2 | cut -d "\"" -f 2) && \
    SSH_HOST_FROM_ENV=$(grep "CW_SOURCE_IP" ".env" | cut -d "=" -f 2 | cut -d "\"" -f 2) && \
    ssh -o StrictHostKeyChecking=no "$SSH_USER_FROM_ENV@$SSH_HOST_FROM_ENV" pwd
    ```
    * import uploads, provide folder where to download remote uploads backup,
    `tmp/cw_uploads` in example below, use some path in `tmp` directory
    ```shell
    shell_scripts/import_cw_uploads.sh.sh tmp/cw_uploads
    ```
    - script does the following:
      - creates wp-content/uploads backup on remote server
      - downloads zipped backup to provided directory (ex.: tmp/cw_uploads)
      - removes uploads backup on remote server
      - unzips uploads backup to the same directory where it is located
      - creates current wp-content/uploads backup and stores it in /tmp/backups folder
      - replaces current wp-content/uploads with uploads from downloaded backup
      - removes unzipped uploads folder
  * import DB
  ```shell
  shell_scripts/import_cw_db.sh tmp
  ```
    - script does the following:
      - creates DB backup on remote server
      - downloads zipped backup to provided directory (tmp)
      - removes DB backup on remote server
      - unzips DB backup to the same directory where it is located
      - imports unzipped SQL DB backup into active DB
      - replaces Cloudways source website hostname url with current
      - removes unzipped DB backup SQL file

* download plugins and theme and activate them
  * enter new website web root directory
      ```shell
      cd /home/forge/<website_name>/public
      ```
  * check themes, and activate default WP theme
    ```shell
    wp theme list
    wp theme activate twentytwentyfour
    ```
  * download CommerceCore theme
    ```shell
    wp cc-install-theme
    ```
  * download CommerceCore plugins
    ```shell
    wp cc-install-plugins
    ```
  * activate CommerceCore theme
    ```shell
    wp theme list
    wp theme activate commercecore-ecom
    ```
  * flush WP rewrite rule
    ```shell
    wp rewrite flush
    ```
