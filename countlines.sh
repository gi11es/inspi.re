#!/bin/bash
find . -path './pma' -prune -o -path './blog' -prune -o -path './punbb' -prune -o -path './js/3rdparty' -prune -o -print | egrep '\.php|\.as|\.sql|\.css|\.js' | grep -v '\.svn' | xargs cat | sed '/^\s*$/d' | wc -l
