<?php
/**
 * Class Url - offers functionality related to URLs.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Url {
        /**
         * Validate a URL.
         *
         * @param       string          $url            URL to validate
         * @return      boolean                         True on success, false otherwise
         */
        public static function validateUrl($url) {
                // Prepare the validation expression
                $regex = "((https?|ftp)\:\/\/)?";                                       // Scheme
                $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";  // User and Pass
                $regex .= "([a-z0-9-.]*)\.([a-z]{2,3})";                                // Host or IP
                $regex .= "(\:[0-9]{2,5})?";                                            // Port
                $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";                                // Path
                $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?";                   // Query string
                $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?";                                // Anchor

                // Return the URL validation result
                return preg_match("/^{$regex}$/i", $url);
        }

        /**
         * Build a URL.
         *
         * @param       string          $host           Host (optional)
         * @param       array           $params         Query parameters
         * @return      string                          URL
         */
        public static function buildUrl($host = null, $params = array()) {
                // In case no host is given, use the current host
                if (empty($host)) $host = URL_BASE;

                // Prepare the query string
                $query = (empty($params) ? '' : '?' . http_build_query($params));

                // Build and return the URL
                return $host . $query;
        }

        /**
         * Build a platform URL.
         *
         * @param       string          $admin          Request admin (optional)
         * @param       string          $module         Request module (optional)
         * @param       string          $controller     Request controller (optional)
         * @param       string          $action         Request action (optional)
         * @param       string          $id             Request data ID (optional)
         * @param       array           $params         Query parameters (optional)
         * @return      string                          Platform URL
         */
        public static function buildPlatformURL($admin = false, $module = null, $controller = null, $action = null, $id = null, $params = array()) {
                // Prepare the endpoint
                $endpoint = array();
                if ($admin) $endpoint[] = REQUEST_ADMIN;
                if ($module) $endpoint[] = strtolower($module);
                if ($controller) $endpoint[] = strtolower($controller);
                if ($action) $endpoint[] = strtolower($action);
                if (is_numeric($id)) $endpoint[] = $id;

                // Prepare the query string
                $query = (empty($params) ? '' : '?' . http_build_query($params));

                // Build and return the URL
                return URL_BASE . implode('/', $endpoint) . $query;
        }

        /**
         * Retrieve the current request URL.
         *
         * @param       boolean         $includeQuery   True to include the query string (optional)
         * @return      string                          Current request URL
         */
        public static function getCurrentUrl($includeQuery = true) {
                // Prepare the URL
                $url = URL_BASE . ltrim(REQUEST_URI, '/');

                // Return the URL
                return ($includeQuery ? $url : preg_replace('/\?.*/', '', $url));
        }

        /**
         * Shorten a URL.
         *
         * @param       string          $url            URL to shorten
         * @return      string                          Shortened URL
         */
        public static function shorten($url) {
                // Prepare the URL parameters
                $params = array('access_token'  => BITLY_TOKEN,
                                'longUrl'       => $url);
                $options = array(CURLOPT_SSL_VERIFYPEER => false);

                // Retrieve the URL
                $requestContents = Connectivity::runCurl(BITLY_URL . '?' . http_build_query($params), $options);

                // Parse and return the URL
                $requestJSON = json_decode($requestContents, true);
                return ($requestJSON && isset($requestJSON['data']['url']) ? $requestJSON['data']['url'] : $url);
        }
}