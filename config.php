<?php
// FPP ESP32 NeoPixel Contact Closure Plugin - Configuration

$configFile = "/home/fpp/media/config/esp32_neopixel_contact.conf";
$logFile = "/home/fpp/media/logs/esp32_neopixel_contact.log";
$contactCountFile = "/home/fpp/media/config/esp32_neopixel_contact_count.txt";

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

// Function to get contact count
function get_contact_count() {
    global $contactCountFile;
    if (file_exists($contactCountFile)) {
        return intval(file_get_contents($contactCountFile));
    }
    return 0;
}

// Function to set contact count
function set_contact_count($count) {
    global $contactCountFile;
    file_put_contents($contactCountFile, strval($count));
}

// Handle reset contact count
if (isset($_POST['reset_count'])) {
    set_contact_count(0);
    log_message("Contact count reset to 0");
    echo "<script>$.jGrowl('Contact count reset');</script>";
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
    $config = "[esp32_neopixel_contact]\n";
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

$currentContactCount = get_contact_count();

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
        
        <p>Configure the IP address and port for your ESP32 S3 ETH device. The ESP32 will send contact closure events to FPP, which will trigger the Neo Trinkey to display a rainbow chase effect.</p>
        
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
        <legend>Contact Closure Statistics</legend>
        <p><b>Total Contact Closures:</b> <code><?php echo $currentContactCount; ?></code></p>
        <form method="post" action="" style="margin: 10px 0;">
            <input type="submit" name="reset_count" value="Reset Count" class="buttons" 
                   onclick="return confirm('Are you sure you want to reset the contact closure count?');">
        </form>
        <p><small><i>Note: When a contact closure is detected, the Neo Trinkey will display a rainbow chase effect.</i></small></p>
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

