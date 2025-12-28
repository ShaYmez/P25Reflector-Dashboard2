<?php
/**
 * P25Reflector-Dashboard2 - Tools and Helper Functions
 * Modern dashboard for P25Reflector
 * Copyright (C) 2025  Shane Daley, M0VUB Aka. ShaYmez
 */

function format_time($seconds) {
    $secs = intval($seconds % 60);
    $mins = intval($seconds / 60 % 60);
    $hours = intval($seconds / 3600 % 24);
    $days = intval($seconds / 86400);
    $uptimeString = "";

    if ($days > 0) {
        $uptimeString .= $days;
        $uptimeString .= (($days == 1) ? "&nbsp;day" : "&nbsp;days");
    }
    if ($hours > 0) {
        $uptimeString .= (($days > 0) ? ", " : "") . $hours;
        $uptimeString .= (($hours == 1) ? "&nbsp;hr" : "&nbsp;hrs");
    }
    if ($mins > 0) {
        $uptimeString .= (($days > 0 || $hours > 0) ? ", " : "") . $mins;
        $uptimeString .= (($mins == 1) ? "&nbsp;min" : "&nbsp;mins");
    }
    if ($secs > 0) {
        $uptimeString .= (($days > 0 || $hours > 0 || $mins > 0) ? ", " : "") . $secs;
        $uptimeString .= (($secs == 1) ? "&nbsp;s" : "&nbsp;s");
    }
    return $uptimeString;
}

function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function searchForKey($field, $needle, $array) {
    foreach ($array as $key => $val) {
        if ($val[$field] === $needle) {
            return $key;
        }
    }
    return null;
}

function checkSetup() {
   $el = error_reporting();
   error_reporting(E_ERROR | E_WARNING | E_PARSE);
   if (defined("DISTRIBUTION")) {
?>
<div class="bg-red-500/20 border border-red-500 rounded-xl p-4 mb-6" role="alert">
    <div class="flex items-center">
        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <span>You are using an old config.php. Please configure your Dashboard by calling <a href="setup.php" class="underline font-semibold">setup.php</a>!</span>
    </div>
</div>
<?php
   } else {
      if (file_exists ("setup.php") && ! defined("DISABLESETUPWARNING")) {
?>
<div class="bg-red-500/20 border border-red-500 rounded-xl p-4 mb-6" role="alert">
    <div class="flex items-center">
        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <span>You forgot to remove setup.php in root-directory of your dashboard or you forgot to configure it! Please delete the file or configure your Dashboard by calling <a href="setup.php" class="underline font-semibold">setup.php</a>!</span>
    </div>
</div>
<?php
      }
   }
   error_reporting($el);
}

function convertTimezone($timestamp) {
   $date = new DateTime($timestamp);
   $date->setTimezone(new DateTimeZone(TIMEZONE));   
   return $date->format('Y-m-d H:i:s');
}
?>
