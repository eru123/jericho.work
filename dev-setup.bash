#!/bin/bash

# get script directory
ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# get current directory
CUR_DIR="$(pwd)"

# Update git
cd $ROOT_DIR
git fetch
git pull

# CDN Client
cd $ROOT_DIR/html/client/cdn
pnpm install
pnpm run build

# Admin Client
cd $ROOT_DIR/html/client/admin
pnpm install
pnpm run build

# Main client
cd $ROOT_DIR/html/client/main
pnpm install
pnpm run build

# Composer dependencies
cd $ROOT_DIR/html
composer install --no-interaction -o && cd $CUR_DIR
