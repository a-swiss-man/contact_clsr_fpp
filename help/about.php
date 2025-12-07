<?php
// FPP ESP32 Contact Closure Plugin - About Page
?>

<div id="contact_clsr_about" class="settings">
    <fieldset>
        <legend>Food Donation Box Monitor - About</legend>
        
        <h3>Overview</h3>
        <p>This plugin monitors a food donation box using an ESP32 S3 ETH device. When the box door is opened (contact closure detected), the system triggers a visual indicator (Neo Trinkey rainbow chase effect) and logs the event with daily and total statistics.</p>
        
        <h3>Features</h3>
        <ul>
            <li><b>Contact Closure Monitoring:</b> Detects when the donation box door is opened via ESP32 S3 ETH</li>
            <li><b>Daily Opens Tracking:</b> Automatically tracks and resets daily open count at midnight</li>
            <li><b>Total Opens Tracking:</b> Maintains a cumulative count of all box opens</li>
            <li><b>Visual Indicator:</b> Triggers Neo Trinkey rainbow chase effect when box is opened</li>
            <li><b>Ethernet Communication:</b> Reliable Ethernet-based communication (no USB required)</li>
            <li><b>Event Logging:</b> All box opens are logged with timestamps</li>
        </ul>
        
        <h3>Hardware Requirements</h3>
        <ul>
            <li><b>Waveshare ESP32 S3 ETH</b> development board with W5500 Ethernet chip</li>
            <li><b>Contact Closure Sensor:</b> Door switch or magnetic sensor connected to ESP32 GPIO pin</li>
            <li><b>Adafruit Neo Trinkey:</b> USB-connected device for visual feedback (rainbow chase effect)</li>
            <li><b>Ethernet Connection:</b> ESP32 connected to same network as FPP</li>
        </ul>
        
        <h3>How It Works</h3>
        <ol>
            <li><b>Box Opening Detection:</b> When the donation box door opens, the contact closure sensor triggers</li>
            <li><b>ESP32 Processing:</b> ESP32 detects the contact closure and sends an event to FPP via Ethernet</li>
            <li><b>FPP Processing:</b> FPP receives the event and:
                <ul>
                    <li>Increments daily and total open counts</li>
                    <li>Logs the event with timestamp</li>
                    <li>Sends rainbow command to Neo Trinkey</li>
                </ul>
            </li>
            <li><b>Visual Feedback:</b> Neo Trinkey displays a rainbow chase effect to indicate the box was opened</li>
            <li><b>Statistics:</b> Daily count automatically resets at midnight; total count continues accumulating</li>
        </ol>
        
        <h3>Setup Instructions</h3>
        <ol>
            <li><b>Upload Firmware:</b> Upload the Arduino firmware to your ESP32 S3 ETH (found in <code>arduino_firmware</code> folder)</li>
            <li><b>Configure ESP32:</b> Update the FPP server IP address in the Arduino code</li>
            <li><b>Install Neo Trinkey Firmware:</b> Copy <code>circuitpython_firmware/neotrinkey_controller.py</code> to Neo Trinkey as <code>code.py</code></li>
            <li><b>Connect Hardware:</b>
                <ul>
                    <li>Connect contact closure sensor to ESP32 GPIO pin 2 (default, configurable)</li>
                    <li>Connect Neo Trinkey to FPP system via USB</li>
                    <li>Connect ESP32 to network via Ethernet</li>
                </ul>
            </li>
            <li><b>Configure Plugin:</b> Enter ESP32 IP address in the plugin configuration page</li>
            <li><b>Verify Operation:</b> Open the box and verify Neo Trinkey displays rainbow effect</li>
        </ol>
        
        <h3>Statistics</h3>
        <p>The plugin tracks two types of statistics:</p>
        <ul>
            <li><b>Daily Opens:</b> Number of times the box was opened today. Automatically resets at midnight.</li>
            <li><b>Total Opens:</b> Cumulative count of all box opens since installation or last reset.</li>
        </ul>
        <p>Both statistics are displayed on the Configuration and Status pages, and can be manually reset if needed.</p>
        
        <h3>Communication Protocol</h3>
        <p>The ESP32 communicates with FPP via TCP on port 8081.</p>
        <p><b>Messages from ESP32 to FPP:</b></p>
        <ul>
            <li><code>CONTACT:&lt;count&gt;</code> - Box opened (count is ESP32's internal counter)</li>
        </ul>
        
        <p><b>Commands from FPP to Neo Trinkey (via USB serial):</b></p>
        <ul>
            <li><code>R</code> - Start rainbow chase effect</li>
            <li><code>O</code> - Turn off (all pixels off)</li>
        </ul>
        
        <h3>Troubleshooting</h3>
        <ul>
            <li><b>No rainbow effect:</b> Check that Neo Trinkey is connected via USB and firmware is uploaded</li>
            <li><b>No events received:</b> Verify ESP32 IP address is correct and ESP32 is on the network</li>
            <li><b>Counts not updating:</b> Check log file for errors and verify listener service is running</li>
            <li><b>Daily count not resetting:</b> Daily reset happens automatically when first event of new day is received</li>
        </ul>
        
        <h3>Version</h3>
        <p>Version 0.1.0</p>
        
        <h3>Author</h3>
        <p>FPP User</p>
        
        <h3>Use Case</h3>
        <p>This plugin is designed for monitoring food donation boxes. Each time someone opens the box to donate food, the system:</p>
        <ul>
            <li>Records the event with a timestamp</li>
            <li>Updates daily and total statistics</li>
            <li>Provides visual feedback via the Neo Trinkey rainbow effect</li>
        </ul>
        <p>This allows organizations to track donation activity and see how often the box is being used.</p>
    </fieldset>
</div>
