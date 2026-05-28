#!/bin/bash
# JMeter Load Test Runner for Siama Web
# Usage: ./run-jmeter.sh [test-plan] [num-users]
set -e

JMETER_HOME="/opt/jmeter"
JMETER_BIN="${JMETER_HOME}/bin/jmeter"
TEST_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$TEST_DIR")"

# Default values
TEST_PLAN="${1:-${TEST_DIR}/public-pages.jmx}"
NUM_USERS="${2:-50}"
RESULTS_DIR="${TEST_DIR}/results"

mkdir -p "${RESULTS_DIR}"
RESULTS_FILE="${RESULTS_DIR}/$(basename "${TEST_PLAN}" .jmx)-$(date +%Y%m%d-%H%M%S).jtl"
REPORT_DIR="${RESULTS_DIR}/report-$(date +%Y%m%d-%H%M%S)"

echo "========================================"
echo " Siama Web - JMeter Load Test"
echo "========================================"
echo " Test Plan   : ${TEST_PLAN}"
echo " Users       : ${NUM_USERS}"
echo " Results     : ${RESULTS_FILE}"
echo " Report      : ${REPORT_DIR}"
echo "========================================"

# Check prerequisites
if [ ! -f "${JMETER_BIN}" ]; then
    echo "ERROR: JMeter not found at ${JMETER_BIN}"
    echo "Download from: https://jmeter.apache.org/download_jmeter.cgi"
    exit 1
fi

if [ ! -f "${TEST_PLAN}" ]; then
    echo "ERROR: Test plan not found: ${TEST_PLAN}"
    exit 1
fi

# Check if Laravel server is running
if ! curl -s -o /dev/null -w "%{http_code}" http://localhost > /dev/null 2>&1; then
    echo "WARNING: Laravel server doesn't seem to be running on localhost:80"
    echo "Start it with: php artisan serve"
    echo ""
fi

# Run JMeter in CLI mode
echo ""
echo "Running JMeter..."
echo ""

"${JMETER_BIN}" \
    -n \
    -t "${TEST_PLAN}" \
    -l "${RESULTS_FILE}" \
    -e -o "${REPORT_DIR}" \
    -Jnum_threads=${NUM_USERS} \
    -Jramp_time=$((${NUM_USERS} / 2)) \
    2>&1 | tee "${RESULTS_DIR}/jmeter-output.log"

echo ""
echo "========================================"
echo " Test Complete!"
echo "========================================"
echo " Raw Results : ${RESULTS_FILE}"
echo " HTML Report : ${REPORT_DIR}/index.html"
echo ""
echo " To view results in JMeter GUI:"
echo "   ${JMETER_BIN} -g ${RESULTS_FILE}"
echo "========================================"
