<?php
/**
 * Config - basic settings and includes.
 *
 * @category    Geonovum
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */

/**
 * @todo:
 * - Visualization select/usage efficiency
 */

/**
 * Constants.
 */
// URLs, paths and files
define('URL_HTTPS', ($_SERVER['HTTPS'] === 'on'));
define('URL_PROTOCOL', (URL_HTTPS ? 'https' : 'http'));
define('URL_BASE', URL_PROTOCOL . "://{$_SERVER['HTTP_HOST']}/");

define('DIR_SEPARATOR', '/');
define('DIR_BASE', $_SERVER['DOCUMENT_ROOT'] . DIR_SEPARATOR);

define('DIR_CONTROLLER', 'controller' . DIR_SEPARATOR);
define('DIR_HELPER', 'helper' . DIR_SEPARATOR);
define('DIR_LANGUAGE', 'language' . DIR_SEPARATOR);
define('DIR_MODEL', 'model' . DIR_SEPARATOR);
define('DIR_VIEW', 'view' . DIR_SEPARATOR);

define('DIR_APP', DIR_BASE . 'app' . DIR_SEPARATOR);
define('DIR_APP_CODE', DIR_APP . 'code' . DIR_SEPARATOR);
define('DIR_APP_CONFIG', DIR_APP . 'config' . DIR_SEPARATOR);
define('DIR_APP_LOCALE', DIR_APP . 'locale' . DIR_SEPARATOR);

define('DIR_APP_CORE', DIR_APP_CODE . 'core' . DIR_SEPARATOR);
define('DIR_APP_CORE_CONTROLLER', DIR_APP_CORE . DIR_CONTROLLER);
define('DIR_APP_CORE_HELPER', DIR_APP_CORE . DIR_HELPER);
define('DIR_APP_CORE_LANGUAGE', DIR_APP_CORE . DIR_LANGUAGE);
define('DIR_APP_CORE_MODEL', DIR_APP_CORE . DIR_MODEL);
define('DIR_APP_CORE_VIEW', DIR_APP_CORE . DIR_VIEW);

define('DIR_APP_MODULE', DIR_APP_CODE . 'module' . DIR_SEPARATOR);
define('DIR_APP_MODULE_SPECIFIC', DIR_APP_MODULE . '%s' . DIR_SEPARATOR);
define('DIR_APP_MODULE_CONTROLLER', DIR_APP_MODULE_SPECIFIC . DIR_CONTROLLER);
define('DIR_APP_MODULE_HELPER', DIR_APP_MODULE_SPECIFIC . DIR_HELPER);
define('DIR_APP_MODULE_LANGUAGE', DIR_APP_MODULE_SPECIFIC . DIR_LANGUAGE);
define('DIR_APP_MODULE_MODEL', DIR_APP_MODULE_SPECIFIC . DIR_MODEL);
define('DIR_APP_MODULE_VIEW', DIR_APP_MODULE_SPECIFIC . DIR_VIEW);

define('DIR_CSS', DIR_BASE . 'css' . DIR_SEPARATOR);
define('DIR_IMAGE', DIR_BASE . 'img' . DIR_SEPARATOR);
define('DIR_JS', DIR_BASE . 'js' . DIR_SEPARATOR);
define('DIR_LIB', DIR_BASE . 'lib' . DIR_SEPARATOR);
define('DIR_MEDIA', DIR_BASE . 'media' . DIR_SEPARATOR);
define('DIR_MEDIA_DISCLAIMER', DIR_MEDIA . 'disclaimer' . DIR_SEPARATOR);
define('DIR_MEDIA_LAYOUT', DIR_MEDIA . 'layout' . DIR_SEPARATOR);
define('DIR_TEMP', DIR_BASE . 'temp' . DIR_SEPARATOR);

// Request
// URI
define('REQUEST_URI', $_SERVER['REQUEST_URI']);
// Referer
define('REQUEST_REFERER', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null));

// Locale
define('REQUEST_LOCALE', 'locale');
define('REQUEST_LOCALE_ENABLED', true);
define('REQUEST_LOCALE_DEFAULT', 'en_US');

// Messages
define('REQUEST_MESSAGE_TYPE_SUCCESS', 'success');
define('REQUEST_MESSAGE_TYPE_NOTICE', 'notice');
define('REQUEST_MESSAGE_TYPE_ERROR', 'error');

// Endpoint admin
define('REQUEST_ADMIN', 'admin');

// Endpoint modules
define('REQUEST_MODULE', 'module');
define('REQUEST_MODULE_DEFAULT', 'overview');

// Endpoint controllers
define('REQUEST_CONTROLLER', 'controller');
define('REQUEST_CONTROLLER_DEFAULT', 'overview');

