#!/bin/sh

# Run the tests from ./tests
#
# You can run all the test (that are not excluded)
#   ./runTests.sh .
# or a subset (often by directory)
#   ./runTests.sh basics/

clear
date
#cd tests

#mkdir -p ./build/logs ./build/coverage/ #./logs/ ./tmp/

TEST="$@"
#if [ -z "$TEST" ]; then
#    # no tests given, go test the biggest set
#    #TEST="./tests"
#fi

TIME_CMD=""
if [ -e "/usr/bin/time" ] ; then
    TIME_CMD='/usr/bin/time --format=Real:%E,\nseconds:%e\n';
fi

PHP="/usr/bin/php"
#VERBOSE="--verbose  "   # --debug --testdox
# no code coverage without phpdbg
#PHP="/usr/bin/phpdbg7.2 -qrr "
#COVERAGE="--coverage-html=build/coverage"

COLORS="--colors"
# config run by default, includes bootstrap
CONF=" -d memory_limit=1024M"
# Setting exclude-group here overrides the config
#GROUP=" --group __nogroup__"
#GROUP=" --group only"
#GROUP=" --exclude-group incomplete,large,webtest,externalAPI,DontRunThisGroup"

# testRoutesAre200Ok is the biggest member of webtest group
#GROUPEXCLUDE=" --exclude-group webtest,testRoutesAre200Ok"
GROUPEXCLUDE=" --exclude-group ci,large,incomplete,webtest,dbtest"
STRICT=" --disallow-test-output --enforce-time-limit --strict-coverage"
#STRICT2="--stop-on-failure"

ulimit -HSn 4096

# Use the phpunit brought in by Composer
PHPUNIT="$PHP vendor/bin/phpunit"

# weak-verbose
# weak
SYMFONY_DEPRECATIONS_HELPER="weak" $TIME_CMD \
  $PHPUNIT $CONF $GROUP $GROUPEXCLUDE $COLORS $VERBOSE $STRICT $STRICT2 $COVERAGE $TEST

# http://stackoverflow.com/questions/911168/how-to-detect-if-my-shell-script-is-running-through-a-pipe
if [ -t 1 ] ; then
    # we are running in a TTY - under human control. Allow easy running again
    echo "#$PHPUNIT $CONF $GROUP $GROUPEXCLUDE $COLORS $VERBOSE $STRICT $STRICT2 $COVERAGE $TEST"
    echo ""
    echo ""
    echo -n "press [Enter] to re-run:> "
    read x
    #cd ..

    exec $0 $@
fi
