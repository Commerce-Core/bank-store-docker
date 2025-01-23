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
TARGET_DIRECTORY=$1
TMP_DIRECTORY="tmp"
SSH_USER=$(grep -v '^\s*#' ".env" | grep "CW_SSH_USER" | cut -d "=" -f 2 | cut -d "\"" -f 2)
SSH_HOST=$(grep -v '^\s*#' ".env" | grep "CW_SOURCE_IP" | cut -d "=" -f 2 | cut -d "\"" -f 2)
CW_HOSTNAME=$(grep -v '^\s*#' ".env" | grep "CW_SOURCE_HOST" | cut -d "=" -f 2 | cut -d "\"" -f 2)

if [[ -z $SSH_USER ]] || [[ -z $SSH_HOST ]] || [[ -z $CW_HOSTNAME ]]; then
    echo "Set proper .env variable values for CW_SSH_USER, CW_SOURCE_IP and CW_HOSTNAME"
    exit 1
fi

echo $SSH_USER
echo $SSH_HOST
echo $CW_HOSTNAME

REMOTE_HOST="$SSH_USER@$SSH_HOST"
REMOTE_SSH="ssh -o StrictHostKeyChecking=no ${REMOTE_HOST}"
REMOTE_HOME=$($REMOTE_SSH "echo \$HOME")

REMOTE_WEBROOT="${REMOTE_HOME}/public_html"
WP_UPLOADS="wp-content/uploads"
REMOTE_UPLOADS="${REMOTE_WEBROOT}/${WP_UPLOADS}"
REMOTE_TEMP_DIRECTORY="${REMOTE_HOME}/tmp"
BACKUP_FILENAME="${CW_HOSTNAME}-uploads-backup-$(date +%Y%m%d-%H%M%S).zip"
BACKUP_FILENAME_PATH="${REMOTE_TEMP_DIRECTORY}/${BACKUP_FILENAME}"

echo $REMOTE_WEBROOT
echo $BACKUP_FILENAME
echo $REMOTE_TEMP_DIRECTORY
echo $BACKUP_FILENAME_PATH
#echo $(realpath $TMP_DIRECTORY)

if [ ! -d "$TARGET_DIRECTORY" ]; then
    echo "Warning: Directory ${TARGET_DIRECTORY} doesn't exist, creating"
    mkdir -p $TARGET_DIRECTORY
fi

if $REMOTE_SSH [ ! -d "$REMOTE_UPLOADS" ]; then
    echo "Error: ${REMOTE_UPLOADS} doesn't exist on ${REMOTE_HOST}"
    exit 1
fi

if $REMOTE_SSH [ ! -d "$REMOTE_TEMP_DIRECTORY" ]; then
    echo "Warning: ${REMOTE_TEMP_DIRECTORY} doesn't exist, creating"
    $REMOTE_SSH "mkdir -p $REMOTE_TEMP_DIRECTORY"
fi

echo "Creating Uploads backup: ${BACKUP_FILENAME_PATH}"
$REMOTE_SSH "cd ${REMOTE_WEBROOT} && zip -r ${BACKUP_FILENAME_PATH} ${WP_UPLOADS} && ls ${BACKUP_FILENAME_PATH}" > /dev/null 2>&1

echo "Copying ${BACKUP_FILENAME_PATH} to ${TARGET_DIRECTORY}"
rsync -chavzP "${REMOTE_HOST}":${BACKUP_FILENAME_PATH} "${TARGET_DIRECTORY}"

echo "Removing backup ${BACKUP_FILENAME_PATH} on ${REMOTE_HOST}"
$REMOTE_SSH "cd ${REMOTE_TEMP_DIRECTORY} && rm ${BACKUP_FILENAME}"

$PWD/shell_scripts/import_uploads.sh "$TARGET_DIRECTORY/${BACKUP_FILENAME}"

# Example
# ./import_cw_uploads.sh tmp/cw_uploads
