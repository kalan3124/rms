#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerinit ]] && exit 0
set -xe

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install git -yqq

# Install mysql driver
# Here you can install any other extension that you need
apt-get install php-odbc
apt-get install php-pdo-odbc

# Install Node Js
curl -sL https://deb.nodesource.com/setup_13.x | bash -
apt-get install -y nodejs

# Update npm
apt-get install npm -yqq
npm install -g npm