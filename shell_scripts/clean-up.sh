#!/bin/bash

if [ ! -z $CC_DEV ]; then
  echo "Variable CC_DEV is set with value: $CC_DEV. Exiting"
  exit 1
fi

PWD=$(pwd)
PLUGINS_DIR="${PWD}/public/wp-content/plugins"
THEMES_DIR="${PWD}/public/wp-content/themes"

if [ ! -d $THEMES_DIR ]; then
  echo "Directory ${PLUGINS_DIR} doesn't exist, run script from project root. Exiting"
  exit 1
fi

if [ ! -d $THEMES_DIR ]; then
  echo "Directory ${THEMES_DIR} doesn't exist, run script from project root. Exiting"
  exit 1
fi

echo "Deleting plugins and theme..."

rm -rf "${THEMES_DIR}/commercecore-ecom" \
  "${PLUGINS_DIR}/post-translator-plugin" \
  "${PLUGINS_DIR}/wp-commercecore-monitoring" \
  "${PLUGINS_DIR}/wp-checkout-plugin" \
  "${PLUGINS_DIR}/wp-ecom-experiments" \
  "${PLUGINS_DIR}/wp-gutenberg-blocks-plugin" \
  "${PLUGINS_DIR}/cc-posts-resave" \
  "${PLUGINS_DIR}/wp-emails-plugin" \
  "${PLUGINS_DIR}/cc-currency-formatter" \
  "${PLUGINS_DIR}/device-info-collect" \
  "${PLUGINS_DIR}/wp-email-marketing" \
  "${PLUGINS_DIR}/sentry-plugin" \
  "${PLUGINS_DIR}/wp-ecom-cache" \
  "${PLUGINS_DIR}/cc-timber-v2" \
  "${PLUGINS_DIR}/breeze" \
  "${PLUGINS_DIR}/malcare-security" \
  "${PLUGINS_DIR}/wp-domain-replacer" \
  "${PLUGINS_DIR}/cc-code-obfuscator" \
  "${PLUGINS_DIR}/cc-image-optimizer" \
  "${PLUGINS_DIR}/custom-css-plugin" \
  "${PLUGINS_DIR}/cc-logger" \
  "${PLUGINS_DIR}/cc-local-gtm" \
  "${PLUGINS_DIR}/cc-maintenance" \
  "${PLUGINS_DIR}/brands-ecom" \
  "${PLUGINS_DIR}/cc-legal-pages" \
  "${PLUGINS_DIR}/wp-landers"



