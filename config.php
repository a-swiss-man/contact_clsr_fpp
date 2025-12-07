<?php
// FPP ESP32 NeoPixel Contact Closure Plugin - Configuration

$configFile = "/home/fpp/media/config/contact_clsr_fpp.conf";
$logFile = "/home/fpp/media/logs/contact_clsr_fpp.log";
$totalCountFile = "/home/fpp/media/config/contact_clsr_fpp_total.txt";
$dailyCountFile = "/home/fpp/media/config/contact_clsr_fpp_daily.txt";
$dailyDateFile = "/home/fpp/media/config/contact_clsr_fpp_daily_date.txt";

// Ensure config directory exists
$configDir = dirname($configFile);
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

// Function to log messages
function log_message($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "$timestamp - $message\n", FILE_APPEND);
}

// Function to get total count
function get_total_count() {
    global $totalCountFile;
    if (file_exists($totalCountFile)) {
        return intval(file_get_contents($totalCountFile));
    }
    return 0;
}

// Function to get daily count
function get_daily_count() {
    global $dailyCountFile, $dailyDateFile;
    // Check if we need to reset daily count
    $today = date('Y-m-d');
    $lastDate = "";
    if (file_exists($dailyDateFile)) {
        $lastDate = trim(file_get_contents($dailyDateFile));
    }
    if ($lastDate != $today) {
        // New day - reset daily count
        file_put_contents($dailyCountFile, "0");
        file_put_contents($dailyDateFile, $today);
        return 0;
    }
    if (file_exists($dailyCountFile)) {
        return intval(file_get_contents($dailyCountFile));
    }
    return 0;
}

// Handle reset counts
if (isset($_POST['reset_daily'])) {
    $pluginDir = "/home/fpp/media/plugins/contact_clsr_fpp";
    $callbackScript = "$pluginDir/callbacks.sh";
    exec("$callbackScript reset_daily 2>&1");
    log_message("Daily count reset requested");
    echo "<script>$.jGrowl('Daily count reset');</script>";
}

if (isset($_POST['reset_total'])) {
    $pluginDir = "/home/fpp/media/plugins/contact_clsr_fpp";
    $callbackScript = "$pluginDir/callbacks.sh";
    exec("$callbackScript reset_total 2>&1");
    log_message("Total count reset requested");
    echo "<script>$.jGrowl('Total count reset');</script>";
}

if (isset($_POST['reset_all'])) {
    $pluginDir = "/home/fpp/media/plugins/contact_clsr_fpp";
    $callbackScript = "$pluginDir/callbacks.sh";
    exec("$callbackScript reset 2>&1");
    log_message("All counts reset requested");
    echo "<script>$.jGrowl('All counts reset');</script>";
}

// Handle form submission
$message = "";
$messageType = "";

if (isset($_POST['save_config'])) {
    $esp32IP = isset($_POST['esp32_ip']) ? trim($_POST['esp32_ip']) : "";
    $esp32Port = isset($_POST['esp32_port']) ? intval($_POST['esp32_port']) : 8080;
    $esp32Port = max(1, min(65535, $esp32Port)); // Clamp between 1-65535
    
    // Validate IP address
    if (!empty($esp32IP) && !filter_var($esp32IP, FILTER_VALIDATE_IP)) {
        $message = "Warning: Invalid IP address format.";
        $messageType = "warning";
    }
    
    // Save configuration
    $config = "[contact_clsr_fpp]\n";
    $config .= "esp32_ip = " . ($esp32IP ? $esp32IP : "") . "\n";
    $config .= "esp32_port = $esp32Port\n";
    
    if (file_put_contents($configFile, $config)) {
        if (empty($message)) {
            $message = "Configuration saved successfully.";
            $messageType = "success";
        }
        log_message("Configuration updated. ESP32 IP: " . ($esp32IP ? $esp32IP : "Not set") . ", Port: $esp32Port");
    } else {
        $message = "Error: Could not save configuration file.";
        $messageType = "error";
    }
}


// Load current configuration
$currentIP = "";
$currentPort = 8080;
if (file_exists($configFile)) {
    $config = parse_ini_file($configFile);
    if (isset($config['esp32_ip']) && !empty($config['esp32_ip'])) {
        $currentIP = $config['esp32_ip'];
    }
    if (isset($config['esp32_port'])) {
        $currentPort = intval($config['esp32_port']);
    }
}

$currentTotalCount = get_total_count();
$currentDailyCount = get_daily_count();
$currentDate = date('Y-m-d');

// Load log content for display
$logContent = "";
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
} else {
    $logContent = "Log file not found.";
}

// Handle log clear
if (isset($_POST['clear_log'])) {
    if (file_exists($logFile)) {
        file_put_contents($logFile, "");
        echo "<script>$.jGrowl('Log cleared');</script>";
        $logContent = "";
    }
}
?>

