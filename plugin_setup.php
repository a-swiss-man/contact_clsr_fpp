<?php
// FPP ESP32 NeoPixel Contact Closure Plugin Setup

$logFile = "/home/fpp/media/logs/esp32_neopixel_contact.log";
$configFile = "/home/fpp/media/config/esp32_neopixel_contact.conf";

if (isset($_POST['clear_log'])) {
    if (file_exists($logFile)) {
        file_put_contents($logFile, "");
        echo "<script>$.jGrowl('Log cleared');</script>";
    }
}

$logContent = "";
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
} else {
    $logContent = "Log file not found.";
}

// Load current configuration
$currentIP = "";
$currentPort = 8080;
$currentContactCount = 0;
if (file_exists($configFile)) {
    $config = parse_ini_file($configFile);
    if (isset($config['esp32_ip']) && !empty($config['esp32_ip'])) {
        $currentIP = $config['esp32_ip'];
    }
    if (isset($config['esp32_port'])) {
        $currentPort = intval($config['esp32_port']);
    }
    if (isset($config['contact_count'])) {
        $currentContactCount = intval($config['contact_count']);
    }
}
?>

<div id="esp32_neopixel_setup" class="settings">
    <fieldset>
        <legend>ESP32 NeoPixel Contact Closure</legend>
        <p>This plugin communicates with a Waveshare ESP32 S3 ETH device via Ethernet to handle contact closure inputs and control NeoPixel status.</p>
        <p><b>Instructions:</b></p>
        <ul>
            <li>Upload the Arduino firmware to your Waveshare ESP32 S3 ETH device (found in the <code>arduino_firmware</code> folder).</li>
            <li>Configure the ESP32 IP address and port in the configuration section.</li>
            <li>Connect your contact closure input to the configured GPIO pin on the ESP32.</li>
            <li>The plugin will receive contact closure events and can send NeoPixel control commands.</li>
        </ul>
        <p><b>Current ESP32 IP:</b> <code><?php echo htmlspecialchars($currentIP ?: 'Not configured'); ?></code></p>
        <p><b>Current Port:</b> <code><?php echo $currentPort; ?></code></p>
        <p><b>Contact Closure Count:</b> <code><?php echo $currentContactCount; ?></code></p>
        <p>Log File: <code><?php echo $logFile; ?></code></p>
    </fieldset>

    <fieldset>
        <legend>Log Viewer</legend>
        <form method="post" action="">
            <textarea style="width: 100%; height: 300px; font-family: monospace;" readonly><?php echo htmlspecialchars($logContent); ?></textarea>
            <br><br>
            <input type="submit" name="clear_log" value="Clear Log" class="buttons">
            <input type="button" value="Refresh" onclick="location.reload();" class="buttons">
        </form>
    </fieldset>
</div>

