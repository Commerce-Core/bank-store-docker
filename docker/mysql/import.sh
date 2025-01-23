#!/bin/bash

SQL_FILE="/backups/${1}"

if [ -f "$SQL_FILE" ]
  then
    echo "Importing ${SQL_FILE} into ${MYSQL_DATABASE}"
    mysql -u root --password="$MYSQL_ROOT_PASSWORD" --database="$MYSQL_DATABASE" < "${SQL_FILE}"
fi
