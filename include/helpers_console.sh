#!/bin/bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# Helper functions for console output
#
# ============================================================================

_init() {
#    if [ -n "${TERM}" -a "${TERM}" != "dumb" ]; then
        GREEN='\e[0;32m' RED='\e[0;31m' BLUE='\e[0;34m' NORMAL='\e[0m'
#    else
#        GREEN="" RED="" BLUE="" NORMAL=""
#    fi
}
die() {
    error=${1:-1}
    shift
    error "$*" >&2
    exit ${error}
}
info() {
    printf "${GREEN}%-7s: %s${NORMAL}\n" "Info" "$*"
}
error() {
    printf "${RED}%-7s: %s${NORMAL}\n" "Error" "$*"
}
warning() {
    printf "${BLUE}%-7s: %s${NORMAL}\n" "Warning" "$*"
}

executeCommand() {
    local _command="$1"
    echo "Executing '${_command}'"
    eval "${_command}"
    evaluateRtc $?
}

evaluateRtc(){
    local _givenRtc=$1
    if [ ${_givenRtc} -ne 0 ] ; then
        die 80 "Error during release steps"
    fi
}
