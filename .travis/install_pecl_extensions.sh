#!/bin/bash

PHP_INI_PATH=$(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")

echo "Loaded php.ini path: "$PHP_INI_PATH

#echo "Install pecl memcache"
#printf "\n" | pecl install memcache
#wget http://pecl.php.net/get/memcache-2.2.7.tgz
#tar -xzf memcache-2.2.7.tgz
#sh -c "cd memcache-2.2.7 && phpize && ./configure --enable-memcache && make && sudo make install"
#echo "extension=memcache.so" >> $PHP_INI_PATH

#echo "Install pecl memcached"
#printf "\n" | pecl install memcached
#wget http://pecl.php.net/get/memcached-2.1.0.tgz
#tar -xzf memcached-2.1.0.tgz
#sh -c "cd memcached-2.1.0 && phpize && ./configure --enable-memcached && make && sudo make install"
#echo "extension=memcached.so" >> $PHP_INI_PATH