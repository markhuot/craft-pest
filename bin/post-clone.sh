#!/bin/bash

composer require --dev craftcms/craft

if [ ! -d "storage" ]; then
  mkdir -p storage
fi

if [ ! -f ".env" ] && [ -f "vendor/craftcms/craft/.env.example.dev" ]; then
  cp  vendor/craftcms/craft/.env.example.dev ./.env
elif [ ! -f ".env" ] && [ -f "vendor/craftcms/craft/.env.example" ]; then
  cp  vendor/craftcms/craft/.env.example ./.env
fi

if ! grep -q "CRAFT_RUN_QUEUE_AUTOMATICALLY=false" .env; then
  echo "" >> .env
  echo "CRAFT_RUN_QUEUE_AUTOMATICALLY=false" >> .env
  echo "" >> .env
fi

if [ ! -f "config/app.php" ]; then
  mkdir -p config
  echo "<?php return [
      'components' => [
          'queue' => [
              'class' => \yii\queue\sync\Queue::class,
              'handle' => true, // if tasks should be executed immediately
          ],
      ],
  ];" > config/app.php
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

composer remove --dev craftcms/craft

