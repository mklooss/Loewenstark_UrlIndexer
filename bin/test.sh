#!/usr/bin/env bash

set -e

MAGENTO_VERSION=$1

if test "$2"; then DB_USER="$2"; else DB_USER="root"; fi
if test "$3"; then DB_PASSWORD="-p $3"; else DB_PASSWORD=""; fi

if test -z "${MAGENTO_VERSION}"; then
    >&2 echo "Please specify a Magento version"
    exit 1
fi

MAGENTO_INSTALL_DIR=magento_${MAGENTO_VERSION}
MAGECI_BIN=vendor/bin/mage-ci

composer -n install

if [ ! -d ${MAGENTO_INSTALL_DIR} ]; then
    ${MAGECI_BIN} install ${MAGENTO_INSTALL_DIR} ${MAGENTO_VERSION} magento_${MAGENTO_VERSION//./_}_test -c -t -r http://mage-ci.ecomdev.org \
           -u ${DB_USER} ${DB_PASSWORD}

    ${MAGECI_BIN} install-module ${MAGENTO_INSTALL_DIR} $(pwd)
    ${MAGECI_BIN} install-module ${MAGENTO_INSTALL_DIR} $(pwd)/vendor/ecomdev/ecomdev_phpunit

    cp phpunit.xml.dist ${MAGENTO_INSTALL_DIR}
fi

${MAGECI_BIN} phpunit ${MAGENTO_INSTALL_DIR}