// Endpoint actions
define('REQUEST_ACTION', 'action');
define('REQUEST_ACTION_DEFAULT', 'index');

// Parameters
define('REQUEST_PARAMETER_AJAX', 'isAjaxCall');
define('REQUEST_PARAMETER_BOUNDINGBOX', 'bbox');
define('REQUEST_PARAMETER_LOGGEDIN', 'loggedIn');
define('REQUEST_PARAMETER_MYMAP', 'myMap');
define('REQUEST_PARAMETER_QUERY', 'q');
define('REQUEST_PARAMETER_SESSION_ID', 'gnmsId');
define('REQUEST_PARAMETER_USER_EMAIL', 'userEmail');
define('REQUEST_PARAMETER_USER_NAME', 'userName');
define('REQUEST_PARAMETER_USER_PASSWORD', 'userPassword');
define('REQUEST_PARAMETER_VERIFICATION', 'v');
define('REQUEST_PARAMETER_VIZ', 'viz');
define('REQUEST_PARAMETER_VIZ_FIELDS', 'vizFields');
define('REQUEST_PARAMETER_VIZ_ID', 'vizId');
define('REQUEST_PARAMETER_VIZ_LAYER_ID', 'vizLayerId');
define('REQUEST_PARAMETER_VIZ_LAYER_INDEX', 'vizLayerIndex');
define('REQUEST_PARAMETER_VIZ_LAYER_NAME', 'vizLayerName');
define('REQUEST_PARAMETER_VIZ_LAYER_TYPE', 'vizLayerType');
define('REQUEST_PARAMETER_VIZ_QUERY', 'vizQuery');
define('REQUEST_PARAMETER_VIZ_TITLE', 'vizTitle');
define('REQUEST_PARAMETER_VIZ_URL', 'vizUrl');
define('REQUEST_PARAMETER_X', 'x');
define('REQUEST_PARAMETER_Y', 'y');
define('REQUEST_PARAMETER_ZOOM', 'zoom');

// Results
define('REQUEST_ERROR', 'error');
define('REQUEST_RESULT', 'result');
define('REQUEST_VALID', 'valid');

// General
// Brand settings
define('BRAND_NAME', 'Spotzi');
define('BRAND_PRODUCT', BRAND_NAME . ' Geonovum');

// Analytics settings
define('ANALYTICS_ACCOUNT', 'UA-27206803-1');

// Email settings
define('EMAIL_ERROR_SUBJECT', BRAND_PRODUCT . ' Error');

// Payment settings
define('PAYMENT_STATUS_ACCEPT', 'accepted');
define('PAYMENT_STATUS_CANCEL', 'canceled');
define('PAYMENT_STATUS_DECLINE', 'declined');
define('PAYMENT_STATUS_EXCEPTION', 'exception');

// Visualization settings
define('VISUALIZATION_DEFAULT', 'http://maps.spotzi.me/api/v2/viz/624435a6-ee58-11e4-9498-00265522ea30/viz.json');
define('VISUALIZATION_DOMAIN', 'spotzi.me');
define('VISUALIZATION_IMAGE_URL', 'http://data.spotzi.com/thumbnail/');
define('VISUALIZATION_IMPORT_SIZE', 1073741824);        // 1GB
define('VISUALIZATION_KEY', md5('Spotviz'));
define('VISUALIZATION_PLACEHOLDER', URL_PROTOCOL . '://tiles.spotzi.com/osm/0/0/0.png');
define('VISUALIZATION_URL', URL_PROTOCOL . '://' . VISUALIZATION_DOMAIN . '/');
define('VISUALIZATION_URL_ASSET', VISUALIZATION_URL . 'assets/3.7.32/');
define('VISUALIZATION_ZOOM_DEFAULT', 17);
define('VISUALIZATION_ZOOM_MAX', 18);

// Editor settings
define('EDITOR_ACTION_NEW_FEATURE', 'NEW_FEATURE');
define('EDITOR_ACTION_EDIT_DATA', 'EDIT_DATA');
define('EDITOR_ACTION_EDIT_GEOM', 'EDIT_GEOM');
define('EDITOR_ACTION_DELETE', 'DELETE');
define('EDITOR_POINT', 'POINT');
define('EDITOR_LINE', 'MULTILINESTRING');
define('EDITOR_POLYGON', 'MULTIPOLYGON');

// URL settings
define('URL_DASHBOARD', 'http://dashboard.spotzi.com/');
define('URL_DASHBOARD_ADMIN', URL_DASHBOARD . 'admin');

/**
 * Includes.
 */