<div id="esp32_neopixel_config" class="settings">
    <fieldset>
        <legend>ESP32 NeoPixel Contact Closure - Configuration</legend>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="padding: 10px; margin: 10px 0; border: 1px solid #ccc; background-color: #f0f0f0;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <p>Configure the IP address and port for your ESP32 S3 ETH device. The ESP32 monitors the food donation box door and sends events to FPP when the box is opened, which triggers the Neo Trinkey to display a rainbow chase effect.</p>
        
        <form method="post" action="">
            <fieldset>
                <legend>ESP32 Network Configuration</legend>
                <table>
                    <tr>
                        <td><label for="esp32_ip">ESP32 IP Address:</label></td>
                        <td>
                            <input type="text" name="esp32_ip" id="esp32_ip" 
                                   value="<?php echo htmlspecialchars($currentIP); ?>" 
                                   placeholder="192.168.1.100" style="width: 200px;">
                            <br><small>Enter the IP address of your ESP32 S3 ETH device</small>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="esp32_port">ESP32 Port:</label></td>
                        <td>
                            <input type="number" name="esp32_port" id="esp32_port" 
                                   min="1" max="65535" value="<?php echo $currentPort; ?>" 
                                   style="width: 100px;">
                            <br><small>TCP port for communication (default: 8080)</small>
                        </td>
                    </tr>
                </table>
            </fieldset>
            
            <br>
            <input type="submit" name="save_config" value="Save Configuration" class="buttons">
        </form>
    </fieldset>
    
    <fieldset>
        <legend>Food Donation Box Statistics</legend>
        <table style="width: 100%; margin: 10px 0;">
            <tr>
                <td style="padding: 5px;"><b>Today's Opens (<?php echo $currentDate; ?>):</b></td>
                <td style="padding: 5px;"><code style="font-size: 18px;"><?php echo $currentDailyCount; ?></code></td>
            </tr>
            <tr>
                <td style="padding: 5px;"><b>Total Opens (All Time):</b></td>
                <td style="padding: 5px;"><code style="font-size: 18px;"><?php echo $currentTotalCount; ?></code></td>
            </tr>
        </table>
        <form method="post" action="" style="margin: 10px 0;">
            <input type="submit" name="reset_daily" value="Reset Daily Count" class="buttons" 
                   onclick="return confirm('Are you sure you want to reset today\'s count?');" style="margin-right: 5px;">
            <input type="submit" name="reset_total" value="Reset Total Count" class="buttons" 
                   onclick="return confirm('Are you sure you want to reset the total count?');" style="margin-right: 5px;">
            <input type="submit" name="reset_all" value="Reset All" class="buttons" 
                   onclick="return confirm('Are you sure you want to reset both daily and total counts?');">
        </form>
        <p><small><i>Note: Daily count automatically resets at midnight. When the box is opened, the Neo Trinkey displays a rainbow chase effect.</i></small></p>
    </fieldset>
    
    <fieldset>
        <legend onclick="toggleSection('config_section')" style="cursor: pointer;">
            Current Configuration <span id="config_toggle">▼</span>
        </legend>
        <div id="config_section" style="display: none;">
            <p><b>Config File:</b> <code><?php echo $configFile; ?></code></p>
            <p><b>Current ESP32 IP:</b> 
                <code><?php echo empty($currentIP) ? 'Not configured' : htmlspecialchars($currentIP); ?></code>
            </p>
            <p><b>Current Port:</b> 
                <code><?php echo $currentPort; ?></code>
            </p>
            <?php if (file_exists($configFile)): ?>
                <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars(file_get_contents($configFile)); ?></pre>
            <?php else: ?>
                <p><i>No configuration file found.</i></p>
            <?php endif; ?>
        </div>
    </fieldset>
    
    <fieldset>
        <legend onclick="toggleSection('log_section')" style="cursor: pointer;">
            Log Viewer <span id="log_toggle">▼</span>
        </legend>
        <div id="log_section" style="display: none;">
            <p><b>Log File:</b> <code><?php echo $logFile; ?></code></p>
            <form method="post" action="">
                <textarea style="width: 100%; height: 400px; font-family: monospace; font-size: 12px;" readonly><?php echo htmlspecialchars($logContent); ?></textarea>
                <br><br>
                <input type="submit" name="clear_log" value="Clear Log" class="buttons">
                <input type="button" value="Refresh" onclick="location.reload();" class="buttons">
            </form>
        </div>
    </fieldset>
</div>

<script>
// Toggle collapsible sections
function toggleSection(sectionId) {
    var section = document.getElementById(sectionId);
    var toggle = document.getElementById(sectionId.replace('_section', '_toggle'));
    if (section.style.display === 'none') {
        section.style.display = 'block';
        toggle.textContent = '▲';
    } else {
        section.style.display = 'none';
        toggle.textContent = '▼';
    }
}
</script>

