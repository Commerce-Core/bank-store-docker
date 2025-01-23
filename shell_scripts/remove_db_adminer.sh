#!/bin/bash

PWD=$(pwd)
WEBROOT="${PWD}/public"

if [ ! -d "$WEBROOT" ]; then
    echo "Error: directory $WEBROOT doesn't exist"
    exit 1
fi

rm "$WEBROOT"/adm1ner-*.php
