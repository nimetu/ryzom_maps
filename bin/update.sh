#!/bin/sh

DIR=$(readlink -f $(dirname $0))

php ${DIR}/bmmaps.php bmmaps:json --ryzom=$HOME/.local/share/Ryzom/ryzom_live/data

php ${DIR}/convert-regions-to-areas.php

php ${DIR}/build-mapjs.php

