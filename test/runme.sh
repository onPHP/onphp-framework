#!/bin/sh
cd `dirname $0`

phpunit --repeat 7 AllTests

cd -
