<?php
/**
 * Class Request - represents a request.
 *
 * @category    Geonovum
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Request {
        // URI path indexes
        const URI_PATH_MODULE                   = 0;
        const URI_PATH_CONTROLLER               = 1;
        const URI_PATH_ACTION                   = 2;
        const URI_PATH_ID                       = 3;

        // Request variables
        public $ajax                            = false;
        public $admin                           = false;

        public $locale                          = REQUEST_LOCALE_DEFAULT;

        public $module                          = null;
        public $controller                      = null;
        public $action                          = null;
        public $id                              = null;
        public $params                          = null;

        /**
         * Constructor.
         */
        public function __construct() {
                // Initialize the request
                $this->initialize();
        }

        /**
         * Initialize the request.
         */
        private function initialize() {
                // Retrieve the request URI
                $uri = ltrim(REQUEST_URI, '/');

                $uriPathComponents = array();
                if ($uri) {
                        // Prepare the endpoint string
                        $uriPath = explode('?', $uri);

                        // Extract the endpoint components
                        $uriPathComponents = explode('/', reset($uriPath));
                }

                // Set the AJAX variable
                $this->ajax = ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                               (isset($_REQUEST[REQUEST_PARAMETER_AJAX]) &&
                               $_REQUEST[REQUEST_PARAMETER_AJAX]));

                // Set the admin variable
                if (reset($uriPathComponents) === REQUEST_ADMIN) {
                        $this->admin = true;
                        array_shift($uriPathComponents);
                }

                // Set the endpoint variables
                $this->module = (isset($uriPathComponents[self::URI_PATH_MODULE]) && $uriPathComponents[self::URI_PATH_MODULE] ?
                                        strtolower($uriPathComponents[self::URI_PATH_MODULE]) : REQUEST_MODULE_DEFAULT);
                $this->controller = (isset($uriPathComponents[self::URI_PATH_CONTROLLER]) && $uriPathComponents[self::URI_PATH_CONTROLLER] ?
                                        strtolower($uriPathComponents[self::URI_PATH_CONTROLLER]) : REQUEST_CONTROLLER_DEFAULT);
                $this->action = (isset($uriPathComponents[self::URI_PATH_ACTION]) && $uriPathComponents[self::URI_PATH_ACTION] ?
                                        strtolower($uriPathComponents[self::URI_PATH_ACTION]) : REQUEST_ACTION_DEFAULT);
                $this->id = (isset($uriPathComponents[self::URI_PATH_ID]) && is_numeric($uriPathComponents[self::URI_PATH_ID]) ?
                                        $uriPathComponents[self::URI_PATH_ID] : null);

                // Set the params variable
                $this->params = array_change_key_case($_REQUEST);
        }
}