<?php
/**
 * P25Reflector-Dashboard2 - TX Status API
 * Lightweight endpoint for checking current transmission status
 * Copyright (C) 2025  Shane Daley, M0VUB Aka. ShaYmez
 */

// Load configuration and includes
if (!file_exists("../config/config.php")) {
    http_response_code(503);
    echo json_encode(['error' => 'Configuration not found']);
    exit();
}

include "../config/config.php";
include "../include/tools.php";
include "../include/functions.php";

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Get current log lines
$logLines = getP25ReflectorLog();

// Get current TX status
$currentlyTXing = getCurrentlyTXing($logLines);

// Return status
if ($currentlyTXing !== null) {
    // Apply GDPR and QRZ settings
    $response = [
        'is_transmitting' => true,
        'timestamp' => $currentlyTXing['timestamp'],
        'source' => $currentlyTXing['source'],
        'target' => htmlspecialchars($currentlyTXing['target'], ENT_QUOTES, 'UTF-8'),
        'gateway' => $currentlyTXing['gateway'],
        'duration' => $currentlyTXing['duration']
    ];
    
    // Handle GDPR anonymization for source
    if (defined("GDPR") && GDPR) {
        $response['source_display'] = htmlspecialchars(str_replace("0","Ø",substr($currentlyTXing['source'],0,3)."***"), ENT_QUOTES, 'UTF-8');
    } else {
        $response['source_display'] = htmlspecialchars(str_replace("0","Ø",$currentlyTXing['source']), ENT_QUOTES, 'UTF-8');
    }
    
    // Handle QRZ link
    if (defined("SHOWQRZ") && SHOWQRZ && $currentlyTXing['source'] !== "??????????" && !is_numeric($currentlyTXing['source'])) {
        $response['qrz_link'] = 'https://qrz.com/db/'.htmlspecialchars($currentlyTXing['source'], ENT_QUOTES, 'UTF-8');
    }
    
    // Handle GDPR anonymization for gateway
    if (defined("GDPR") && GDPR) {
        $response['gateway_display'] = htmlspecialchars(str_replace("0","Ø",substr($currentlyTXing['gateway'],0,3)."***"), ENT_QUOTES, 'UTF-8');
    } else {
        $response['gateway_display'] = htmlspecialchars(str_replace("0","Ø",$currentlyTXing['gateway']), ENT_QUOTES, 'UTF-8');
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'is_transmitting' => false
    ]);
}
?>
