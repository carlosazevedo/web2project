<?php
/**
 * If you experience a 'white screen of death' or other problems,
 * change the following line of code to this:
 */
ini_set('display_errors', 1);
ini_set('display_errors', 0);

if (defined('E_DEPRECATED')) {
    // since php 5.3
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL & ~ E_NOTICE);
}
//error_reporting(-1);

$baseDir = dirname(__file__);

/*
 * only rely on env variables if not using a apache handler
 */
function safe_get_env($name)
{
    if (isset($_SERVER[$name])) {
        return $_SERVER[$name];
    } elseif (strpos(php_sapi_name(), 'apache') === false) {
        getenv($name);
    } else {
        return '';
    }
}

// automatically define the base url
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' :
    'http://';
$baseUrl .= safe_get_env('HTTP_HOST');
$baseUrl .= dirname(safe_get_env('SCRIPT_NAME'));
$baseUrl = preg_replace('#/$#D', '', $baseUrl);
// Note: This resolves http://bugs.web2project.net/view.php?id=1081 on IIS,
// but I'm not sure I like it..
$baseUrl = stripslashes($baseUrl);

// Defines to deprecate the global baseUrl/baseDir
define('W2P_BASE_DIR', $baseDir);
define('W2P_BASE_URL', $baseUrl);

// Set the ADODB directory
if (!defined('ADODB_DIR')) {
    define('ADODB_DIR', W2P_BASE_DIR . '/lib/adodb');
}

/*
 *  This  is set to get past the dotProject security sentinel.  It is only
 * required during the conversion process to load config.php.  Hopefully we
 * will be able to kill this off down the road or someone can come up with a
 * better idea.
 */
define('DP_BASE_DIR', $baseDir);

// required includes for start-up
global $w2Pconfig;
$w2Pconfig = array();

// Start up mb_string UTF-8 if available
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

define('FMT_DATEISO', '%Y%m%dT%H%M%S');
define('FMT_DATELDAP', '%Y%m%d%H%M%SZ');
define('FMT_DATETIME_MYSQL', '%Y-%m-%d %H:%M:%S');
define('FMT_DATERFC822', '%a, %d %b %Y %H:%M:%S');
define('FMT_TIMESTAMP', '%Y%m%d%H%M%S');
define('FMT_TIMESTAMP_DATE', '%Y%m%d');
define('FMT_TIMESTAMP_TIME', '%H%M%S');
define('FMT_UNIX', '3');
define('WDAY_SUNDAY', 0);
define('WDAY_MONDAY', 1);
define('WDAY_TUESDAY', 2);
define('WDAY_WEDNESDAY', 3);
define('WDAY_THURSDAY', 4);
define('WDAY_FRIDAY', 5);
define('WDAY_SATURDAY', 6);
define('SEC_MINUTE', 60);
define('SEC_HOUR', 3600);
define('SEC_DAY', 86400);

define('UI_MSG_OK', 1);
define('UI_MSG_ALERT', 2);
define('UI_MSG_WARNING', 3);
define('UI_MSG_ERROR', 4);

$GLOBALS['translate'] = array();

define('UI_CASE_MASK', 0x0F);
define('UI_CASE_UPPER', 1);
define('UI_CASE_LOWER', 2);
define('UI_CASE_UPPERFIRST', 3);

define('UI_OUTPUT_MASK', 0xF0);
define('UI_OUTPUT_HTML', 0);
define('UI_OUTPUT_JS', 0x10);
define('UI_OUTPUT_RAW', 0x20);

define('ACCESS_DENIED', 'm=public&a=access_denied');

/**
 * This was previously set to ADODB_FETCH_NUM which was 1 in all cases.
 *  Realistically, we should use the constant to make sure they stay the same
 *  but we're trying to clean up some of the complexity in the loaded libraries.
 */
define('QUERY_STYLE_NUM', 1);