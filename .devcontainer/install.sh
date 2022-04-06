#!/bin/sh

cd ..

if [ ! -d /workspaces/craft ]; then
  composer create-project craftcms/craft
fi

cd craft
./craft install --username=admin --email=mark@markhuot.com --password=secret --siteName="Craft Pest" --siteUrl="\$PRIMARY_SITE_URL" --language="en-US"