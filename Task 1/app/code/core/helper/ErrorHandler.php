<?php
// Activate the custom error handler
ErrorHandler::setCustomErrorHandling();
// Display errors
ini_set('display_errors', '1');

/**
 * Class ErrorHandler - offers error handling support functions.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class ErrorHandler {
        // List containing all possible error types
        public static $errortypes = array (E_ERROR              => 'Error',
                                           E_WARNING            => 'Warning',
                                           E_PARSE              => 'Parsing Error',
                                           E_NOTICE             => 'Notice',
                                           E_CORE_ERROR         => 'Core Error',
                                           E_CORE_WARNING       => 'Core Warning',
                                           E_COMPILE_ERROR      => 'Compile Error',
                                           E_COMPILE_WARNING    => 'Compile Warning',
                                           E_USER_ERROR         => 'User Error',
                                           E_USER_WARNING       => 'User Warning',
                                           E_USER_NOTICE        => 'User Notice',
                                           E_STRICT             => 'Runtime Notice',
                                           E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
                                           E_DEPRECATED         => 'Deprecated',
                                           E_USER_DEPRECATED    => 'User deprecated',
                                           E_ALL                => 'All');

        /**
         * Throw an error.
         */
        public static function error() {
                // Retrieve the function arguments
                $args = func_get_args();

                // Retrieve the error number
                $errorNo = array_shift($args);
                // Retrieve the error index
                $errorArg = reset($args);
                $errorIndex = (is_integer($errorArg) && $errorArg >= 0 ? array_shift($args) : 0);

                // Retrieve the debug backtrace
                $errorTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                // Prepare the error message
                $errorMsg = (count($args) > 1 ? call_user_func_array('sprintf', $args) : reset($args));

                // Handle the error
                self::handle($errorNo, $errorMsg, $errorTrace[$errorIndex]['file'], $errorTrace[$errorIndex]['line']);
        }

        /**
         * Handle an error.
         *
         * @param       int             $errorNo        Error number
         * @param       string          $errorMsg       Error message
         * @param       string          $errorFile      Error script file
         * @param       int             $errorLine      Error script line
         */
        public static function handle($errorNo, $errorMsg, $errorFile, $errorLine) {
                // Build the complete error message
                $mailMsg = '<b>--- ' . BRAND_NAME . ' ErrorHandler ---</b>' . PHP_EOL . PHP_EOL;
                $mailMsg .= 'Date: '. Date::format() . PHP_EOL;
                $mailMsg .= 'Error number: ' . $errorNo . PHP_EOL;
                $mailMsg .= 'Error type: ' . self::$errortypes[$errorNo] . PHP_EOL;
                $mailMsg .= 'Error message: ' . $errorMsg . PHP_EOL;
                $mailMsg .= 'Script name: ' . $errorFile . PHP_EOL;
                $mailMsg .= 'Line number: ' . $errorLine . PHP_EOL;
                $mailMsg .= 'Request URL: ' . URL_BASE . ltrim(REQUEST_URI, '/') . PHP_EOL;
                if (isset($_SERVER['HTTP_USER_AGENT'])) $mailMsg .= 'User agent: ' . $_SERVER['HTTP_USER_AGENT'];

                // Determine whether debug mode is active
                if (debugMode()) {
                        // In case debug mode is active, set the error message as the frontend message
                        debugPrint($mailMsg);
                } else {
                        // Prepare the error mailer
                        Mail::addMailer(EMAIL_HOST, EMAIL_PORT, EMAIL_ERROR_FROM,
                                        EMAIL_ERROR_FROM_PASSWORD, BRAND_PRODUCT);
                        // Send the error email
                        Mail::send(EMAIL_ERROR_RECIPIENT, EMAIL_ERROR_FROM,
                                   EMAIL_ERROR_SUBJECT, nl2br($mailMsg));

                        // In case of a fatal error, stop execution and show the general frontend message
                        if ($errorNo !== E_WARNING && $errorNo !== E_NOTICE &&
                            $errorNo !== E_USER_NOTICE && $errorNo !== E_STRICT)
                                debugPrint(__('An unexpected error has occured.<br>If this error keeps occuring, please contact your vendor for assistance') . __('<br>Message: %s', $errorMsg));
                }
        }

        /**
         * Activate the default error handler.
         */
        public static function setDefaultErrorHandling() {
                restore_error_handler();
        }

        /**
         * Activate our custom error handler.
         */
        public static function setCustomErrorHandling() {
                // Activate our custom error handler
                set_error_handler(array(get_class(), 'handle'));

                // Set the error handling level depending on debug/production mode
                error_reporting(debugMode() ? E_ALL | E_STRICT : E_ALL);
        }
}