#!/bin/bash

if [ ! -z $CC_DEV ]; then
  echo "Variable CC_DEV is set with value: $CC_DEV. Exiting"
  exit 1
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

echo "Activating plugins..."

$WP_CLI_CMD plugin activate \
advanced-custom-fields-pro \
wp-checkout-plugin \
wp-emails-plugin \
cc-posts-resave \
wp-ecom-cache \
wp-gutenberg-blocks-plugin \
cc-currency-formatter \
wp-ecom-experiments \
wp-email-marketing \
post-translator-plugin \
timber-library \
wp-commercecore-monitoring \
wp-domain-replacer \
cc-image-optimizer \
cc-legal-pages
