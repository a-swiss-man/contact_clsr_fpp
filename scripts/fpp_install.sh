#!/bin/bash

# fpp_install.sh
# This script runs when the plugin is installed.

echo "Installing ESP32 NeoPixel Contact Closure Plugin..."

PLUGIN_DIR="/home/fpp/media/plugins/contact_clsr_fpp"
CALLBACKS_SCRIPT="$PLUGIN_DIR/callbacks.sh"
LISTENER_SCRIPT="$PLUGIN_DIR/scripts/esp32_listener.sh"
LOG_FILE="/home/fpp/media/logs/contact_clsr_fpp.log"

# Ensure the callbacks script is executable
chmod +x "$CALLBACKS_SCRIPT"

# Ensure the listener scripts are executable
if [ -f "$LISTENER_SCRIPT" ]; then
    chmod +x "$LISTENER_SCRIPT"
    echo "✓ ESP32 listener script is executable"
fi

PYTHON_LISTENER="$PLUGIN_DIR/scripts/esp32_listener.py"
if [ -f "$PYTHON_LISTENER" ]; then
    chmod +x "$PYTHON_LISTENER"
    echo "✓ Python listener script is executable"
fi

# Create log file if it doesn't exist
touch "$LOG_FILE"
chmod 666 "$LOG_FILE"

# Create config directory if it doesn't exist
mkdir -p /home/fpp/media/config
chmod 755 /home/fpp/media/config

# Create contact count file
CONTACT_COUNT_FILE="/home/fpp/media/config/contact_clsr_fpp_count.txt"
touch "$CONTACT_COUNT_FILE"
chmod 666 "$CONTACT_COUNT_FILE"
echo "0" > "$CONTACT_COUNT_FILE"

# Set up systemd service for ESP32 listener
SERVICE_FILE="$PLUGIN_DIR/scripts/esp32-listener.service"
SYSTEMD_DIR="/etc/systemd/system"

if [ -f "$SERVICE_FILE" ]; then
    echo ""
    echo "Setting up systemd service for ESP32 listener..."
    
    # Copy service file to systemd directory
    if sudo cp "$SERVICE_FILE" "$SYSTEMD_DIR/esp32-listener.service" 2>/dev/null; then
        echo "✓ Service file installed to $SYSTEMD_DIR"
        
        # Reload systemd
        if sudo systemctl daemon-reload 2>/dev/null; then
            echo "✓ Systemd daemon reloaded"
            
            # Enable service to start on boot
            if sudo systemctl enable esp32-listener.service 2>/dev/null; then
                echo "✓ Service enabled for automatic startup"
            else
                echo "⚠ Warning: Could not enable service (may need sudo)"
            fi
            
            # Start the service now
            if sudo systemctl start esp32-listener.service 2>/dev/null; then
                echo "✓ Service started"
            else
                echo "⚠ Warning: Could not start service (may need sudo)"
                echo "  You can start it manually with: sudo systemctl start esp32-listener"
            fi
        else
            echo "⚠ Warning: Could not reload systemd (may need sudo)"
        fi
    else
        echo "⚠ Warning: Could not install service file (may need sudo)"
        echo "  You can install it manually:"
        echo "    sudo cp $SERVICE_FILE $SYSTEMD_DIR/"
        echo "    sudo systemctl daemon-reload"
        echo "    sudo systemctl enable esp32-listener"
        echo "    sudo systemctl start esp32-listener"
    fi
else
    echo "⚠ Warning: Service file not found: $SERVICE_FILE"
fi

# Test that callbacks.sh can be executed
if [ -x "$CALLBACKS_SCRIPT" ]; then
    echo "✓ Callbacks script is executable"
else
    echo "✗ Error: Callbacks script is not executable"
fi

# Verify plugin structure
echo ""
echo "Verifying plugin structure..."
if [ -f "$PLUGIN_DIR/pluginInfo.json" ] && [ -f "$CALLBACKS_SCRIPT" ]; then
    echo "✓ Plugin structure matches FPP expectations"
    echo "  - pluginInfo.json: Found"
    echo "  - callbacks.sh: Found and executable"
else
    echo "⚠ Warning: Plugin structure may be incomplete"
fi

echo ""
echo "Installation Complete."
echo ""
echo "Plugin Functionality:"
echo "  - ESP32 sends contact closure events to FPP"
echo "  - FPP listener triggers Neo Trinkey rainbow chase effect"
echo "  - Contact count is tracked in: $CONTACT_COUNT_FILE"
echo ""
echo "IMPORTANT - After installation:"
echo "  1. RESTART FPPD: sudo systemctl restart fppd"
echo "  2. Configure ESP32 IP address in plugin configuration"
echo "  3. Verify plugin is enabled in FPP web interface (Settings > Plugins)"
echo "  4. Check log file: $LOG_FILE"
echo ""
echo "ESP32 Listener Service:"
echo "  - Listens for contact closure messages from ESP32 on port 8081"
echo "  - Sends rainbow command to Neo Trinkey via USB serial"
echo "  - Service: esp32-listener.service"
echo "  - Status: sudo systemctl status esp32-listener"
echo "  - Logs: tail -f $LOG_FILE"
echo ""

