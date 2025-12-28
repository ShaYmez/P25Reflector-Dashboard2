<?php
/**
 * P25Reflector-Dashboard2 - Core Functions
 * Functions for reading and parsing P25Reflector logs
 * Copyright (C) 2025  Shane Daley, M0VUB Aka. ShaYmez
 */

// Constants for log parsing
define('MAX_CALLSIGN_LENGTH', 11);

function getP25ReflectorVersion() {
    $filename = P25REFLECTORPATH."/P25Reflector";
    // Validate that the path exists and is executable
    if (!file_exists($filename) || !is_executable($filename)) {
        return getP25ReflectorFileVersion();
    }
    exec(escapeshellcmd($filename)." -v 2>&1", $output);
    if (isset($output[0]) && !startsWith(substr($output[0],21,8),"20")) {
        return getP25ReflectorFileVersion();
    } else {
        return isset($output[0]) ? substr($output[0],21,8)." (compiled ".getP25ReflectorFileVersion().")" : "Unknown";
    }
}

function getP25ReflectorFileVersion() {
    $filename = P25REFLECTORPATH."/P25Reflector";
    if (file_exists($filename)) {
        return date("d M Y", filectime($filename));
    }
    return "Unknown";
}

function getGitVersion(){
    if (file_exists(".git")) {
        exec("git rev-parse --short HEAD", $output);
        if (isset($output[0]) && !empty($output[0])) {
            $commitHash = htmlspecialchars($output[0], ENT_QUOTES, 'UTF-8');
            return 'GitID #<a href="https://github.com/ShaYmez/P25Reflector-Dashboard2/commit/'.$commitHash.'" target="_blank">'.$commitHash.'</a>';
        }
        return 'GitID unknown';
    } else {
        return 'GitID unknown';
    }
}

function getP25ReflectorConfig() {
    $conf = array();
    $configPath = P25REFLECTORINIPATH."/".P25REFLECTORINIFILENAME;
    
    // Check if file exists and is readable
    if (!file_exists($configPath)) {
        error_log("P25Reflector config file not found: " . $configPath);
        return $conf;
    }
    
    if (!is_readable($configPath)) {
        error_log("P25Reflector config file not readable: " . $configPath);
        return $conf;
    }
    
    if ($configs = fopen($configPath, 'r')) {
        while ($config = fgets($configs)) {
            array_push($conf, trim ( $config, " \t\n\r\0\x0B"));
        }
        fclose($configs);
    }
    return $conf;
}

function getConfigItem($section, $key, $configs) {
    if (empty($configs) || !is_array($configs)) {
        return '';
    }
    
    // Validate and sanitize section and key parameters
    // Only allow alphanumeric characters, underscores, and hyphens
    if (!is_string($section) || !is_string($key)) {
        return '';
    }
    
    // Remove any characters that could be used for injection
    $section = preg_replace('/[^a-zA-Z0-9_-]/', '', $section);
    $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
    
    if (empty($section) || empty($key)) {
        return '';
    }
    
    $sectionIndex = array_search("[" . $section . "]", $configs);
    if ($sectionIndex === false) {
        return '';
    }
    
    $sectionpos = $sectionIndex + 1;
    $len = count($configs);
    
    while($sectionpos < $len) {
        if (startsWith($configs[$sectionpos],"[")) {
            return '';
        }
        if (startsWith($configs[$sectionpos], $key."=")) {
            return substr($configs[$sectionpos], strlen($key) + 1);
        }
        $sectionpos++;
    }
    return '';
}

function getP25ReflectorLog() {
    $logPath = P25REFLECTORLOGPATH."/".P25REFLECTORLOGPREFIX."-".date("Y-m-d").".log";
    $logLines = array();
    if (file_exists($logPath) && is_readable($logPath)) {
        if ($log = fopen($logPath, 'r')) {
            while ($logLine = fgets($log)) {
                if (startsWith($logLine, "M:")) {
                    array_push($logLines, $logLine);
                }
            }
            fclose($log);
        }
    }
    return $logLines;
}

function getLastHeard($logLines) {
    //returns last heard list from log
    $lastHeard = array();
    $heardCalls = array();
    $heardList = getHeardList($logLines);
    foreach ($heardList as $listElem) {
        if(array_search($listElem[1], $heardCalls) === false) {
            array_push($heardCalls, $listElem[1]);
            array_push($lastHeard, $listElem);
        }
    }
    return $lastHeard;
}

