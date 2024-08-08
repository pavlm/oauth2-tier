#!/bin/bash

set -x
cd `dirname $0`
thisDir=`pwd`

(cd ../vendor/kelunik/oauth; patch -f -p1 < "$thisDir"/patch-kelunik-oauth-001.patch) || exit 1

