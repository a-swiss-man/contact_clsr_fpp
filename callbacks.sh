#!/bin/bash

# FPP ESP32 NeoPixel Contact Closure Plugin
# This script handles FPP events and sends commands to the ESP32.

# Log file
LOG_FILE="/home/fpp/media/logs/contact_clsr_fpp.log"
CONFIG_FILE="/home/fpp/media/config/contact_clsr_fpp.conf"
TOTAL_COUNT_FILE="/home/fpp/media/config/contact_clsr_fpp_total.txt"
DAILY_COUNT_FILE="/home/fpp/media/config/contact_clsr_fpp_daily.txt"
DAILY_DATE_FILE="/home/fpp/media/config/contact_clsr_fpp_daily_date.txt"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Log script execution for debugging
log_message "callbacks.sh executed with args: $@"



# Function to check and reset daily count if needed
check_daily_reset() {
    local today=$(date '+%Y-%m-%d')
    local last_date=""
    
    if [ -f "$DAILY_DATE_FILE" ]; then
        last_date=$(cat "$DAILY_DATE_FILE" 2>/dev/null || echo "")
    fi
    
    if [ "$last_date" != "$today" ]; then
        # New day - reset daily count
        echo "0" > "$DAILY_COUNT_FILE"
        echo "$today" > "$DAILY_DATE_FILE"
        log_message "Daily count reset for new day: $today"
    fi
}

# Function to increment contact count (both daily and total)
increment_contact_count() {
    # Check if we need to reset daily count
    check_daily_reset
    
    # Increment total count
    local total_count=0
    if [ -f "$TOTAL_COUNT_FILE" ]; then
        total_count=$(cat "$TOTAL_COUNT_FILE" 2>/dev/null || echo "0")
        total_count=$((total_count + 0))  # Ensure it's a number
    fi
    total_count=$((total_count + 1))
    echo "$total_count" > "$TOTAL_COUNT_FILE"
    
    # Increment daily count
    local daily_count=0
    if [ -f "$DAILY_COUNT_FILE" ]; then
        daily_count=$(cat "$DAILY_COUNT_FILE" 2>/dev/null || echo "0")
        daily_count=$((daily_count + 0))  # Ensure it's a number
    fi
    daily_count=$((daily_count + 1))
    echo "$daily_count" > "$DAILY_COUNT_FILE"
    
    local today=$(date '+%Y-%m-%d')
    log_message "Box opened - Daily: $daily_count, Total: $total_count (Date: $today)"
    echo "$total_count"
}

# Function to reset daily count
reset_daily_count() {
    echo "0" > "$DAILY_COUNT_FILE"
    local today=$(date '+%Y-%m-%d')
    echo "$today" > "$DAILY_DATE_FILE"
    log_message "Daily count reset to 0"
}

# Function to reset total count
reset_total_count() {
    echo "0" > "$TOTAL_COUNT_FILE"
    log_message "Total count reset to 0"
}

# Arguments:
# $1: Event Type (e.g., "contact", "reset")
# $2: Action (e.g., count for contact, or empty for reset)

TYPE=$1
ACTION=$2

log_message "Event received: Type=$TYPE, Action=$ACTION"

case "$TYPE" in
    "contact")
        # Contact closure event from ESP32 listener (box opened)
        # ACTION contains the count (from ESP32, but we track our own)
        increment_contact_count
        ;;
    "reset_daily")
        # Reset daily count
        reset_daily_count
        ;;
    "reset_total")
        # Reset total count
        reset_total_count
        ;;
    "reset")
        # Reset both daily and total counts
        reset_daily_count
        reset_total_count
        ;;
    *)
        # Default or unknown event
        log_message "Unknown event type: $TYPE"
        ;;
esac

