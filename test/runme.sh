#!/bin/sh
cd `dirname $0`

phpunit --repeat 9 AllTests

cd -
