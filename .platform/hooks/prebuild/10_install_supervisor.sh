#!/bin/bash

sudo dnf install -y python3-pip
sudo pip3 install supervisor

# Generate a default config, if you like:
sudo echo_supervisord_conf > /etc/supervisord.conf
