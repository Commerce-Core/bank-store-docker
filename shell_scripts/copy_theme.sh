#!/bin/bash

if [ ! -z $CC_DEV ]; then
  echo "Variable CC_DEV is set with value: $CC_DEV. Exiting"
  exit 1
fi

THEMES_DIR="${PWD}/public/wp-content/themes"

if [ ! -d $THEMES_DIR ]; then
  echo "Directory ${THEMES_DIR} doesn't exist, run script from project root. Exiting"
  exit 1
fi

echo "Deleting theme..."
rm -rf "${THEMES_DIR}/commercecore-ecom"

echo "Copying theme..."
rsync -a --exclude='.git' submodules/commercecore-ecom/ public/wp-content/themes/commercecore-ecom/