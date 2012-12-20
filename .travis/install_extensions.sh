#!/bin/bash

EXT_PATH = `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

echo "Installing memcahe"
pecl install memcache
echo "extension=memcache.so" >> $(EXT_PATH)


echo "Installing memcahed"
pecl install memcached
echo "extension=memcached.so" >> $(EXT_PATH)


