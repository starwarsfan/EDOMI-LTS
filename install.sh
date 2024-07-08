#!/usr/bin/env bash
# ============================================================================
#
# Helper script to install Eodmi on different platforms
#
# ============================================================================

# Store path from where script was called,
# determine own location and cd there
callDir=$(pwd)
ownLocation="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd ${ownLocation}
. ./include/helpers_console.sh
_init

# ----------------------------------------------------------------------------
# Determining current operating system (distribution)
info "Determining system"
if [[ -e /etc/os-release ]] ; then
    . /etc/os-release
else
    warning "File /etc/os-release not found, not installing anything"
    exit 1
fi
info "    Determined $NAME"
echo ""

usedDistro=''
releaseName=''
case ${ID} in
    rocky)
        case ${VERSION_ID} in
            "8."*)
                info "Starting Rocky Linux 8 installation"
                . ./include/install_RockyLinux8.sh
                info " -> Rocky Linux 8 installation finished"
                ;;
            *)
                warning "Only Rocky Linux 8 supported at the moment"
                ;;
        esac
        ;;
    *)
        echo "Unsupported operating system ${ID}, VERSION_ID=${VERSION_ID}"
        exit
        ;;
esac
