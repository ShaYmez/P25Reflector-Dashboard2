<?php
/**
 * P25Reflector-Dashboard2 by M0VUB Aka ShaYmez - Main Dashboard
 * Responsive dashboard for P25Reflector
 * Copyright (C) 2025  Shane Daley, M0VUB Aka. ShaYmez
 */

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

// Check if config exists
if (!file_exists("config/config.php")) {
    header("Location: setup.php");
    exit();
}

// Load configuration and includes
include "config/config.php";
include "include/tools.php";
include "include/functions.php";

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
$sysInfo = getSystemInfo();
$diskInfo = getDiskInfo();

// Version info
define("VERSION", "2.0.1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="P25Reflector-Dashboard V2">
    <meta name="author" content="M0VUB Aka ShaYmez">
    <meta http-equiv="expires" content="0">
    
    <title><?php echo htmlspecialchars(defined("DASHBOARD_NAME") ? DASHBOARD_NAME : "P25 Reflector Dashboard", ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(getConfigItem("Info", "Name", $configs), ENT_QUOTES, 'UTF-8'); ?></title>
    
    <link rel="stylesheet" href="assets/css/output.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Animated Background -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900"></div>
        <div class="absolute top-0 left-0 w-full h-full opacity-20">
            <div class="absolute top-20 left-20 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl animate-pulse"></div>
            <div class="absolute bottom-20 right-20 w-96 h-96 bg-purple-500 rounded-full filter blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <?php checkSetup(); ?>

        <!-- Header -->
        <div class="card-glossy p-6 mb-8">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-4xl font-bold mb-2 bg-clip-text text-transparent bg-gradient-to-r from-blue-200 to-purple-200">
                        <?php echo htmlspecialchars(defined("DASHBOARD_NAME") ? DASHBOARD_NAME : "P25 Reflector Dashboard", ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <p class="text-lg text-white/80">
                        <?php echo htmlspecialchars(defined("DASHBOARD_TAGLINE") ? DASHBOARD_TAGLINE : "Modern Dashboard for Amateur Radio", ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <div class="mt-4 flex flex-wrap gap-4 text-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Reflector: <strong><?php echo htmlspecialchars(getConfigItem("Info", "Name", $configs), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span>ID: <strong>#<?php echo htmlspecialchars(getConfigItem("Info", "Id", $configs), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span><?php echo htmlspecialchars(getConfigItem("Info", "Description", $configs), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </div>
                <?php 
                $logoPath = getLogoPath();
                if ($logoPath !== false) { 
                ?>
                <div class="mt-6 lg:mt-0">
                    <img src="<?php echo htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8'); ?>" 
                         alt="Logo" 
                         class="max-w-xs h-32 object-contain rounded-xl shadow-glossy">
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- Currently TXing Alert -->
        <?php if ($currentlyTXing !== null) { ?>
        <div id="tx-alert" class="card-glossy p-6 mb-8 border-2 border-red-500/50 animate-pulse">
            <div class="flex items-center">
                <div class="bg-red-500/30 p-4 rounded-xl mr-6">
                    <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-3 animate-pulse"></span>
                        <h2 class="text-3xl font-bold text-red-400">TRANSMITTING..</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <p class="text-white/60 text-sm uppercase tracking-wide">Callsign</p>
                            <p id="tx-callsign" class="text-2xl font-bold text-white mt-1">
                                <?php 
                                if (defined("SHOWQRZ") && SHOWQRZ && $currentlyTXing['source'] !== "??????????" && !is_numeric($currentlyTXing['source'])) {
                                    echo '<a target="_blank" href="https://qrz.com/db/'.htmlspecialchars($currentlyTXing['source'], ENT_QUOTES, 'UTF-8').'" class="text-blue-300 hover:text-blue-200 underline">'.htmlspecialchars(str_replace("0","Ø",$currentlyTXing['source']), ENT_QUOTES, 'UTF-8').'</a>';
                                } else if (defined("GDPR") && GDPR) {
                                    echo htmlspecialchars(str_replace("0","Ø",substr($currentlyTXing['source'],0,3)."***"), ENT_QUOTES, 'UTF-8');
                                } else {
                                    echo htmlspecialchars(str_replace("0","Ø",$currentlyTXing['source']), ENT_QUOTES, 'UTF-8');
                                }
                                ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-white/60 text-sm uppercase tracking-wide">Target</p>
                            <p id="tx-target" class="text-2xl font-bold text-white mt-1"><?php echo htmlspecialchars($currentlyTXing['target'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div>
                            <p class="text-white/60 text-sm uppercase tracking-wide">Via Gateway</p>
                            <p id="tx-gateway" class="text-2xl font-bold text-white mt-1">
                                <?php 
                                if (defined("GDPR") && GDPR) {
                                    echo htmlspecialchars(str_replace("0","Ø",substr($currentlyTXing['gateway'],0,3)."***"), ENT_QUOTES, 'UTF-8');
                                } else {
                                    echo htmlspecialchars(str_replace("0","Ø",$currentlyTXing['gateway']), ENT_QUOTES, 'UTF-8');
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- System Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Connected Gateways -->
            <div class="card-glossy p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm font-semibold uppercase tracking-wide">Connected</p>
                        <p id="gateway-count" class="text-4xl font-bold mt-2"><?php echo count($gateways); ?></p>
                        <p class="text-sm text-white/80 mt-1">Gateways</p>
                    </div>
                    <div class="bg-blue-500/20 p-4 rounded-xl">
                        <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- CPU Load -->
            <div class="card-glossy p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm font-semibold uppercase tracking-wide">CPU Load</p>
                        <p class="text-4xl font-bold mt-2"><?php echo number_format($sysInfo['load'][0], 2); ?></p>
                        <p class="text-sm text-white/80 mt-1">Average (1m)</p>
                    </div>
                    <div class="bg-green-500/20 p-4 rounded-xl">
                        <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Temperature -->
            <div class="card-glossy p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm font-semibold uppercase tracking-wide">Temperature</p>
                        <p class="text-4xl font-bold mt-2 <?php echo (defined('TEMPERATUREALERT') && $sysInfo['temperature'] > TEMPERATUREHIGHLEVEL) ? 'text-red-500' : ''; ?>"><?php echo $sysInfo['temperature']; ?>°C</p>
                        <p class="text-sm text-white/80 mt-1">CPU Temp</p>
                    </div>
                    <div class="bg-orange-500/20 p-4 rounded-xl">
                        <svg class="w-10 h-10 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Disk Usage -->
            <div class="card-glossy p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm font-semibold uppercase tracking-wide">Disk Usage</p>
                        <p class="text-4xl font-bold mt-2"><?php echo $diskInfo['percent']; ?>%</p>
                        <p class="text-sm text-white/80 mt-1"><?php echo $diskInfo['used']; ?> / <?php echo $diskInfo['total']; ?> GB</p>
                    </div>
                    <div class="bg-purple-500/20 p-4 rounded-xl">
                        <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Connected Gateways -->
            <div class="card-glossy p-6">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    Connected Gateways
                </h2>
                <div class="overflow-x-auto">
                    <table class="table-glossy">
                        <thead>
                            <tr>
                                <th>Time (<?php echo TIMEZONE;?>)</th>
                                <th>Callsign</th>
                            </tr>
                        </thead>
                        <tbody id="gateways-table-body">
                            <?php
                            if (count($gateways) > 0) {
                                foreach ($gateways as $gateway) {
                                    echo "<tr>";
                                    echo "<td>".htmlspecialchars(convertTimezone($gateway['timestamp']), ENT_QUOTES, 'UTF-8')."</td>";
                                    if (defined("GDPR") && GDPR) {
                                        echo "<td>".htmlspecialchars(str_replace("0","Ø",substr($gateway['callsign'],0,3)."***"), ENT_QUOTES, 'UTF-8')."</td>";
                                    } else {
                                        echo "<td>".htmlspecialchars(str_replace("0","Ø",$gateway['callsign']), ENT_QUOTES, 'UTF-8')."</td>";
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' class='text-center text-white/60'>No gateways connected</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System Information -->
            <div class="card-glossy p-6">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                    System Information
                </h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-white/10">
                        <span class="text-white/80">System Uptime</span>
                        <span class="font-semibold"><?php echo $sysInfo['uptime']; ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-white/10">
                        <span class="text-white/80">Load Average (1m)</span>
                        <span class="font-semibold"><?php echo number_format($sysInfo['load'][0], 2); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-white/10">
                        <span class="text-white/80">Load Average (5m)</span>
                        <span class="font-semibold"><?php echo number_format($sysInfo['load'][1], 2); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-white/10">
                        <span class="text-white/80">Load Average (15m)</span>
                        <span class="font-semibold"><?php echo number_format($sysInfo['load'][2], 2); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-white/10">
                        <span class="text-white/80">P25Reflector Version</span>
                        <span class="font-semibold text-sm"><?php echo getP25ReflectorVersion(); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3">
                        <span class="text-white/80">Dashboard Version</span>
                        <span class="font-semibold"><?php echo VERSION; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Heard List -->
        <div class="card-glossy p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                </svg>
                Last Heard List
            </h2>
            <div class="overflow-x-auto">
                <table class="table-glossy">
                    <thead>
                        <tr>
                            <th>Time (<?php echo TIMEZONE;?>)</th>
                            <th>Callsign</th>
                            <th>Target</th>
                            <th>Gateway</th>
                        </tr>
                    </thead>
                    <tbody id="last-heard-table-body">
                        <?php
                        if (count($lastHeard) > 0) {
                            foreach ($lastHeard as $heard) {
                                echo "<tr>";
                                echo "<td>".htmlspecialchars($heard[0], ENT_QUOTES, 'UTF-8')."</td>";
                                
                                // Callsign with QRZ link if enabled
                                if (defined("SHOWQRZ") && SHOWQRZ && $heard[1] !== "??????????" && !is_numeric($heard[1])) {
                                    echo "<td><a target=\"_blank\" href=\"https://qrz.com/db/".htmlspecialchars($heard[1], ENT_QUOTES, 'UTF-8')."\" class=\"text-blue-400 hover:text-blue-300 underline\">".htmlspecialchars(str_replace("0","Ø",$heard[1]), ENT_QUOTES, 'UTF-8')."</a></td>";
                                } else {
                                    if (defined("GDPR") && GDPR) {
                                        echo "<td>".htmlspecialchars(str_replace("0","Ø",substr($heard[1],0,3)."***"), ENT_QUOTES, 'UTF-8')."</td>";
                                    } else {
                                        echo "<td>".htmlspecialchars(str_replace("0","Ø",$heard[1]), ENT_QUOTES, 'UTF-8')."</td>";
                                    }
                                }
                                
                                echo "<td>".htmlspecialchars($heard[2], ENT_QUOTES, 'UTF-8')."</td>";
                                
                                // Gateway callsign
                                if (defined("GDPR") && GDPR) {
                                    echo "<td>".htmlspecialchars(str_replace("0","Ø",substr($heard[3],0,3)."***"), ENT_QUOTES, 'UTF-8')."</td>";
                                } else {
                                    echo "<td>".htmlspecialchars(str_replace("0","Ø",$heard[3]), ENT_QUOTES, 'UTF-8')."</td>";
                                }
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-white/60'>No activity recorded</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="card-glossy p-6 text-center">
            <div class="text-sm text-white/80">
                <?php
                $lastReload = new DateTime();
                $lastReload->setTimezone(new DateTimeZone(TIMEZONE));
                echo "P25Reflector-Dashboard2 V".VERSION." | Last Reload ".$lastReload->format('Y-m-d, H:i:s')." (".TIMEZONE.")";
                $time = microtime();
                $time = explode(' ', $time);
                $time = $time[1] + $time[0];
                $finish = $time;
                $total_time = round(($finish - $start), 4);
                echo ' | Page generated in '.$total_time.' seconds';
                ?>
            </div>
            <div class="mt-3">
                <a href="https://github.com/ShaYmez/P25Reflector-Dashboard2" target="_blank" class="text-blue-400 hover:text-blue-300 underline text-sm">
                    Get your own at GitHub
                </a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript for full dashboard live updates -->
    <script>
        // Update dashboard every 5 seconds for fully responsive, real-time updates
        let lastTxState = <?php echo $currentlyTXing !== null ? 'true' : 'false'; ?>;
        let lastTxCallsign = <?php echo $currentlyTXing !== null ? '"' . addslashes($currentlyTXing['source']) . '"' : 'null'; ?>;
        
        function updateDashboard() {
            fetch('api/dashboard_data.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Dashboard update failed:', data.error);
                        return;
                    }
                    
                    // Update TX Status
                    updateTxStatus(data.tx_status);
                    
                    // Update Gateway Count
                    const gatewayCountEl = document.getElementById('gateway-count');
                    if (gatewayCountEl) {
                        gatewayCountEl.textContent = data.gateway_count;
                    }
                    
                    // Update Gateways Table
                    updateGatewaysTable(data.gateways);
                    
                    // Update Last Heard Table
                    updateLastHeardTable(data.last_heard);
                })
                .catch(error => {
                    console.error('Error fetching dashboard data:', error);
                });
        }
        
        function updateTxStatus(txData) {
            const txAlert = document.getElementById('tx-alert');
            
            if (txData && txData.is_transmitting) {
                // Transmission is active
                if (!lastTxState) {
                    // New transmission started - show alert
                    if (txAlert) {
                        txAlert.style.display = 'block';
                        txAlert.classList.add('animate-pulse');
                    } else {
                        // TX alert doesn't exist, create it dynamically
                        createTxAlert(txData);
                    }
                } else {
                    // Update existing TX display
                    updateTxDisplay(txData);
                }
                lastTxState = true;
                lastTxCallsign = txData.source;
            } else {
                // No transmission
                if (lastTxState && txAlert) {
                    // Transmission ended - hide alert
                    txAlert.style.display = 'none';
                }
                lastTxState = false;
                lastTxCallsign = null;
            }
        }
        
        function createTxAlert(data) {
            // Create TX alert element dynamically if it doesn't exist
            const container = document.querySelector('.container');
            const statsGrid = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2');
            
            if (!container || !statsGrid) return;
            
            const txAlertHTML = `
                <div id="tx-alert" class="card-glossy p-6 mb-8 border-2 border-red-500/50 animate-pulse">
                    <div class="flex items-center">
                        <div class="bg-red-500/30 p-4 rounded-xl mr-6">
                            <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-3 animate-pulse"></span>
                                <h2 class="text-3xl font-bold text-red-400">TRANSMITTING..</h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <p class="text-white/60 text-sm uppercase tracking-wide">Callsign</p>
                                    <p id="tx-callsign" class="text-2xl font-bold text-white mt-1"></p>
                                </div>
                                <div>
                                    <p class="text-white/60 text-sm uppercase tracking-wide">Target</p>
                                    <p id="tx-target" class="text-2xl font-bold text-white mt-1"></p>
                                </div>
                                <div>
                                    <p class="text-white/60 text-sm uppercase tracking-wide">Via Gateway</p>
                                    <p id="tx-gateway" class="text-2xl font-bold text-white mt-1"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            statsGrid.insertAdjacentHTML('beforebegin', txAlertHTML);
            updateTxDisplay(data);
        }
        
        function updateTxDisplay(data) {
            const callsignEl = document.getElementById('tx-callsign');
            if (callsignEl) {
                if (data.qrz_link) {
                    callsignEl.innerHTML = '<a target="_blank" href="' + data.qrz_link + '" class="text-blue-300 hover:text-blue-200 underline">' + data.source_display + '</a>';
                } else {
                    callsignEl.textContent = data.source_display;
                }
            }
            
            const targetEl = document.getElementById('tx-target');
            if (targetEl) {
                targetEl.textContent = data.target;
            }
            
            const gatewayEl = document.getElementById('tx-gateway');
            if (gatewayEl) {
                gatewayEl.textContent = data.gateway_display;
            }
        }
        
        function updateGatewaysTable(gateways) {
            const tbody = document.getElementById('gateways-table-body');
            if (!tbody) return;
            
            if (gateways.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center text-white/60">No gateways connected</td></tr>';
                return;
            }
            
            let html = '';
            gateways.forEach(gateway => {
                html += '<tr>';
                html += '<td>' + gateway.timestamp + '</td>';
                html += '<td>' + gateway.callsign_display + '</td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
        }
        
        function updateLastHeardTable(lastHeard) {
            const tbody = document.getElementById('last-heard-table-body');
            if (!tbody) return;
            
            if (lastHeard.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-white/60">No activity recorded</td></tr>';
                return;
            }
            
            let html = '';
            lastHeard.forEach(heard => {
                html += '<tr>';
                html += '<td>' + heard.time + '</td>';
                
                // Callsign with optional QRZ link
                if (heard.qrz_link) {
                    html += '<td><a target="_blank" href="' + heard.qrz_link + '" class="text-blue-400 hover:text-blue-300 underline">' + heard.callsign_display + '</a></td>';
                } else {
                    html += '<td>' + heard.callsign_display + '</td>';
                }
                
                html += '<td>' + heard.target + '</td>';
                html += '<td>' + heard.gateway_display + '</td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
        }
        
        // Start updating dashboard every 5 seconds for full real-time experience
        setInterval(updateDashboard, 5000);
    </script>
</body>
</html>
