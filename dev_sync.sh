#!/bin/sh
rsync --recursive --verbose --compress --perms --times --links --delete --delete-after --exclude "pictures"  --exclude "logs" --exclude "js/generated/" --exclude "blog" --exclude "pma" . daruma@inspi.re:/home/daruma/public_html/dev
rsync ./devsettings.php daruma@inspi.re:/home/daruma/public_html/dev/settings.php

