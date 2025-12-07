#!/bin/bash

# FPP ESP32 NeoPixel Contact Closure Plugin
# This script handles FPP events and sends commands to the ESP32.

# Log file
LOG_FILE="/home/fpp/media/logs/esp32_neopixel_contact.log"
CONFIG_FILE="/home/fpp/media/config/esp32_neopixel_contact.conf"
CONTACT_COUNT_FILE="/home/fpp/media/config/esp32_neopixel_contact_count.txt"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Log script execution for debugging
log_message "callbacks.sh executed with args: $@"



# Function to increment contact count
increment_contact_count() {
    local current_count=0
    if [ -f "$CONTACT_COUNT_FILE" ]; then
        current_count=$(cat "$CONTACT_COUNT_FILE" 2>/dev/null || echo "0")
        current_count=$((current_count + 0))  # Ensure it's a number
    fi
    current_count=$((current_count + 1))
    echo "$current_count" > "$CONTACT_COUNT_FILE"
    log_message "Contact closure count incremented to $current_count"
    echo "$current_count"
}

# Function to reset contact count
reset_contact_count() {
    echo "0" > "$CONTACT_COUNT_FILE"
    log_message "Contact closure count reset to 0"
}

# Arguments:
# $1: Event Type (e.g., "contact", "reset")
# $2: Action (e.g., count for contact, or empty for reset)

TYPE=$1
ACTION=$2

log_message "Event received: Type=$TYPE, Action=$ACTION"

case "$TYPE" in
    "contact")
        # Contact closure event from ESP32 listener
        # ACTION contains the count
        increment_contact_count
        ;;
    "reset")
        # Reset contact count
        reset_contact_count
        ;;
    *)
        # Default or unknown event
        log_message "Unknown event type: $TYPE"
        ;;
esac

