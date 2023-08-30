#!/bin/bash

./craft plugin/install pest
php ./bin/create-default-fs.php > /dev/null 2>&1
