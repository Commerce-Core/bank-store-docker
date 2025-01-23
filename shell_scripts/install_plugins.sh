#!/bin/bash

if [ ! -z $CC_DEV ]; then
  echo "Variable CC_DEV is set with value: $CC_DEV. Exiting"
  exit 1
fi

ACTIVATE_PLUGINS=1

if [ "$#" -eq 1 ]; then
    ACTIVATE_PLUGINS=$1
fi

WEBROOT="${PWD}/public"

if [ ! -d $WEBROOT ]; then
  echo "Directory ${WEBROOT} doesn't exist, run script from project root. Exiting"
  exit 1
fi

cd $WEBROOT

CURRENT_USER=$(id -u)

if [ $CURRENT_USER -eq 0 ]; then
    echo "Running WP-CLI as root"
    WP_CLI_CMD="$(which wp-cli) --allow-root"
else
    echo "Running WP-CLI as user"
    WP_CLI_CMD="$(which wp)"
fi

echo "Installing plugins..."

$WP_CLI_CMD cc-install-plugins --activate="$ACTIVATE_PLUGINS"
