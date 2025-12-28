<?php
/**
 * P25Reflector-Dashboard2 - Full Dashboard Data API
 * Returns all dashboard data for dynamic updates
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

// Initialize data
$configs = getP25ReflectorConfig();
if (!defined("TIMEZONE")) {
    define("TIMEZONE", "UTC");
}

$logLines = getP25ReflectorLog();
$reverseLogLines = $logLines;
array_multisort($reverseLogLines, SORT_DESC);
$lastHeard = getLastHeard($reverseLogLines);
$gateways = getLinkedGateways($logLines);
$currentlyTXing = getCurrentlyTXing($logLines);

// Build response
$response = [
    'success' => true,
    'timestamp' => time(),
    'tx_status' => null,
    'last_heard' => [],
    'gateways' => [],
    'gateway_count' => count($gateways)
];

// TX Status
if ($currentlyTXing !== null) {
    $response['tx_status'] = [
        'is_transmitting' => true,
        'timestamp' => $currentlyTXing['timestamp'],
        'source' => $currentlyTXing['source'],
        'target' => htmlspecialchars($currentlyTXing['target'], ENT_QUOTES, 'UTF-8'),
        'gateway' => $currentlyTXing['gateway'],
        'duration' => $currentlyTXing['duration']
    ];
    
    // Handle GDPR anonymization for source
    if (defined("GDPR") && GDPR) {
        $response['tx_status']['source_display'] = htmlspecialchars(str_replace("0","Ø",substr($currentlyTXing['source'],0,3)."***"), ENT_QUOTES, 'UTF-8');
    } else {
        $response['tx_status']['source_display'] = htmlspecialchars(str_replace("0","Ø",$currentlyTXing['source']), ENT_QUOTES, 'UTF-8');
    }
    
    // Handle QRZ link
    if (defined("SHOWQRZ") && SHOWQRZ && $currentlyTXing['source'] !== "??????????" && !is_numeric($currentlyTXing['source'])) {
        $response['tx_status']['qrz_link'] = 'https://qrz.com/db/'.htmlspecialchars($currentlyTXing['source'], ENT_QUOTES, 'UTF-8');
    }
    
    // Handle GDPR anonymization for gateway
    if (defined("GDPR") && GDPR) {
        $response['tx_status']['gateway_display'] = htmlspecialchars(str_replace("0","Ø",substr($currentlyTXing['gateway'],0,3)."***"), ENT_QUOTES, 'UTF-8');
    } else {
        $response['tx_status']['gateway_display'] = htmlspecialchars(str_replace("0","Ø",$currentlyTXing['gateway']), ENT_QUOTES, 'UTF-8');
    }
}

// Last Heard List (limit to 20 for performance)
$lastHeardLimit = min(20, count($lastHeard));
for ($i = 0; $i < $lastHeardLimit; $i++) {
    $heard = $lastHeard[$i];
    $heardItem = [
        'time' => $heard[0],
        'callsign' => $heard[1],
        'target' => htmlspecialchars($heard[2], ENT_QUOTES, 'UTF-8'),
        'gateway' => $heard[3]
    ];
    
    // Handle GDPR and QRZ for callsign
    if (defined("SHOWQRZ") && SHOWQRZ && $heard[1] !== "??????????" && !is_numeric($heard[1])) {
        $heardItem['qrz_link'] = 'https://qrz.com/db/'.$heard[1];
        $heardItem['callsign_display'] = str_replace("0","Ø",$heard[1]);
    } else if (defined("GDPR") && GDPR) {
        $heardItem['callsign_display'] = str_replace("0","Ø",substr($heard[1],0,3)."***");
    } else {
        $heardItem['callsign_display'] = str_replace("0","Ø",$heard[1]);
    }
    
    // Handle GDPR for gateway
    if (defined("GDPR") && GDPR) {
        $heardItem['gateway_display'] = str_replace("0","Ø",substr($heard[3],0,3)."***");
    } else {
        $heardItem['gateway_display'] = str_replace("0","Ø",$heard[3]);
    }
    
    $response['last_heard'][] = $heardItem;
}

// Gateway List
foreach ($gateways as $gateway) {
    $gatewayItem = [
        'timestamp' => convertTimezone($gateway['timestamp']),
        'callsign' => $gateway['callsign']
    ];
    
    // Handle GDPR
    if (defined("GDPR") && GDPR) {
        $gatewayItem['callsign_display'] = str_replace("0","Ø",substr($gateway['callsign'],0,3)."***");
    } else {
        $gatewayItem['callsign_display'] = str_replace("0","Ø",$gateway['callsign']);
    }
    
    $response['gateways'][] = $gatewayItem;
}

echo json_encode($response);
?>
