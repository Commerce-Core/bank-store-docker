#!/bin/bash

WEBROOT="$PWD/public"

if [ ! -d "$WEBROOT" ]; then
    echo "Error: Webroot directory ${WEBROOT} doesn't exist, execute script from project root"
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

if [[ -z $SSH_USER ]] || [[ -z $SSH_HOST ]]; then
    echo "Set proper .env variable values for SSH_USER and SSH_HOST"
    exit 1
fi

REMOTE_HOST="$SSH_USER@$SSH_HOST"
REMOTE_SSH="ssh -o StrictHostKeyChecking=no ${REMOTE_HOST}"
REMOTE_HOME=$($REMOTE_SSH "echo \$HOME")

REMOTE_WEBROOT="${REMOTE_HOME}/public_html"
REMOTE_WP_SALT_FILE="${REMOTE_WEBROOT}/wp-salt.php"

if ssh "$REMOTE_HOST" [ ! -d "$REMOTE_WEBROOT" ]; then
    echo "Error: ${REMOTE_WEBROOT} doesn't exist on ${REMOTE_HOST}"
    exit 1
fi

if ssh "$REMOTE_HOST" [ ! -f "$REMOTE_WP_SALT_FILE" ]; then
    echo "Error: ${REMOTE_WP_SALT_FILE} doesn't exist"
    exit 1
fi

WP_SALT_FILE="${WEBROOT}/wp-salt.php"

echo "Copying ${REMOTE_WP_SALT_FILE} to ${WP_SALT_FILE}"
rsync -chazP  "${REMOTE_HOST}":${REMOTE_WP_SALT_FILE} "${WP_SALT_FILE}" > /dev/null 2>&1

if [[ $? -ne 0 ]]; then
  echo "Error: Copying ${REMOTE_WP_SALT_FILE} to ${WP_SALT_FILE}"
  exit 1
fi

echo "---SUCCESS---"