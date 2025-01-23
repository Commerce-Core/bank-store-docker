#!/bin/bash

# Get Cloudways source website essential variables

ENV_FILE=".env"

if [ ! -f $ENV_FILE ]; then
    echo "Error: .env doesn't exist"
    exit 1
fi

# Variables from .env file
SSH_USER=$(grep -v '^\s*#' ".env" | grep "CW_SSH_USER" | cut -d "=" -f 2 | cut -d "\"" -f 2)
SSH_HOST=$(grep -v '^\s*#' ".env" | grep "CW_SOURCE_IP" | cut -d "=" -f 2 | cut -d "\"" -f 2)
CW_HOSTNAME=$(grep -v '^\s*#' ".env" | grep "CW_SOURCE_HOST" | cut -d "=" -f 2 | cut -d "\"" -f 2)

if [[ -z $SSH_USER ]] || [[ -z $SSH_HOST ]] || [[ -z $CW_HOSTNAME ]]; then
    echo "ERROR: Set proper .env variable values for SSH_USER, SSH_HOST and CW_HOSTNAME"
    exit 1
fi

# Create temp directories
TEMP_DIR="$PWD/tmp/migrate-${CW_HOSTNAME}-$(date +%Y%m%d-%H%M%S)"
DB_TEMP_DIR="$TEMP_DIR/db"
UPLOADS_TEMP_DIR="$TEMP_DIR/uploads"

mkdir -p $DB_TEMP_DIR
mkdir -p $UPLOADS_TEMP_DIR

echo "Starting $CW_HOSTNAME migration"

# Import DB from Cloudways and replace domain
echo "Importing DB..."
shell_scripts/import_cw_db.sh $DB_TEMP_DIR

if [[ $? -ne 0 ]]; then
  echo "ERROR: Error importing DB"
  exit 1
fi

# Import Uploads from Cloudways
echo "Importing Uploads..."
shell_scripts/import_cw_uploads.sh $UPLOADS_TEMP_DIR

if [[ $? -ne 0 ]]; then
  echo "ERROR: Error importing Uploads"
  exit 1
fi

# Import wp-salt.php from Cloudways
echo "Importing wp-salt.php..."
shell_scripts/import_cw_wp_salt.sh

# Download CommerceCore plugins and theme and activate them
# CC plugins and theme are deleted in case they already exist before performing this action
echo "Downloading CC Plugins and Theme..."
shell_scripts/reinit_project.sh

if [[ $? -ne 0 ]]; then
  echo "ERROR: Error Downloading CC Plugins and Theme"
  exit 1
fi

# Flush WP rewrite rules
echo "Flushing Rewrite Rules..."
shell_scripts/flush_rewrite.sh

if [[ $? -ne 0 ]]; then
  echo "ERROR: Error Flushing Rewrite Rules"
fi

rm -rf $TEMP_DIR

echo "---SUCCESS---"

