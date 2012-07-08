#!/bin/sh
cd `dirname $0`

if [ "$*" = '' ]
then
	ARGS='--repeat 10 AllTests'
else
	ARGS="$*"
fi

eval "phpunit ${ARGS}"

cd -
