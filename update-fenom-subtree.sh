#!/bin/sh
echo "-- Checking for fenom's origin  --"
git remote add fenom https://github.com/AdOnWeb/fenom.git

echo "-- Fetching fenom/master --"
git fetch fenom master

echo "-- Pulling changes to subtree --"
git subtree pull --prefix lib/Fenom fenom master --squash -m "updated fenom"