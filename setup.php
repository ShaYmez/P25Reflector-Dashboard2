<?php
/**
 * P25Reflector-Dashboard2 - Setup Page
 * Initial configuration for P25Reflector Dashboard
 * Copyright (C) 2025  Shane Daley, M0VUB Aka. ShaYmez
 * REMOVE THIS FILE AFTER SETUP IS COMPLETE!
 */

// Default configuration values
if (!defined("P25REFLECTORLOGPATH")) define("P25REFLECTORLOGPATH", "/var/log/P25Reflector/");
if (!defined("P25REFLECTORLOGPREFIX")) define("P25REFLECTORLOGPREFIX", "P25Reflector");
if (!defined("P25REFLECTORINIPATH")) define("P25REFLECTORINIPATH", "/etc/");
if (!defined("P25REFLECTORINIFILENAME")) define("P25REFLECTORINIFILENAME", "P25Reflector.ini");
if (!defined("P25REFLECTORPATH")) define("P25REFLECTORPATH", "/usr/local/bin/");
if (!defined("TIMEZONE")) define("TIMEZONE", "UTC");
if (!defined("LOGO")) define("LOGO", "");
if (!defined("REFRESHAFTER")) define("REFRESHAFTER", "15");
if (!defined("SHOWPROGRESSBARS")) define("SHOWPROGRESSBARS", "");
if (!defined("SHOWOLDMHEARD")) define("SHOWOLDMHEARD", "7");
if (!defined("TEMPERATUREALERT")) define("TEMPERATUREALERT", "");
if (!defined("TEMPERATUREHIGHLEVEL")) define("TEMPERATUREHIGHLEVEL", "60");
if (!defined("SHOWQRZ")) define("SHOWQRZ", "");
if (!defined("GDPR")) define("GDPR", "");
if (!defined("DASHBOARD_NAME")) define("DASHBOARD_NAME", "P25 Reflector Dashboard");
if (!defined("DASHBOARD_TAGLINE")) define("DASHBOARD_TAGLINE", "Modern Dashboard for Amateur Radio");

include "include/tools.php";
include "include/functions.php"; // Include functions for getSupportedLogoFormats()

