#!/bin/bash

# fpp_uninstall.sh
# This script runs when the plugin is uninstalled.

echo "Uninstalling ESP32 NeoPixel Contact Closure Plugin..."

PLUGIN_DIR="/home/fpp/media/plugins/contact_clsr_fpp"
SERVICE_NAME="esp32-listener.service"
SYSTEMD_DIR="/etc/systemd/system"

# Stop and disable the listener service
if sudo systemctl is-active "$SERVICE_NAME" >/dev/null 2>&1; then
    echo "Stopping ESP32 listener service..."
    sudo systemctl stop "$SERVICE_NAME" 2>/dev/null
fi

if sudo systemctl is-enabled "$SERVICE_NAME" >/dev/null 2>&1; then
    echo "Disabling ESP32 listener service..."
    sudo systemctl disable "$SERVICE_NAME" 2>/dev/null
fi

# Remove service file
if [ -f "$SYSTEMD_DIR/$SERVICE_NAME" ]; then
    echo "Removing service file..."
    sudo rm "$SYSTEMD_DIR/$SERVICE_NAME" 2>/dev/null
    sudo systemctl daemon-reload 2>/dev/null
fi

# Remove event hooks
EVENTS_DIR="/home/fpp/media/events"
if [ -d "$EVENTS_DIR" ]; then
    echo "Removing event hooks..."
    rm -f "$EVENTS_DIR"/playlist-*.sh 2>/dev/null
    rm -f "$EVENTS_DIR"/media-*.sh 2>/dev/null
    rm -f "$EVENTS_DIR"/fppd-*.sh 2>/dev/null
    rm -f "$EVENTS_DIR"/playlist_*.sh 2>/dev/null
    rm -f "$EVENTS_DIR"/media_*.sh 2>/dev/null
    rm -f "$EVENTS_DIR"/fppd_*.sh 2>/dev/null
fi

echo ""
echo "Uninstallation Complete."
echo ""
echo "Note: Configuration files and logs are preserved:"
echo "  - Config: /home/fpp/media/config/contact_clsr_fpp.conf"
echo "  - Log: /home/fpp/media/logs/contact_clsr_fpp.log"
echo "  - Total Count: /home/fpp/media/config/contact_clsr_fpp_total.txt"
echo "  - Daily Count: /home/fpp/media/config/contact_clsr_fpp_daily.txt"
echo "  - Daily Date: /home/fpp/media/config/contact_clsr_fpp_daily_date.txt"
echo ""
echo "To completely remove, manually delete these files if desired."