function isNoiseLogLine($logLine) {
    return (strpos($logLine,"Data from") !== false || strpos($logLine,"Received command") !== false || 
            strpos($logLine,"blocked") !== false || strpos($logLine,"Reload the Blacklist from File") !== false || 
            strpos($logLine,"server status enquiry from") !== false || 
            strpos($logLine,"Data received from an unknown source") !== false);
}

function isTransmissionEndLine($logLine) {
    return (strpos($logLine,"end of transmission") !== false || strpos($logLine,"watchdog has expired") !== false);
}

function getHeardList($logLines) {
    // P25 log format: "M: 2025-01-01 12:34:56.789 Transmission from CALLSIGN at GATEWAY to TG 12345"
    // or: "M: 2025-01-01 12:34:56.789 Transmission from CALLSIGN at GATEWAY to 12345"
    $heardList = array();
    $dttxend = "";
    
    foreach ($logLines as $logLine) {
        // Filter out noise lines
        if (isNoiseLogLine($logLine)) {
            continue;
        }
        
        // Check for transmission data: "Transmission from X at Y to TG Z" or "Transmission from X at Y to Z"
        if (strpos($logLine, "Transmission from") !== false && 
            strpos($logLine, " at ") !== false && 
            strpos($logLine, " to ") !== false) {
            
            $duration = "transmitting";
            $timestamp = substr($logLine, 3, 19);
            $dttimestamp = new DateTime($timestamp);
            if ($dttxend !== "") {
                $duration = $dttimestamp->diff($dttxend)->format("%s");
            }
            
            // Extract: "Transmission from CALLSIGN at GATEWAY to TG 12345"
            // Pattern: after "from " to " at "
            $fromPos = strpos($logLine, "Transmission from ");
            if ($fromPos !== false) {
                $afterFrom = substr($logLine, $fromPos + 18); // After "Transmission from "
                $atPos = strpos($afterFrom, " at ");
                $toPos = strpos($afterFrom, " to ");
                
                if ($atPos !== false && $toPos !== false && $toPos > $atPos) {
                    // Extract callsign (from start to " at ")
                    $callsign = trim(substr($afterFrom, 0, $atPos));
                    
                    // Extract gateway (from " at " to " to ")
                    $gateway = trim(substr($afterFrom, $atPos + 4, $toPos - $atPos - 4));
                    
                    // Extract target (after " to ")
                    $target = trim(substr($afterFrom, $toPos + 4));
                    
                    // Callsign should be reasonable length
                    if (strlen($callsign) < MAX_CALLSIGN_LENGTH && strlen($callsign) > 0) {
                        array_push($heardList, array(convertTimezone($timestamp), $callsign, $target, $gateway, $duration));
                    }
                }
            }
        }
        
        // Track transmission end times for duration calculation
        if(isTransmissionEndLine($logLine)) {
            $txend = substr($logLine, 3, 19);
            $dttxend = new DateTime($txend);
        }
    }
    return $heardList;
}

