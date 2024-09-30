#!/bin/bash

if [ ! -d "storage" ]; then
  mkdir -p storage
fi

if [ ! -f ".env" ]; then
  cp  vendor/craftcms/craft/.env.example.dev ./.env.example
fi

if ! grep -q "CRAFT_RUN_QUEUE_AUTOMATICALLY=" .env.example; then
  echo "" >> .env.example
  echo "CRAFT_RUN_QUEUE_AUTOMATICALLY=false" >> .env.example
  echo "" >> .env.example
fi

if ! grep -q "CRAFT_TEMPLATES_PATH=" .env.example; then
  echo "" >> .env.example
  echo "CRAFT_TEMPLATES_PATH=./tests/templates" >> .env.example
  echo "" >> .env.example
fi

if ! grep -q "CRAFT_OMIT_SCRIPT_NAME_IN_URLS=" .env.example; then
  echo "" >> .env.example
  echo "CRAFT_OMIT_SCRIPT_NAME_IN_URLS=true" >> .env.example
  echo "" >> .env.example
fi

if [ ! -d "config" ]; then
  cp -r stubs/config ./
fi

if [ ! -d "web" ]; then
  cp -r vendor/craftcms/craft/web ./
fi

if [ ! -f "craft" ]; then
  cp  vendor/craftcms/craft/craft ./
  chmod +x ./craft
fi

if [ ! -f "bootstrap.php" ]; then
  cp  vendor/craftcms/craft/bootstrap.php ./
fi

php craft setup/keys
