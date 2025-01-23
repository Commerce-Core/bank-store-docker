#!/bin/bash

# Check for correct number of arguments
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <db_backup_zip>"
    exit 1
fi

DB_BACKUP_FILE="$1"

# make full path when relative pathname of input file is used
if [[ ! $DB_BACKUP_FILE =~ ^\/ ]]; then
    DB_BACKUP_FILE="$PWD/$DB_BACKUP_FILE";
fi

if [ ! -f $DB_BACKUP_FILE ]; then
    echo "Error: $DB_BACKUP_FILE doesn't exist"
    exit 1
fi

DB_BACKUP_FILE_DIRECTORY="$(dirname "${DB_BACKUP_FILE}")"
DB_BACKUP_SQL_FILE=${DB_BACKUP_FILE%".zip"}

unzip -o $DB_BACKUP_FILE -d $DB_BACKUP_FILE_DIRECTORY

if [ ! -f $DB_BACKUP_SQL_FILE ]; then
    echo "Error: $DB_BACKUP_SQL_FILE doesn't exist"
    exit 1
fi

echo $DB_BACKUP_SQL_FILE

ENV_FILE=".env"

if [ ! -f $ENV_FILE ]; then
    echo "Error: .env doesn't exist"
    exit 1
fi

CW_HOSTNAME=$(grep "CW_SOURCE_HOST" ".env" | cut -d "=" -f 2 | cut -d "\"" -f 2)
WORDPRESS_URL=$(grep "WORDPRESS_URL" ".env" | cut -d "=" -f 2 | cut -d "\"" -f 2)

if [[ -z $CW_HOSTNAME ]] || [[ -z $WORDPRESS_URL ]]; then
    echo "Set proper .env variable values for CW_HOSTNAME and WORDPRESS_URL"
    exit 1
fi

CW_HOST_URL="https://$CW_HOSTNAME"

PUBLIC_HTML_DIRECTORY="$PWD/public"

if [ ! -d $PUBLIC_HTML_DIRECTORY ]; then
    echo "Error: $PUBLIC_HTML_DIRECTORY directory doesn't exist"
    exit 1
fi

echo "Importing DB from $DB_BACKUP_SQL_FILE"

CURRENT_USER=$(id -u)

if [ $CURRENT_USER -eq 0 ]; then
    echo "Running WP-CLI as root"
    WP_CLI_CMD="$(which wp-cli) --allow-root"
    echo $WP_CLI_CMD
else
    echo "Running WP-CLI as user"
    WP_CLI_CMD="$(which wp)"
fi

cd $PUBLIC_HTML_DIRECTORY
$WP_CLI_CMD db import $DB_BACKUP_SQL_FILE

echo "Replacing $CW_HOST_URL occurrences with $WORDPRESS_URL in DB"
$WP_CLI_CMD search-replace ${CW_HOST_URL} ${WORDPRESS_URL}

echo "Removing $DB_BACKUP_SQL_FILE"
rm $DB_BACKUP_SQL_FILE
