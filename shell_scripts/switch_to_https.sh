#!/bin/bash

ENV_FILE=".env"

if [ ! -f $ENV_FILE ]; then
    echo "Error: .env doesn't exist"
    exit 1
fi

# Variables
WORDPRESS_URL=$(grep -v '^\s*#' ".env" | grep "WORDPRESS_URL" | cut -d "=" -f 2 | cut -d "\"" -f 2)

if [[ -z $WORDPRESS_URL ]]; then
    echo "Set proper .env variable values for WORDPRESS_URL"
    exit 1
fi

NEW_WORDPRESS_URL="${WORDPRESS_URL//http:/https:}"

WEBROOT="${PWD}/public"
CC_CACHE_DIR="${PWD}/public/wp-content/cache/cc"

if [ ! -d $WEBROOT ]; then
  echo "Directory ${WEBROOT} doesn't exist, run script from project root. Exiting"
  exit 1
fi

CURRENT_USER=$(id -u)

if [ $CURRENT_USER -eq 0 ]; then
    echo "Running WP-CLI as root"
    WP_CLI_CMD="$(which wp-cli) --allow-root"
else
    echo "Running WP-CLI as user"
    WP_CLI_CMD="$(which wp)"
fi

echo "Replacing $WORDPRESS_URL to $NEW_WORDPRESS_URL in .env..."
sed -i "s#^WORDPRESS_URL=\"${WORDPRESS_URL}\"#WORDPRESS_URL=\"${NEW_WORDPRESS_URL}\"#g" .env 2>&1

cd $WEBROOT

echo "Replacing $WORDPRESS_URL to $NEW_WORDPRESS_URL in DB..."
$WP_CLI_CMD search-replace ${WORDPRESS_URL} ${NEW_WORDPRESS_URL}

# Flush rewrite rules
cd .. && ./shell_scripts/flush_rewrite.sh

echo "Clearing WP CC cache $CC_CACHE_DIR..."
rm -rf ${CC_CACHE_DIR}