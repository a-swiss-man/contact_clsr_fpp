#!/bin/bash

# ESP32 Message Listener
# This script is a wrapper that calls the Python listener for better reliability

PLUGIN_DIR="/home/fpp/media/plugins/esp32_neopixel_contact"
PYTHON_LISTENER="$PLUGIN_DIR/scripts/esp32_listener.py"
LOG_FILE="/home/fpp/media/logs/esp32_neopixel_contact.log"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - [LISTENER] $1" >> "$LOG_FILE"
}

# Try to use Python listener first (more reliable)
if [ -f "$PYTHON_LISTENER" ] && command -v python3 >/dev/null 2>&1; then
    log_message "Starting Python-based listener"
    exec python3 "$PYTHON_LISTENER"
else
    log_message "Python listener not available, falling back to basic netcat listener"
    
    # Fallback to basic netcat if Python not available
    LISTENER_PORT=8081
    CALLBACKS_SCRIPT="$PLUGIN_DIR/callbacks.sh"
    
    # Process incoming message from ESP32
    process_message() {
        local message="$1"
        log_message "Received message from ESP32: $message"
        
        if [[ "$message" =~ ^CONTACT: ]]; then
            count=$(echo "$message" | cut -d':' -f2)
            log_message "Contact closure detected, count: $count"
            "$CALLBACKS_SCRIPT" contact "$count"
        elif [[ "$message" =~ ^STATUS: ]]; then
            status=$(echo "$message" | cut -d':' -f2)
            log_message "ESP32 status update: $status"
        fi
    }
    
    log_message "ESP32 listener started on port $LISTENER_PORT (netcat fallback)"
    
    while true; do
        if command -v nc >/dev/null 2>&1; then
            message=$(nc -l -p "$LISTENER_PORT" -w 1 2>/dev/null)
            if [ -n "$message" ]; then
                process_message "$message"
            fi
        else
            log_message "Error: netcat (nc) not found. Cannot listen for ESP32 messages."
            sleep 5
        fi
        sleep 0.1
    done
fi

