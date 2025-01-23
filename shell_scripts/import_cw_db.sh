#!/bin/bash

# Check for correct number of arguments
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <target_directory>"
    exit 1
fi

ENV_FILE=".env"

if [ ! -f $ENV_FILE ]; then
    echo "Error: .env doesn't exist"
    exit 1
fi

# Variables
SSH_USER=$(grep -v '^\s*#' ".env" | grep "CW_SSH_USER" | cut -d "=" -f 2 | cut -d "\"" -f 2)
SSH_HOST=$(grep -v '^\s*#' ".env" | grep "CW_SOURCE_IP" | cut -d "=" -f 2 | cut -d "\"" -f 2)
CW_HOSTNAME=$(grep -v '^\s*#' ".env" | grep "CW_SOURCE_HOST" | cut -d "=" -f 2 | cut -d "\"" -f 2)

if [[ -z $SSH_USER ]] || [[ -z $SSH_HOST ]]; then
    echo "Set proper .env variable values for SSH_USER and SSH_HOST"
    exit 1
fi

echo $SSH_USER
echo $SSH_HOST
echo $CW_HOSTNAME

TARGET_DIRECTORY="$1"

if [ ! -d "$TARGET_DIRECTORY" ]; then
    echo "Error: Directory ${TARGET_DIRECTORY} doesn't exist"
    exit 1
fi

REMOTE_HOST="$SSH_USER@$SSH_HOST"
REMOTE_SSH="ssh -o StrictHostKeyChecking=no ${REMOTE_HOST}"
REMOTE_HOME=$($REMOTE_SSH "echo \$HOME")

echo $REMOTE_HOME

REMOTE_WEBROOT="${REMOTE_HOME}/public_html"
REMOTE_TEMP_DIRECTORY="${REMOTE_HOME}/tmp"
BACKUP_FILENAME="${CW_HOSTNAME}-db-backup-$(date +%Y%m%d-%H%M%S).sql"
BACKUP_FILENAME_PATH="${REMOTE_TEMP_DIRECTORY}/${BACKUP_FILENAME}"
BACKUP_FILENAME_ZIP_PATH="${REMOTE_TEMP_DIRECTORY}/${BACKUP_FILENAME}.zip"

if ssh "$REMOTE_HOST" [ ! -d"$REMOTE_WEBROOT" ]; then
    echo "Error: ${REMOTE_WEBROOT} doesn't exist on ${REMOTE_HOST}"
    exit 1
fi

if ssh "$REMOTE_HOST" [ ! -d "$REMOTE_TEMP_DIRECTORY" ]; then
    echo "Warning: ${REMOTE_TEMP_DIRECTORY} doesn't exist, creating"
    ssh "$REMOTE_HOST" "mkdir -p $REMOTE_TEMP_DIRECTORY"
fi

echo "Creating DB backup: ${BACKUP_FILENAME_PATH}"
EXCLUDE_TABLES=$($REMOTE_SSH "cd $REMOTE_WEBROOT && wp db tables 'wp_bv_*' --all-tables --format=csv")
$REMOTE_SSH "cd ${REMOTE_WEBROOT} && wp db export --exclude_tables=${EXCLUDE_TABLES} ${BACKUP_FILENAME_PATH} && cd ${REMOTE_TEMP_DIRECTORY} && zip ${BACKUP_FILENAME_ZIP_PATH} ${BACKUP_FILENAME} && rm ${BACKUP_FILENAME}"

echo "Copying ${BACKUP_FILENAME_ZIP_PATH} to ${TARGET_DIRECTORY}"
rsync -chavzP --stats "${REMOTE_HOST}":${BACKUP_FILENAME_ZIP_PATH} "${TARGET_DIRECTORY}"

$PWD/shell_scripts/import_db.sh "$TARGET_DIRECTORY/${BACKUP_FILENAME}.zip"

echo "Removing backup ${BACKUP_FILENAME_PATH} on ${REMOTE_HOST}"
$REMOTE_SSH "cd ${REMOTE_TEMP_DIRECTORY} && rm ${BACKUP_FILENAME}.zip"

# Example
# shell_scripts/import_cw_db.sh tmp