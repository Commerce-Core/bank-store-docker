#!/bin/bash

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

echo "SSH_USER=${SSH_USER} SSH_HOST=${SSH_HOST} CW_HOSTNAME=${CW_HOSTNAME}"

TEMP_DIR="$PWD/tmp"

if [ ! -d $TEMP_DIR ]; then
  mkdir -p $TEMP_DIR
fi

LOG_FILE="$TEMP_DIR/migrate-${CW_HOSTNAME}-$(date +%Y%m%d-%H%M%S).log"

touch $LOG_FILE

shell_scripts/migrate_cw_site2.sh >> $LOG_FILE 2>&1 &

echo "LOGFILE: $LOG_FILE"


