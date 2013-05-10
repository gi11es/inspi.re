#!/bin/sh
rsync --recursive --verbose --compress --perms --times --links --delete --delete-after . daruma@kirby.inspi.re:/home/daruma/public_html