function createConfigLines() { 
    $out ="";
    // Whitelist of allowed configuration keys with validation
    $allowedKeys = [
        "DASHBOARD_NAME" => "string",
        "DASHBOARD_TAGLINE" => "string",
        "LOGO" => "string", // Changed from FILTER_VALIDATE_URL to support local paths
        "P25REFLECTORLOGPATH" => "string",
        "P25REFLECTORLOGPREFIX" => "string",
        "P25REFLECTORINIPATH" => "string",
        "P25REFLECTORINIFILENAME" => "string",
        "P25REFLECTORPATH" => "string",
        "TIMEZONE" => "string",
        "REFRESHAFTER" => FILTER_VALIDATE_INT,
        "SHOWOLDMHEARD" => FILTER_VALIDATE_INT,
        "TEMPERATUREHIGHLEVEL" => FILTER_VALIDATE_INT,
        "SHOWPROGRESSBARS" => "string",
        "TEMPERATUREALERT" => "string",
        "SHOWQRZ" => "string",
        "GDPR" => "string"
    ];
    
    foreach($_GET as $key=>$val) { 
        if($key != "cmd" && isset($allowedKeys[$key])) {
            // Validate based on type
            if ($allowedKeys[$key] === FILTER_VALIDATE_INT) {
                $sanitizedVal = filter_var($val, FILTER_VALIDATE_INT);
                if ($sanitizedVal === false) {
                    continue; // Skip invalid integers
                }
            } else {
                // Sanitize string input - using htmlspecialchars for PHP 7.4+ compatibility
                // (replaces deprecated FILTER_SANITIZE_STRING which was removed in PHP 8.1)
                $sanitizedVal = htmlspecialchars($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            
            // Handle checkboxes
            if ($val === "on") {
                $out .= "define(\"".addslashes($key)."\", true);"."\n";
            } else {
                $out .= "define(\"".addslashes($key)."\", \"".addslashes($sanitizedVal)."\");"."\n";
            }
        }
    }
    return $out;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P25Reflector-Dashboard2 - Setup</title>
    <link rel="stylesheet" href="assets/css/output.css">
    <style>
        .setup-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="setup-bg">
    <?php
    if (isset($_GET['cmd']) && $_GET['cmd'] == "writeconfig") {
        if (!file_exists('./config')) {
            if (!mkdir('./config', 0755, true)) {
    ?>
        <div class="container mx-auto px-4 py-12">
            <div class="max-w-2xl mx-auto">
                <div class="bg-red-500/20 border border-red-500 rounded-xl p-6 mb-6">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 mr-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-lg">You forgot to give write permissions to your webserver user!</span>
                    </div>
                </div>
            </div>
        </div>
    <?php
            }
        }
        $configfile = fopen("config/config.php", 'w');
        fwrite($configfile,"<?php\n");
        fwrite($configfile,"/**\n");
        fwrite($configfile," * P25Reflector-Dashboard2 Configuration\n");
        fwrite($configfile," * This is an auto-generated config file!\n");
        fwrite($configfile," * Be careful when manually editing this!\n");
        fwrite($configfile," */\n\n");
        fwrite($configfile,"date_default_timezone_set('UTC');\n\n");
        fwrite($configfile, createConfigLines());
        fwrite($configfile,"?>\n");
        fclose($configfile);
    ?>
        <div class="container mx-auto px-4 py-12">
            <div class="max-w-2xl mx-auto">
                <div class="card-glossy p-8 text-center">
                    <div class="mb-6">
                        <svg class="w-20 h-20 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold mb-4">Setup Complete!</h1>
                    <p class="text-lg mb-6 text-white/80">Your configuration file has been created successfully at config/config.php</p>
                    <div class="bg-yellow-500/20 border border-yellow-500 rounded-xl p-4 mb-6">
                        <p class="font-semibold">⚠️ Important Security Notice</p>
                        <p class="text-sm mt-2">Please remove setup.php from your web directory for security reasons!</p>
                    </div>
                    <a href="index.php" class="btn-primary inline-block">
                        Go to Dashboard →
                    </a>
                </div>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="container mx-auto px-4 py-12">
            <div class="max-w-5xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-5xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-blue-200 to-purple-200">
                        P25Reflector-Dashboard2
                    </h1>
                    <p class="text-xl text-white/80">Initial Setup & Configuration</p>
                </div>

                <form id="config" action="setup.php" method="get" class="space-y-8">
                    <input type="hidden" name="cmd" value="writeconfig">

                    <!-- Branding Section -->
                    <div class="card-glossy p-8">
                        <h2 class="text-3xl font-bold mb-6 flex items-center">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                            </svg>
                            Dashboard Branding
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2">Dashboard Name</label>
                                <input type="text" name="DASHBOARD_NAME" value="<?php echo constant("DASHBOARD_NAME") ?>" 
                                       class="input-glossy w-full" placeholder="P25 Reflector Dashboard" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Dashboard Tagline</label>
                                <input type="text" name="DASHBOARD_TAGLINE" value="<?php echo constant("DASHBOARD_TAGLINE") ?>" 
                                       class="input-glossy w-full" placeholder="Modern Dashboard for Amateur Radio">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold mb-2">Logo URL or Local Path (optional)</label>
                                <input type="text" name="LOGO" value="<?php echo constant("LOGO") ?>" 
                                       class="input-glossy w-full" placeholder="https://example.com/logo.png or img/logo.png">
                                <p class="text-sm text-white/60 mt-2">
                                    <strong>Option 1:</strong> Enter a full URL (e.g., https://example.com/logo.png)<br>
                                    <strong>Option 2:</strong> Place your logo file in the 
                                    <code class="bg-white/10 px-2 py-1 rounded">img/</code> directory 
                                    with filename 
                                    <code class="bg-white/10 px-2 py-1 rounded">logo.png</code>, 
                                    <code class="bg-white/10 px-2 py-1 rounded">logo.jpg</code>, 
                                    or other supported formats. 
                                    The dashboard will automatically detect it (case-insensitive).<br>
                                    <strong>Supported formats:</strong> 
                                    <?php echo htmlspecialchars(getLogoFormatsDisplay(), ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- P25Reflector Configuration -->
                    <div class="card-glossy p-8">
                        <h2 class="text-3xl font-bold mb-6 flex items-center">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            P25Reflector Configuration
                        </h2>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2">Path to P25Reflector Log Files</label>
                                <input type="text" name="P25REFLECTORLOGPATH" value="<?php echo constant("P25REFLECTORLOGPATH") ?>" 
                                       class="input-glossy w-full" placeholder="/var/log/P25Reflector/" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Log File Prefix</label>
                                <input type="text" name="P25REFLECTORLOGPREFIX" value="<?php echo constant("P25REFLECTORLOGPREFIX") ?>" 
                                       class="input-glossy w-full" placeholder="P25Reflector" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Path to P25Reflector.ini</label>
                                <input type="text" name="P25REFLECTORINIPATH" value="<?php echo constant("P25REFLECTORINIPATH") ?>" 
                                       class="input-glossy w-full" placeholder="/etc/" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">P25Reflector.ini Filename</label>
                                <input type="text" name="P25REFLECTORINIFILENAME" value="<?php echo constant("P25REFLECTORINIFILENAME") ?>" 
                                       class="input-glossy w-full" placeholder="P25Reflector.ini" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Path to P25Reflector Executable</label>
                                <input type="text" name="P25REFLECTORPATH" value="<?php echo constant("P25REFLECTORPATH") ?>" 
                                       class="input-glossy w-full" placeholder="/usr/local/bin/" required>
                            </div>
                        </div>
                    </div>

                    <!-- Global Settings -->
                    <div class="card-glossy p-8">
                        <h2 class="text-3xl font-bold mb-6 flex items-center">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            Global Settings
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2">Timezone</label>
                                <select name="TIMEZONE" class="input-glossy w-full">
                                    <?php
                                    $timezones = timezone_identifiers_list();
                                    $current_tz = constant("TIMEZONE");
                                    foreach($timezones as $tz) {
                                        $selected = ($tz === $current_tz) ? 'selected' : '';
                                        echo "<option value=\"".htmlspecialchars($tz, ENT_QUOTES, 'UTF-8')."\" $selected>".htmlspecialchars($tz, ENT_QUOTES, 'UTF-8')."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Refresh Interval (seconds)</label>
                                <input type="number" name="REFRESHAFTER" value="<?php echo constant("REFRESHAFTER") ?>" 
                                       class="input-glossy w-full" placeholder="15" required>
                                <p class="text-xs text-white/60 mt-1">Default: 15 seconds. Lower values provide more responsive updates.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Historic Logs (days)</label>
                                <input type="number" name="SHOWOLDMHEARD" value="<?php echo constant("SHOWOLDMHEARD") ?>" 
                                       class="input-glossy w-full" placeholder="7" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Temperature Warning Level (°C)</label>
                                <input type="number" name="TEMPERATUREHIGHLEVEL" value="<?php echo constant("TEMPERATUREHIGHLEVEL") ?>" 
                                       class="input-glossy w-full" placeholder="60" required>
                            </div>
                        </div>

                        <!-- Checkbox Options -->
                        <div class="mt-8 space-y-4">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="SHOWPROGRESSBARS" class="w-5 h-5 rounded border-white/20 bg-white/5 text-blue-600 focus:ring-2 focus:ring-blue-500" <?php if (defined("SHOWPROGRESSBARS") && constant("SHOWPROGRESSBARS")) echo "checked" ?>>
                                <span class="text-sm font-medium">Show Progress Bars</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="TEMPERATUREALERT" class="w-5 h-5 rounded border-white/20 bg-white/5 text-blue-600 focus:ring-2 focus:ring-blue-500" <?php if (defined("TEMPERATUREALERT") && constant("TEMPERATUREALERT")) echo "checked" ?>>
                                <span class="text-sm font-medium">Enable CPU Temperature Warnings</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="SHOWQRZ" class="w-5 h-5 rounded border-white/20 bg-white/5 text-blue-600 focus:ring-2 focus:ring-blue-500" <?php if (defined("SHOWQRZ") && constant("SHOWQRZ")) echo "checked" ?>>
                                <span class="text-sm font-medium">Show QRZ.com Links on Callsigns</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="GDPR" class="w-5 h-5 rounded border-white/20 bg-white/5 text-blue-600 focus:ring-2 focus:ring-blue-500" <?php if (defined("GDPR") && constant("GDPR")) echo "checked" ?>>
                                <span class="text-sm font-medium">Anonymize Callsigns (GDPR Compliance)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn-primary text-lg px-12 py-4">
                            <span class="flex items-center justify-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Configuration
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Footer -->
                <div class="text-center mt-12 text-white/60 text-sm">
                    <p>P25Reflector-Dashboard2 by ShaYmez</p>
                    <p class="mt-2">Compatible with P25Reflector</p>
                </div>
            </div>
        </div>
    <?php
    }
    ?>
</body>
</html>
