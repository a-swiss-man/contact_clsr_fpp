<?php
// FPP ESP32 NeoPixel Contact Closure Plugin - About Page
?>

<div id="esp32_neopixel_about" class="settings">
    <fieldset>
        <legend>ESP32 NeoPixel Contact Closure Plugin - About</legend>
        
        <h3>Overview</h3>
        <p>This plugin enables communication between FPP and a Waveshare ESP32 S3 ETH device for contact closure input and NeoPixel status control.</p>
        
        <h3>Features</h3>
        <ul>
            <li>Receives contact closure events from ESP32 via Ethernet</li>
            <li>Sends NeoPixel control commands to ESP32 (Idle, Playing, Stopped, Rainbow)</li>
            <li>Tracks total contact closure count</li>
            <li>Reset functionality to clear contact closure count</li>
            <li>Ethernet-based communication (no USB required)</li>
        </ul>
        
        <h3>Hardware Requirements</h3>
        <ul>
            <li>Waveshare ESP32 S3 ETH development board</li>
            <li>NeoPixel LED strip or ring</li>
            <li>Contact closure sensor/switch</li>
            <li>Ethernet connection to FPP network</li>
        </ul>
        
        <h3>Setup Instructions</h3>
        <ol>
            <li>Upload the Arduino firmware to your ESP32 S3 ETH (found in <code>arduino_firmware</code> folder)</li>
            <li>Configure the ESP32's IP address in the plugin configuration</li>
            <li>Connect your contact closure input to the configured GPIO pin</li>
            <li>Connect NeoPixel data line to the configured GPIO pin</li>
            <li>Ensure ESP32 is on the same network as FPP</li>
        </ol>
        
        <h3>Communication Protocol</h3>
        <p>The plugin communicates with the ESP32 via TCP on port 8080 (configurable).</p>
        <p><b>Commands sent to ESP32:</b></p>
        <ul>
            <li><code>I</code> - Set NeoPixel to Idle (Blue)</li>
            <li><code>P</code> - Set NeoPixel to Playing (Green)</li>
            <li><code>S</code> - Set NeoPixel to Stopped (Red)</li>
            <li><code>R</code> - Set NeoPixel to Rainbow Cycle</li>
            <li><code>RESET</code> - Reset contact closure count</li>
        </ul>
        
        <p><b>Messages received from ESP32:</b></p>
        <ul>
            <li><code>CONTACT:&lt;count&gt;</code> - Contact closure detected (count is total triggers)</li>
            <li><code>STATUS:&lt;status&gt;</code> - ESP32 status update</li>
        </ul>
        
        <h3>Version</h3>
        <p>Version 0.0.1</p>
        
        <h3>Author</h3>
        <p>FPP User</p>
    </fieldset>
</div>

