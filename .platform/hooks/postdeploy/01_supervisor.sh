#!/usr/bin/env bash

# Find the supervisord binary path:
# SUPERVISOR_PATH=$(command -v supervisord)

# mkdir -p /etc/supervisord.d

# cat << 'EOF' | sudo tee /etc/supervisord.d/laravel-worker.ini
# [program:laravel_queue_worker]
# command=php /var/app/current/artisan queue:work --sleep=3 --tries=3
# autostart=true
# autorestart=true
# user=root
# stdout_logfile=/var/log/laravel-queue-out.log
# stderr_logfile=/var/log/laravel-queue-err.log
# EOF

# # If supervisord is running, reload config
# if pgrep supervisord >/dev/null; then
#     sudo supervisorctl reread
#     sudo supervisorctl update
#     sudo supervisorctl restart laravel_queue_worker
# else
#     # Start supervisord with the main config
#     # Make sure /etc/supervisord.conf exists
#     sudo $SUPERVISOR_PATH -c /etc/supervisord.conf
# fi
