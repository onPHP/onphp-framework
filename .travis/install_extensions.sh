#!/bin/bash

PHP_INI_PATH=$(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")

echo "Installing memcahe"
printf "\n" | pecl install memcache
echo "extension=memcache.so" >> $PHP_INI_PATH


echo "Installing memcahed"
printf "\n" | pecl install memcached
echo "extension=memcached.so" >> $PHP_INI_PATH


