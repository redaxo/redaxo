#!/usr/bin/env sh

BASEDIR=$(dirname $0)/..

if [ ! -d "$BASEDIR/vendor" ]
then
    BASEDIR=$BASEDIR/../../..
fi

## wrapper bash file which allows to pass a filepath to lint-query

echo "$1"

cat "$1" | $BASEDIR/vendor/bin/lint-query
