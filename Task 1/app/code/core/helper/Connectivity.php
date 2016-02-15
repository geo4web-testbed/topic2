<?php
/**
 * Class Connectivity - offers functionality related to external sources.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Connectivity {
        // Connection variable
        protected static $curl                  = null;

        /**
         * Initialize the cURL connection.
         *
         * @param       string          $url            URL to call
         * @param       array           $options        cURL options (optional)
         * @return      resource                        cURL connection
         */
        public static function initCurl($url, $options = array()) {
                // Initialize the cURL connection
                if (is_null(self::$curl)) self::$curl = curl_init();

                // Set the connection options
                curl_setopt_array(self::$curl, array_replace(array(CURLOPT_URL                  => str_replace(' ', '%20', $url),
                                                                   CURLOPT_FRESH_CONNECT        => false,
                                                                   CURLOPT_RETURNTRANSFER       => true,
                                                                   CURLOPT_TIMEOUT              => 30), $options));
        }

        /**
         * Close the cURL connection.
         */
        public static function closeCurl() {
                if (!is_null(self::$curl)) {
                        curl_close(self::$curl);
                        self::$curl = null;
                }
        }

        /**
         * Send a cURL request.
         *
         * @param       string          $url            URL to call
         * @param       array           $options        cURL options (optional)
         * @param       array           $timeout        Request timeout in seconds (optional)
         * @return      mixed                           Result
         */
        public static function runCurl($url, $options = array(), $timeout = 30) {
                // Prepare the timeout option
                $options[CURLOPT_TIMEOUT] = intval($timeout);

                // Initialize the cURL connection
                self::initCurl($url, $options);
                // Execute the call
                $curlResult = curl_exec(self::$curl);

                // In case of an unsuccessful request, set the result to false
                if (!in_array(curl_getinfo(self::$curl, CURLINFO_HTTP_CODE), array(200, 204, 301, 302, 304))) {
                        // In case debug mode is enabled, throw an error
                        if (debugMode()) ErrorHandler::error(E_NOTICE, 1, $url . ",\n" . curl_getinfo(self::$curl, CURLINFO_HTTP_CODE) . ': ' . curl_error(self::$curl) . ",\nresult: " . $curlResult);

                        $curlResult = false;
                }

                // Return the call result
                return $curlResult;
        }

        /**
         * Send an asynchrous cURL request.
         *
         * @param       string          $url            URL to call
         * @param       array           $options        cURL options (optional)
         * @return      mixed                           Result
         */
        public static function runCurlAsync($url, $options = array()) {
                // Send the cURL request
                $curlResult = self::runCurl($url, array_replace($options, array(CURLOPT_FRESH_CONNECT   => true,
                                                                                CURLOPT_TIMEOUT_MS      => 1)), false);

                // Return the call result
                return $curlResult;
        }

        /**
         * Get the cURL info related to the last cURL request.
         *
         * @param       mixed           $opt            cURL option (optional)
         * @return      array                           cURL info when available
         */
        public static function getCurlInfo($opt = null) {
                if (is_null(self::$curl)) {
                        return array();
                } else {
                        if (!$opt) {
                                $curlInfo = curl_getinfo(self::$curl);
                                if ($curlInfo) $curlInfo['error'] = curl_error(self::$curl);
                        } else {
                                $curlInfo = ($opt === 'error' ? curl_error(self::$curl) : curl_getinfo(self::$curl, $opt));
                        }

                        return $curlInfo;
                }
        }
}