function getCurrentlyTXing($logLines) {
    // Get the most recent transmission from the log
    // A station is considered "TXing" if:
    // 1. They have transmitted within the last 180 seconds
    // 2. There is no transmission end marker after their most recent transmission data
    $txTimeout = 180; // seconds
    $mostRecentTX = null;
    
    // Find the most recent transmission data line to determine current transmitter
    $currentCallsign = null;
    $currentTarget = null;
    $currentGateway = null;
    $mostRecentTXIndex = -1;
    $mostRecentTXTime = null;
    
    for ($i = count($logLines) - 1; $i >= 0; $i--) {
        $logLine = $logLines[$i];
        
        // Filter out noise log lines
        if (isNoiseLogLine($logLine)) {
            continue;
        }
        
        // Skip transmission end markers in this pass
        if (isTransmissionEndLine($logLine)) {
            continue;
        }
        
        // Check for transmission data
        if (strpos($logLine, "Transmission from") !== false && 
            strpos($logLine, " at ") !== false && 
            strpos($logLine, " to ") !== false) {
            
            $timestamp = substr($logLine, 3, 19);
            
            // Extract callsign
            $fromPos = strpos($logLine, "Transmission from ");
            if ($fromPos !== false) {
                $afterFrom = substr($logLine, $fromPos + 18);
                $atPos = strpos($afterFrom, " at ");
                $toPos = strpos($afterFrom, " to ");
                
                if ($atPos !== false && $toPos !== false && $toPos > $atPos) {
                    $callsign = trim(substr($afterFrom, 0, $atPos));
                    
                    // Only process if callsign is valid length
                    if (strlen($callsign) < MAX_CALLSIGN_LENGTH && strlen($callsign) > 0) {
                        // Parse timestamp and check if it's recent
                        $txTime = new DateTime($timestamp, new DateTimeZone('UTC'));
                        $now = new DateTime('now', new DateTimeZone('UTC'));
                        $diff = $now->getTimestamp() - $txTime->getTimestamp();
                        
                        // Check if transmission is within timeout window
                        if ($diff >= 0 && $diff <= $txTimeout) {
                            // Found most recent transmission data - record details
                            $mostRecentTXIndex = $i;
                            $mostRecentTXTime = $txTime;
                            $currentCallsign = $callsign;
                            
                            // Extract gateway
                            $currentGateway = trim(substr($afterFrom, $atPos + 4, $toPos - $atPos - 4));
                            
                            // Extract target
                            $currentTarget = trim(substr($afterFrom, $toPos + 4));
                        }
                        break; // Found the most recent transmission data
                    }
                }
            }
        }
    }
    
    // If no recent transmission found, return null
    if ($currentCallsign === null) {
        return null;
    }
    
    // Now check if there's a transmission end marker after the most recent TX data
    // by looking forward from the most recent TX index
    $hasEndedAfterMostRecentData = false;
    for ($i = $mostRecentTXIndex + 1; $i < count($logLines); $i++) {
        $logLine = $logLines[$i];
        if (isTransmissionEndLine($logLine)) {
            $hasEndedAfterMostRecentData = true;
            break;
        }
    }
    
    // If there's an end marker after the most recent data, transmission has ended
    if ($hasEndedAfterMostRecentData) {
        return null;
    }
    
    // Now find when this callsign started transmitting to calculate accurate duration
    // Go backwards from the most recent TX to find the first TX data from this callsign
    $transmissionStartTime = $mostRecentTXTime;
    
    for ($i = $mostRecentTXIndex - 1; $i >= 0; $i--) {
        $logLine = $logLines[$i];
        
        // If we hit an end marker, the transmission started after this
        if (isTransmissionEndLine($logLine)) {
            break;
        }
        
        // Skip noise
        if (isNoiseLogLine($logLine)) {
            continue;
        }
        
        // Check if this is a TX data line from the same callsign
        if (strpos($logLine, "Transmission from") !== false && 
            strpos($logLine, " at ") !== false && 
            strpos($logLine, " to ") !== false) {
            
            $fromPos = strpos($logLine, "Transmission from ");
            if ($fromPos !== false) {
                $afterFrom = substr($logLine, $fromPos + 18);
                $atPos = strpos($afterFrom, " at ");
                
                if ($atPos !== false) {
                    $callsign = trim(substr($afterFrom, 0, $atPos));
                    
                    if ($callsign === $currentCallsign) {
                        // Same callsign - this is part of the same transmission
                        $timestamp = substr($logLine, 3, 19);
                        $transmissionStartTime = new DateTime($timestamp, new DateTimeZone('UTC'));
                    } else {
                        // Different callsign - the current transmission started after this
                        break;
                    }
                }
            }
        }
    }
    
    // Calculate duration from the start of the transmission
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $duration = $now->getTimestamp() - $transmissionStartTime->getTimestamp();
    
    // Return the active transmission info with accurate duration
    $mostRecentTX = array(
        'timestamp' => convertTimezone($transmissionStartTime->format('Y-m-d H:i:s')),
        'source' => $currentCallsign,
        'target' => $currentTarget,
        'gateway' => $currentGateway,
        'duration' => $duration
    );
    
    return $mostRecentTX;
}

