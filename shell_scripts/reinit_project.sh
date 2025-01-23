#!/bin/bash

if [ ! -z $CC_DEV ]; then
  echo "Variable CC_DEV is set with value: $CC_DEV. Exiting"
  exit 1
fi

ENV_FILE=".env"

if [ ! -f $ENV_FILE ]; then
    echo "Error: .env doesn't exist"
    exit 1
fi

# Variables
APP_ENV=$(grep "APP_ENV" ".env" | cut -d "=" -f 2 | cut -d "\"" -f 2)


# Activate standard WP theme
shell_scripts/activate_wp_theme.sh

# Cleanup
shell_scripts/clean-up.sh


# Install or copying theme depending on environment
if [[ $APP_ENV == "dev" ]]; then
    shell_scripts/copy_theme.sh
else
    shell_scripts/install_theme.sh
fi

# Install plugins
shell_scripts/install_plugins.sh 0

# Activate theme
shell_scripts/activate_theme.sh

# Activate plugins
shell_scripts/activate_plugins.sh

