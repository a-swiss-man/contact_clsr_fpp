# ESP32 Contact Closure to Neo Trinkey Plugin

This FPP plugin enables communication between FPP, a Waveshare ESP32 S3 ETH device, and an Adafruit Neo Trinkey. When the ESP32 detects a contact closure, it sends an event to FPP, which triggers the Neo Trinkey to display a rainbow chase effect.

## Features

- **Contact Closure Input**: Monitors contact closure events from ESP32 via Ethernet
- **Neo Trinkey Control**: Automatically triggers rainbow chase on the Neo Trinkey when contact closure is detected
- **Contact Count Tracking**: Tracks total contact closure count
- **Reset Functionality**: Reset contact closure count
- **Ethernet Communication**: ESP32 uses Ethernet for reliable communication with FPP
- **USB Serial Control**: FPP controls Neo Trinkey via USB serial connection

## Hardware Requirements

- Waveshare ESP32 S3 ETH development board
- Adafruit Neo Trinkey (connected to FPP via USB)
- Contact closure sensor/switch
- Ethernet connection to FPP network

## Installation

1. Copy the plugin folder to `/home/fpp/media/plugins/esp32_neopixel_contact`
2. Install the plugin through FPP's plugin interface
3. Configure the ESP32 IP address in the plugin configuration page
4. Upload the Arduino firmware to your ESP32 S3 ETH (found in `arduino_firmware` folder)
5. Restart FPPD: `sudo systemctl restart fppd`

## Configuration

### ESP32 Setup

1. **Upload Firmware**: Use Arduino IDE to upload `arduino_firmware/esp32_neopixel_contact.ino` to your ESP32 S3 ETH
2. **Configure Network**: The ESP32 will use DHCP by default. Note its IP address.
3. **Hardware Connections**:
   - Contact closure input: Connect to GPIO2 (default, configurable in code)
   - Update FPP_SERVER_IP in the code to match your FPP server IP address

### Neo Trinkey Setup

1. **Upload Firmware**: Copy `circuitpython_firmware/neotrinkey_controller.py` to your Neo Trinkey as `code.py`
2. **Connect to FPP**: Connect the Neo Trinkey to the FPP system via USB
3. **Verify Connection**: The listener will auto-detect the Neo Trinkey USB serial device

### Plugin Configuration

1. Go to FPP web interface → Status → ESP32 NeoPixel Contact - Configuration
2. Enter the ESP32 IP address
3. Configure the port (default: 8080)
4. Save configuration

## Communication Protocol

### Messages Received from ESP32

- `CONTACT:<count>` - Contact closure detected (count is total triggers)

### Commands Sent to Neo Trinkey

- `R` - Start rainbow chase effect
- `O` - Turn off (all pixels off)

## How It Works

1. **Contact Closure Detection**: ESP32 monitors the contact closure input pin
2. **Event Transmission**: When contact closure is detected, ESP32 sends `CONTACT:<count>` to FPP on port 8081
3. **Neo Trinkey Control**: FPP listener receives the contact event and sends `R` command to Neo Trinkey via USB serial
4. **Rainbow Display**: Neo Trinkey displays rainbow chase effect when `R` is received
5. **Default State**: Neo Trinkey remains OFF when no contact closure is detected
6. **Count Tracking**: Plugin maintains a count of contact closures in `/home/fpp/media/config/esp32_neopixel_contact_count.txt`

## File Structure

```
esp32_neopixel_contact/
├── pluginInfo.json          # Plugin metadata
├── menu.inc                 # Menu configuration
├── config.php              # Configuration page
├── status.php              # Status viewer
├── plugin_setup.php        # Setup page
├── callbacks.sh            # FPP event handler
├── arduino_firmware/       # ESP32 Arduino code
│   └── esp32_neopixel_contact.ino
├── circuitpython_firmware/ # Neo Trinkey CircuitPython code
│   ├── neotrinkey_controller.py  # Neo Trinkey controller (copy to Neo Trinkey as code.py)
│   └── code.py  # Legacy code (not used)
├── scripts/
│   ├── fpp_install.sh      # Installation script
│   ├── fpp_uninstall.sh    # Uninstallation script
│   ├── esp32_listener.sh   # Message listener (wrapper)
│   ├── esp32_listener.py   # Python message listener
│   └── esp32-listener.service  # Systemd service file
└── help/
    └── about.php           # About page
```

## Troubleshooting

### ESP32 Not Responding

1. Check ESP32 IP address is correct in configuration
2. Verify ESP32 is on the same network as FPP
3. Check ESP32 serial output for connection status
4. Verify Ethernet cable is connected

### Contact Closures Not Detected

1. Check contact closure input pin configuration
2. Verify pull-up/pull-down resistor setup
3. Check debounce settings in Arduino code
4. Review log file: `/home/fpp/media/logs/esp32_neopixel_contact.log`

### Neo Trinkey Not Responding

1. Verify Neo Trinkey is connected via USB to the FPP system
2. Check that `neotrinkey_controller.py` is uploaded to Neo Trinkey as `code.py`
3. Check log file for Neo Trinkey device detection messages
4. Verify USB serial device permissions (may need to add user to dialout group)
5. Try manually sending commands: `echo "R" > /dev/ttyACM0` (adjust device path as needed)

### Listener Service Not Running

```bash
# Check service status
sudo systemctl status esp32-listener

# Start service manually
sudo systemctl start esp32-listener

# View logs
sudo journalctl -u esp32-listener -f
```

## Customization

### Change GPIO Pins

Edit `arduino_firmware/esp32_neopixel_contact.ino`:
```cpp
#define CONTACT_PIN 2    // Change contact closure pin
```

### Change Network Settings

Edit the Arduino code:
```cpp
#define FPP_SERVER_IP "192.168.1.100"  // FPP server IP
#define FPP_SERVER_PORT 8081            // Port to send to FPP
```

## Version History

- **0.1.0** - Simplified version
  - ESP32 only sends contact closure events (no status receiving)
  - Neo Trinkey control via USB serial
  - Rainbow chase triggered on contact closure
  - Removed FPP status handling
  - Contact count tracking

- **0.0.1** - Initial release (deprecated)
  - Contact closure detection
  - NeoPixel control on ESP32
  - Ethernet communication
  - FPP status handling

## License

This plugin is provided as-is for use with FPP (Falcon Player).

