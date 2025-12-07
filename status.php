<?php
// FPP ESP32 NeoPixel Contact Closure Plugin - Status Viewer

$logFile = "/home/fpp/media/logs/contact_clsr_fpp.log";
$configFile = "/home/fpp/media/config/contact_clsr_fpp.conf";
$totalCountFile = "/home/fpp/media/config/contact_clsr_fpp_total.txt";
$dailyCountFile = "/home/fpp/media/config/contact_clsr_fpp_daily.txt";
$dailyDateFile = "/home/fpp/media/config/contact_clsr_fpp_daily_date.txt";

if (isset($_POST['clear_log'])) {
    if (file_exists($logFile)) {
        file_put_contents($logFile, "");
        echo "<script>$.jGrowl('Log cleared');</script>";
    }
}

// Handle reset trinkey
if (isset($_POST['reset_trinkey'])) {
    $pluginDir = "/home/fpp/media/plugins/contact_clsr_fpp";
    $resetScript = "$pluginDir/scripts/reset_trinkey.py";
    $output = [];
    $returnVar = 0;
    exec("python3 $resetScript 2>&1", $output, $returnVar);
    if ($returnVar === 0) {
        echo "<script>$.jGrowl('Neo Trinkey reset (turned off)');</script>";
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "$timestamp - [UI] Reset Trinkey requested\n", FILE_APPEND);
    } else {
        echo "<script>$.jGrowl('Error resetting Neo Trinkey. Check logs for details.', {life: 5000});</script>";
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "$timestamp - [UI] Reset Trinkey failed: " . implode("\n", $output) . "\n", FILE_APPEND);
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

// Get counts
$currentTotalCount = 0;
if (file_exists($totalCountFile)) {
    $currentTotalCount = intval(file_get_contents($totalCountFile));
}

$currentDailyCount = 0;
$currentDate = date('Y-m-d');
$lastDate = "";
if (file_exists($dailyDateFile)) {
    $lastDate = trim(file_get_contents($dailyDateFile));
}
if ($lastDate == $currentDate && file_exists($dailyCountFile)) {
    $currentDailyCount = intval(file_get_contents($dailyCountFile));
}
?>

<div id="esp32_neopixel_status" class="settings">
    <fieldset>
        <legend>Food Donation Box - Status</legend>
        <p>This plugin monitors a food donation box using an ESP32 S3 ETH device. When the box door is opened, it triggers the Neo Trinkey to display a rainbow chase effect and logs the event.</p>
        <table style="width: 100%; margin: 10px 0;">
            <tr>
                <td style="padding: 5px;"><b>Current ESP32 IP:</b></td>
                <td style="padding: 5px;"><code><?php echo htmlspecialchars($currentIP); ?></code></td>
            </tr>
            <tr>
                <td style="padding: 5px;"><b>Today's Opens (<?php echo $currentDate; ?>):</b></td>
                <td style="padding: 5px;"><code style="font-size: 18px;"><?php echo $currentDailyCount; ?></code></td>
            </tr>
            <tr>
                <td style="padding: 5px;"><b>Total Opens (All Time):</b></td>
                <td style="padding: 5px;"><code style="font-size: 18px;"><?php echo $currentTotalCount; ?></code></td>
            </tr>
            <tr>
                <td style="padding: 5px;"><b>Log File:</b></td>
                <td style="padding: 5px;"><code><?php echo $logFile; ?></code></td>
            </tr>
        </table>
        <form method="post" action="" style="margin: 10px 0;">
            <input type="submit" name="reset_trinkey" value="Reset Trinkey (Turn Off)" class="buttons" 
                   onclick="return confirm('Are you sure you want to reset the Neo Trinkey (turn it off)?');">
        </form>
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

