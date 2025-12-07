<?php
// FPP ESP32 NeoPixel Contact Closure Plugin - Status Viewer

$logFile = "/home/fpp/media/logs/contact_clsr_fpp.log";
$configFile = "/home/fpp/media/config/contact_clsr_fpp.conf";
$contactCountFile = "/home/fpp/media/config/contact_clsr_fpp_count.txt";

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

// Get current device from config if available
$currentIP = "Not configured";
if (file_exists($configFile)) {
    $config = parse_ini_file($configFile);
    if (isset($config['esp32_ip']) && !empty($config['esp32_ip'])) {
        $currentIP = $config['esp32_ip'];
    }
}

$currentContactCount = 0;
if (file_exists($contactCountFile)) {
    $currentContactCount = intval(file_get_contents($contactCountFile));
}
?>

<div id="esp32_neopixel_status" class="settings">
    <fieldset>
        <legend>ESP32 NeoPixel Contact Closure - Status</legend>
        <p>This plugin communicates with an ESP32 S3 ETH device via Ethernet to handle contact closure inputs. When a contact closure is detected, it triggers the Neo Trinkey to display a rainbow chase effect.</p>
        <p><b>Current ESP32 IP:</b> <code><?php echo htmlspecialchars($currentIP); ?></code></p>
        <p><b>Contact Closure Count:</b> <code><?php echo $currentContactCount; ?></code></p>
        <p><b>Log File:</b> <code><?php echo $logFile; ?></code></p>
    </fieldset>

    <fieldset>
        <legend>Log Viewer</legend>
        <form method="post" action="">
            <textarea style="width: 100%; height: 400px; font-family: monospace; font-size: 12px;" readonly><?php echo htmlspecialchars($logContent); ?></textarea>
            <br><br>
            <input type="submit" name="clear_log" value="Clear Log" class="buttons">
            <input type="button" value="Refresh" onclick="location.reload();" class="buttons">
        </form>
    </fieldset>
</div>

