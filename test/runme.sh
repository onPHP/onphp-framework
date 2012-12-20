#!/bin/bash
cd `dirname $0`

if [ "$*" = '' ]
then
	ARGS='--repeat 9 AllTests'
else
	ARGS="$*"
fi

eval "phpunit ${ARGS}"

cd -
