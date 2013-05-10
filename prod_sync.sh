#!/bin/sh
rsync --recursive --verbose --compress --perms --times --links --delete --delete-after --exclude "dev" --exclude "logs" --exclude "pictures" --exclude "pma" --exclude "js/generated" --exclude "dynamicpictures" . daruma@inspi.re:/home/daruma/public_html/inspire
