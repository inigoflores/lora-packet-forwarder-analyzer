#!/bin/bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

# Stop and disable service
echo "Disabling service"
systemctl stop packet-forwarder-sniffer.service
systemctl disable packet-forwarder-sniffer.service
systemctl daemon-reload

# Delete files
echo "Removing service files"
rm /etc/systemd/system/packet-forwarder-sniffer.service
rm /etc/logrotate.d/packet-forwarder-sniffer.conf
rm /usr/local/sbin/packet-forwarder-sniffer.sh