// Environment configuration
require_once('environment.php');
// Request
require_once(DIR_APP . 'Request.php');
// Controllers
require_once(DIR_APP_CORE_CONTROLLER . 'AbstractController.php');
require_once(DIR_APP_CORE_CONTROLLER . 'ModuleController.php');
require_once(DIR_APP_CORE_CONTROLLER . 'CoreController.php');

/**
 * Exceptions.
 */

/**
 * Classes.
 */

/**
 * Functions.
 */
if (!function_exists('__')) {
        /**
         * Apply function arguments to a translation.
         *
         * @return      string                          Translation result
         */
        function __() {
                // Retrieve the function arguments
                $args = func_get_args();
                // Translate the message
                $args[0] = _($args[0]);

                return (count($args) > 1 ? call_user_func_array('sprintf', $args) : $args[0]);
        }
}

if (!function_exists('_e')) {
        /**
         * Apply function arguments to a translation and output directly.
         */
        function _e() {
                echo call_user_func_array('__', func_get_args());
        }
}

if (!function_exists('setExecutionSettings')) {
        /**
         * Set the script execution settings (memory limit, time limit and user abort).
         *
         * @param       int             $memoryLimit    Memory limit (optional)
         * @param       int             $timeLimit      Time limit (optional)
         * @param       int             $ignoreAbort    True to ignore user abort, false otherwise (optional)
         */
        function setExecutionSettings($memoryLimit = 256, $timeLimit = 0, $ignoreAbort = true) {
                if (!is_string($memoryLimit) || substr($memoryLimit, -2) !== 'M') $memoryLimit .= 'M';

                ini_set('memory_limit', $memoryLimit);
                set_time_limit($timeLimit);
                ignore_user_abort($ignoreAbort);
        }
}

if (!function_exists('addPath')) {
        /**
         * Add a path to PHP's path environment variable.
         *
         * @param       string          $path           Path
         */
        function addPath($path) {
                $origPath = str_replace('"', '', getenv('PATH'));
                if (!in_array($path, explode(PATH_SEPARATOR, $origPath)))
                        putenv('PATH=' . $origPath . PATH_SEPARATOR . $path);
        }
}

if (!function_exists('debugMode')) {
        /**
         * Return whether debug mode is enabled.
         *
         * @return      boolean                         True when debug mode is enabled, false otherwise
         */
        function debugMode() {
                return DEBUG_MODE;
        }
}

if (!function_exists('debugDump')) {
        /**
         * Dump variables and exit.
         *
         * @param       mixed           $var            Variable
         */
        function debugDump() {
                header('Content-Type: text/html; charset=utf-8');

                echo '<pre>';
                // Retrieve the method arguments
                foreach (func_get_args() as $arg) {
                        var_dump($arg);
                        echo '<p>';
                }
                exit();
        }
}

if (!function_exists('debugPrint')) {
        /**
         * Print variables and exit.
         */
        function debugPrint() {
                header('Content-Type: text/html; charset=utf-8');

                echo '<pre>';
                // Retrieve the method arguments
                foreach (func_get_args() as $arg) {
                        print_r($arg);
                        echo '<p>';
                }
                exit();
        }
}

if (!function_exists('logConsole')) {
        /**
         * Logs messages/variables/data to browser console from within PHP.
         *
         * @param       mixed           $value          Message to be shown for optional data/vars
         */
        function logConsole($value) {
                echo "<script>console.log('{$value}');</script>";
        }
}

if (!function_exists('getIp')) {
        /**
         * Return the requester's IP address.
         *
         * @return      string                          Requester IP address
         */
        function getIp() {
                if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
                        return $_SERVER['HTTP_CLIENT_IP'];
                } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
                        return $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
                        return $_SERVER['REMOTE_ADDR'];
                }
        }
}

if (!function_exists('ipIsLocal')) {
        /**
         * Return whether the requester's IP address is a local one.
         *
         * @return      boolean                         True when the IP address is local, false otherwise
         */
        function ipIsLocal() {
                return !filter_var(getIp(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
        }
}

if (!function_exists('ipIsOwn')) {
        /**
         * Return whether the requester's IP address is a local one.
         *
         * @return      boolean                         True when the IP address is local, false otherwise
         */
        function ipIsOwn() {
                $testIps = array('213.124.36.112', '213.124.36.113', '213.124.36.114', '213.124.36.115',        // Spotzi
                                 '213.124.36.116', '213.124.36.117', '213.124.36.118', '213.124.36.119',        // Spotzi
                                 '77.243.229.42',       // DB-WEB
                                 '86.85.2.11');         // Remco

                return (ipIsLocal() || in_array(getIp(), $testIps));
        }
}