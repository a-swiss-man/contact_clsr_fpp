#!/usr/bin/env python3
"""
Reset Neo Trinkey Script
This script sends the 'O' command to the Neo Trinkey to turn it off.
"""

import os
import sys
import glob
from datetime import datetime

# Try to import serial, but make it optional
try:
    import serial
    SERIAL_AVAILABLE = True
except ImportError:
    SERIAL_AVAILABLE = False

LOG_FILE = "/home/fpp/media/logs/contact_clsr_fpp.log"

def log_message(message):
    """Log a message to the log file"""
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    with open(LOG_FILE, 'a') as f:
        f.write(f"{timestamp} - [RESET] {message}\n")

def find_neotrinkey_device():
    """Find the Neo Trinkey USB serial device"""
    # Common USB serial device patterns
    patterns = ['/dev/ttyACM*', '/dev/ttyUSB*', '/dev/tty.usbmodem*']
    
    for pattern in patterns:
        devices = glob.glob(pattern)
        for device in devices:
            try:
                # Check if device exists and is accessible
                if os.path.exists(device) and os.access(device, os.W_OK):
                    # Try to open it to verify it's a serial device
                    if SERIAL_AVAILABLE:
                        try:
                            ser = serial.Serial(device, 115200, timeout=1)
                            ser.close()
                        except:
                            continue
                    log_message(f"Found Neo Trinkey at {device}")
                    return device
            except (OSError, PermissionError):
                continue
    
    log_message("Error: Neo Trinkey device not found")
    return None

def reset_trinkey():
    """Send 'O' command to turn off the Neo Trinkey"""
    device = find_neotrinkey_device()
    
    if device is None:
        log_message("Cannot reset - Neo Trinkey not found")
        print("ERROR: Neo Trinkey device not found", file=sys.stderr)
        return False
    
    try:
        if SERIAL_AVAILABLE:
            # Use serial library if available
            ser = serial.Serial(device, 115200, timeout=1)
            ser.write(b'O\n')
            ser.flush()
            ser.close()
        else:
            # Use direct file I/O if serial library not available
            with open(device, 'wb') as f:
                f.write(b'O\n')
                f.flush()
        
        log_message("Successfully sent reset command (O) to Neo Trinkey")
        print("SUCCESS: Neo Trinkey reset (turned off)")
        return True
    except (OSError, PermissionError, IOError) as e:
        log_message(f"Error sending reset command to Neo Trinkey: {e}")
        print(f"ERROR: {e}", file=sys.stderr)
        return False

if __name__ == "__main__":
    success = reset_trinkey()
    sys.exit(0 if success else 1)
