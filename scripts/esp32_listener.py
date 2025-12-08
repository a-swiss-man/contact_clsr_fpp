#!/usr/bin/env python3
"""
ESP32 Message Listener
This script listens for incoming messages from the ESP32 and processes them.
When contact closure is detected, it sends a rainbow command to the Neo Trinkey.
"""

import socket
import subprocess
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

PLUGIN_DIR = "/home/fpp/media/plugins/contact_clsr_fpp"
CALLBACKS_SCRIPT = os.path.join(PLUGIN_DIR, "callbacks.sh")
LOG_FILE = "/home/fpp/media/logs/contact_clsr_fpp.log"
LISTENER_PORT = 8081  # Port to listen on for ESP32 messages

# Neo Trinkey USB serial device (will be auto-detected)
NEO_TRINKEY_DEVICE = None

def log_message(message):
    """Log a message to the log file"""
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    with open(LOG_FILE, 'a') as f:
        f.write(f"{timestamp} - [LISTENER] {message}\n")

def find_neotrinkey_device():
    """Find the Neo Trinkey USB serial device"""
    global NEO_TRINKEY_DEVICE
    
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
                    NEO_TRINKEY_DEVICE = device
                    log_message(f"Found Neo Trinkey at {device}")
                    return device
            except (OSError, PermissionError):
                continue
    
    log_message("Warning: Neo Trinkey device not found. Will retry on next contact closure.")
    return None

def send_to_neotrinkey(command):
    """Send a command to the Neo Trinkey via USB serial"""
    global NEO_TRINKEY_DEVICE
    
    # Try to find device if not already found
    if NEO_TRINKEY_DEVICE is None:
        find_neotrinkey_device()
    
    if NEO_TRINKEY_DEVICE is None:
        log_message(f"Error: Cannot send command '{command}' - Neo Trinkey not found")
        return False
    
    try:
        if SERIAL_AVAILABLE:
            # Use serial library if available
            ser = serial.Serial(NEO_TRINKEY_DEVICE, 115200, timeout=1)
            ser.write((command + '\n').encode('utf-8'))
            ser.flush()
            ser.close()
        else:
            # Use direct file I/O if serial library not available
            with open(NEO_TRINKEY_DEVICE, 'wb') as f:
                f.write((command + '\n').encode('utf-8'))
                f.flush()
        
        log_message(f"Sent command '{command}' to Neo Trinkey")
        return True
    except (OSError, PermissionError, IOError) as e:
        log_message(f"Error sending command to Neo Trinkey: {e}")
        # Reset device path so we try to find it again next time
        NEO_TRINKEY_DEVICE = None
        return False

def process_message(message):
    """Process incoming message from ESP32"""
    message = message.strip()
    if not message:
        return
    
    log_message(f"Received message from ESP32: {message}")
    
    # Parse message format: "CONTACT:<count>"
    if message.startswith("CONTACT:"):
        # Contact closure detected
        count = message.split(":")[1] if ":" in message else "0"
        log_message(f"Contact closure detected, count: {count}")
        
        # Send rainbow command to Neo Trinkey
        send_to_neotrinkey("R")
        
        # Trigger callback to increment count
        try:
            subprocess.run([CALLBACKS_SCRIPT, "contact", count], check=True)
        except subprocess.CalledProcessError as e:
            log_message(f"Error calling callback script: {e}")
    elif message.startswith("TRINKEY:OFF"):
        # Turn off Trinkey command
        log_message("Received TRINKEY:OFF command")
        send_to_neotrinkey("O")
    else:
        log_message(f"Unknown message format: {message}")

def main():
    """Main listener loop"""
    log_message(f"ESP32 listener started on port {LISTENER_PORT}")
    
    # Create TCP socket
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    
    try:
        sock.bind(('0.0.0.0', LISTENER_PORT))
        sock.listen(5)
        log_message(f"Listening on port {LISTENER_PORT}")
        
        while True:
            try:
                # Accept incoming connection
                client_socket, address = sock.accept()
                log_message(f"Connection from {address[0]}:{address[1]}")
                
                # Receive data
                data = client_socket.recv(1024).decode('utf-8')
                if data:
                    process_message(data)
                
                # Close connection
                client_socket.close()
                
            except socket.error as e:
                log_message(f"Socket error: {e}")
                continue
            except KeyboardInterrupt:
                log_message("Listener stopped by user")
                break
            except Exception as e:
                log_message(f"Unexpected error: {e}")
                continue
                
    except socket.error as e:
        log_message(f"Failed to bind to port {LISTENER_PORT}: {e}")
        sys.exit(1)
    finally:
        sock.close()
        log_message("Listener socket closed")

if __name__ == "__main__":
    main()

