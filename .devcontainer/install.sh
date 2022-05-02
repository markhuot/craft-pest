#!/bin/sh

# cd ..

# if [ ! -d /workspaces/craft ]; then
#   composer create-project craftcms/craft
# fi

# cd craft
# ./craft install --username=admin --email=mark@markhuot.com --password=secret --siteName="Craft Pest" --siteUrl="\$PRIMARY_SITE_URL" --language="en-US"

mkdir -p storage
composer create-project --no-install --no-scripts --no-interaction craftcms/craft craft-src
cp -r craft-src/config/ ./
sed -i '/my-module/d' config/app.php
sed -i '/allowAdminChanges/d' config/general.php
cp craft-src/craft ./
cp craft-src/bootstrap.php ./
rm -rf craft-src

echo "DB_SERVER=mysql" >> .env
echo "DB_USER=root" >> .env
echo "DB_PASSWORD=root" >> .env
echo "DB_DATABASE=pest" >> .env