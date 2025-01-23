#!/bin/bash

# Check for correct number of arguments
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <uploads_backup_zip>"
    exit 1
fi

UPLOADS_BACKUP_FILE="$1"

if [ ! -f $UPLOADS_BACKUP_FILE ]; then
    UPLOADS_BACKUP_FILE="$PWD/$1"
fi

if [ ! -f $UPLOADS_BACKUP_FILE ]; then
    echo "Error: $UPLOADS_BACKUP_FILE doesn't exist"
    exit 1
fi

UPLOADS_BACKUP_FILE_DIRECTORY="$(dirname "${UPLOADS_BACKUP_FILE}")"
UNZIPPED_UPLOADS_DIRECTORY="${UPLOADS_BACKUP_FILE_DIRECTORY}/wp-content/uploads"
WP_CONTENT_DIRECTORY="$PWD/public/wp-content"
TMP_BACKUPS_DIRECTORY="$PWD/tmp/backups"

if [ ! -d $WP_CONTENT_DIRECTORY ]; then
    echo "Error: $WP_CONTENT_DIRECTORY doesn't exist"
    exit 1
fi

echo "Extracting $UPLOADS_BACKUP_FILE to $UPLOADS_BACKUP_FILE_DIRECTORY"
unzip -qq -o $UPLOADS_BACKUP_FILE -d $UPLOADS_BACKUP_FILE_DIRECTORY

if [ ! -d $UNZIPPED_UPLOADS_DIRECTORY ]; then
    echo "Error: $UNZIPPED_UPLOADS_DIRECTORY doesn't exist"
    exit 1
fi

if [ ! -d $TMP_BACKUPS_DIRECTORY ]; then
    mkdir -p $TMP_BACKUPS_DIRECTORY
fi

UPLOADS_TARGET_DIRECTORY="$WP_CONTENT_DIRECTORY/uploads"

if [ ! -d $UPLOADS_TARGET_DIRECTORY ]; then
    echo "Warning: $UPLOADS_TARGET_DIRECTORY doesn't exist, creating"
    mkdir -p $UPLOADS_TARGET_DIRECTORY
fi

echo "Replacing $UPLOADS_TARGET_DIRECTORY with $UNZIPPED_UPLOADS_DIRECTORY"
rsync -r "$UNZIPPED_UPLOADS_DIRECTORY/" $UPLOADS_TARGET_DIRECTORY

echo "Removing $UNZIPPED_UPLOADS_DIRECTORY"
rm -rf $UNZIPPED_UPLOADS_DIRECTORY

# Example
# shell_scripts/import_uploads.sh tmp/uploads/tryminipix.com-uploads-backup-20240712-160413.zip

