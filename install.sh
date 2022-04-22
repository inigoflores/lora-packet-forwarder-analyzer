#!/bin/bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

echo  "Installing required packages"
apt install -y ngrep gawk php-cli

# Copy files to system folders
echo  "Installing service"
cp service/packet-forwarder-sniffer.service /etc/systemd/system/
cp service/packet-forwarder-sniffer.conf /etc/logrotate.d/
cp service/packet-forwarder-sniffer.sh /usr/local/sbin

# Create log folder
mkdir -p /var/log/packet-forwarder/

# Enable and start service
echo  "Starting service"

systemctl daemon-reload
systemctl start packet-forwarder-sniffer.service
systemctl enable packet-forwarder-sniffer.service
