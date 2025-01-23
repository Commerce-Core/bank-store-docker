#!/bin/bash

get_env_variable_value() {
    echo $(grep -v '^\s*#' ".env" | grep "$1=" | cut -d "=" -f 2 | cut -d "\"" -f 2)
}

PWD=$(pwd)
WEBROOT="${PWD}/public"
ADMINER_FILE="${PWD}/adminer/adminer-cc.php"
RANDOM_STRING=$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 16; echo)
ENV_FILE=".env"

if [ ! -f $ENV_FILE ]; then
    echo "Error: .env doesn't exist"
    exit 1
fi

WORDPRESS_URL=$(grep -v '^\s*#' ".env" | grep "WORDPRESS_URL" | cut -d "=" -f 2 | cut -d "\"" -f 2)
WORDPRESS_DB_USER=$(get_env_variable_value "WORDPRESS_DB_USER")
WORDPRESS_DB_NAME=$(get_env_variable_value "WORDPRESS_DB_NAME")
WORDPRESS_DB_HOST=$(get_env_variable_value "WORDPRESS_DB_HOST")

if [[ -z $WORDPRESS_DB_HOST ]]; then
    WORDPRESS_DB_HOST="localhost"
fi


if [[ $WORDPRESS_DB_USER =~ ^\$ ]]; then
  WORDPRESS_DB_USER=$(get_env_variable_value "MYSQL_USER")
fi

if [[ $WORDPRESS_DB_NAME =~ ^\$ ]]; then
  WORDPRESS_DB_NAME=$(get_env_variable_value "MYSQL_DATABASE")
fi

if [[ -z $WORDPRESS_URL ]] || [[ -z $WORDPRESS_DB_USER ]] || [[ -z $WORDPRESS_DB_NAME ]]; then
    echo "Set proper .env variable values for WORDPRESS_URL, WORDPRESS_DB_USER and WORDPRESS_DB_NAME"
    exit 1
fi

if [ ! -d "$WEBROOT" ]; then
    echo "Error: directory $WEBROOT doesn't exist"
    exit 1
fi

if [ ! -f "$ADMINER_FILE" ]; then
    echo "Error: $ADMINER_FILE doesn't exist"
    exit 1
fi

EXISTING_ADMINER_INSTANCE=$(find public/ -name "adm1ner-*.php" | head -n 1)

if [[ ! -z "$EXISTING_ADMINER_INSTANCE" ]] && [[ $EXISTING_ADMINER_INSTANCE =~ ^public ]]; then
    ADMINER_PUBLIC_FILE_NAME="${EXISTING_ADMINER_INSTANCE/public\//}"
else
    ADMINER_PUBLIC_FILE_NAME="adm1ner-$RANDOM_STRING.php"
    ADMINER_PUBLIC_FILE_PATH="$WEBROOT/$ADMINER_PUBLIC_FILE_NAME"

    cp "$ADMINER_FILE" "$ADMINER_PUBLIC_FILE_PATH"

    if [[ $? -ne 0 ]]; then
        echo "Copying $ADMINER_FILE to $ADMINER_PUBLIC_FILE_PATH has failed"
        exit 1
    fi
fi


echo "ADMINER_PATH: $WORDPRESS_URL/$ADMINER_PUBLIC_FILE_NAME?server=$WORDPRESS_DB_HOST&username=$WORDPRESS_DB_USER&db=$WORDPRESS_DB_NAME"
