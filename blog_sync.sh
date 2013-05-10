#!/bin/sh
rsync --rsh='ssh -p10022' --recursive --verbose --compress --perms --times --links --delete --delete-after ./blog/ daruma@darumazone.com:/home/daruma/public_html/inspire/blog