function getLinkedGateways($logLines) {
    // Parse log format:
    // M: 2016-06-24 11:11:41.787 Currently linked repeaters:
    // M: 2016-06-24 11:11:41.787     CALLSIGN: 217.82.212.214:42000 2/60
    
    $gateways = Array();
    for ($i = count($logLines) - 1; $i >= 0; $i--) {
        $logLine = $logLines[$i];
        
        if (strpos($logLine, "Starting P25Reflector")) {
            return $gateways;
        }
        if (strpos($logLine, "No repeaters linked")) {
            return $gateways;
        }
        if (strpos($logLine, "Currently linked repeaters")) {
            for ($j = $i+1; $j < count($logLines); $j++) {
                $logLine = $logLines[$j];
                if (!startsWith(substr($logLine,27), "   ")) {
                    return $gateways;
                } else {
                    $timestamp = substr($logLine, 3, 19);
                    $callsign = substr($logLine, 31, 10);
                    $ipport = substr($logLine,31);
                    $key = searchForKey("ipport",$ipport, $gateways);
                    if ($key === NULL) {
                        array_push($gateways, Array('callsign'=>$callsign,'timestamp'=>$timestamp,'ipport'=>$ipport));
                    }
                }
            }
        }
    }
    return $gateways;
}

function getSystemInfo() {
    $uptimeData = file_get_contents('/proc/uptime');
    $uptime = explode(" ", $uptimeData);
    
    $load = sys_getloadavg();
    
    $temperature = 0;
    if (file_exists('/sys/class/thermal/thermal_zone0/temp')) {
        $temperature = round(file_get_contents('/sys/class/thermal/thermal_zone0/temp') / 1000, 1);
    }
    
    return array(
        'uptime' => format_time($uptime[0]),
        'load' => $load,
        'temperature' => $temperature
    );
}

function getDiskInfo() {
    $total = disk_total_space("/");
    $free = disk_free_space("/");
    $used = $total - $free;
    $percentUsed = round(($used / $total) * 100, 1);
    
    return array(
        'total' => round($total / 1024 / 1024 / 1024, 2),
        'used' => round($used / 1024 / 1024 / 1024, 2),
        'free' => round($free / 1024 / 1024 / 1024, 2),
        'percent' => $percentUsed
    );
}

/**
 * Get supported logo file formats
 * @return array List of supported file extensions
 */
function getSupportedLogoFormats() {
    return ['png', 'jpg', 'jpeg', 'bmp', 'webp', 'gif', 'svg'];
}

/**
 * Get display string of supported logo formats for UI
 * @return string Formatted string like "PNG, JPEG, BMP, WEBP, GIF, SVG"
 */
function getLogoFormatsDisplay() {
    $formats = getSupportedLogoFormats();
    $displayFormats = array_map(function($ext) {
        // Display 'jpg' as 'JPEG' for clarity
        return ($ext === 'jpg') ? 'JPEG' : strtoupper($ext);
    }, $formats);
    // Remove duplicates (jpg and jpeg both shown as JPEG)
    return implode(', ', array_unique($displayFormats));
}

/**
 * Get logo path - checks for local logo files first, then falls back to LOGO constant
 * Supports jpg, jpeg, png, bmp, webp, gif, svg formats
 * @return string|false Logo path/URL or false if no logo configured
 */
function getLogoPath() {
    // Check if LOGO constant is defined and is a URL (starts with http:// or https://)
    if (defined("LOGO") && !empty(LOGO)) {
        // If it's a URL, return it directly
        if (preg_match('/^https?:\/\//i', LOGO)) {
            return LOGO;
        }
        // If it's a relative path and the file exists, return it
        if (file_exists(LOGO)) {
            return LOGO;
        }
    }
    
    // Check for local logo files in img/ directory (case-insensitive)
    $supportedFormats = getSupportedLogoFormats();
    
    // Scan the img directory for logo files
    if (is_dir('img') && is_readable('img')) {
        $files = scandir('img');
        // Check if scandir failed or returned false
        if ($files === false) {
            return false;
        }
        
        foreach ($files as $file) {
            // Skip directory entries
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            // Check if filename (without extension) is "logo" (case-insensitive)
            $pathInfo = pathinfo($file);
            if (isset($pathInfo['extension']) && isset($pathInfo['filename'])) {
                $filenameLower = strtolower($pathInfo['filename']);
                $extensionLower = strtolower($pathInfo['extension']);
                
                if ($filenameLower === 'logo' && in_array($extensionLower, $supportedFormats)) {
                    return 'img/' . $file;
                }
            }
        }
    }
    
    // No logo found
    return false;
}
?>
