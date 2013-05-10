#!/bin/sh
rsync --rsh='ssh -p10022' --recursive --verbose --compress --perms --times --links daruma@darumazone.com:/home/daruma/public_html/inspire/blog/ ./blog
