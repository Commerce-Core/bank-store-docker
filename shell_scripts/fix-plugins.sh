#!/bin/bash

if [ ! -z $CC_DEV ]; then
  echo "Variable CC_DEV is set with value: $CC_DEV. Exiting"
  exit 1
fi

PWD=$(pwd)
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

PLUGINS_ACTIVATE_PATTERN='Plugins to activate: (.+)$'

# fix plugins directories and remove duplicates
COMMAND_RESULT=$($WP_CLI_CMD cc-plugins-fix)

if [[ $? -ne 0 ]]; then
    echo "WP CLI command execution has failed"
    exit 1
fi

# Activate plugins provided by cc-plugins-fix WP-CLI command
if [[ $COMMAND_RESULT =~ $PLUGINS_ACTIVATE_PATTERN ]]; then

    # Extract plugins list from command output
    PLUGINS_TO_ACTIVATE=${BASH_REMATCH[1]}

    echo "Activating $PLUGINS_TO_ACTIVATE plugins"
    $WP_CLI_CMD plugin activate ${PLUGINS_TO_ACTIVATE}
fi



