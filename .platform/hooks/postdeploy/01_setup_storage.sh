#!/bin/bash

# Create the storage directory structure
mkdir -p /var/app/current/storage/logs
mkdir -p /var/app/current/storage/framework/sessions
mkdir -p /var/app/current/storage/framework/views
mkdir -p /var/app/current/storage/framework/cache

# Set permissions for the storage directory
chmod -R 775 /var/app/current/storage
chown -R webapp:webapp /var/app/current/storage

# Ensure the Laravel log file exists
touch /var/app/current/storage/logs/laravel.log
chmod 664 /var/app/current/storage/logs/laravel.log
chown webapp:webapp /var/app/current/storage/logs/laravel.log