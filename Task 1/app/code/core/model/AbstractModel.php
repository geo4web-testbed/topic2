<?php
/**
 * Class AbstractModel - offers database functions used throughout the reports.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
abstract class AbstractModel {
        // Request variable
        public $request                         = null;

        // Module variable
        public $module                          = null;
        // Module path variable
        public $modulePath                      = null;
        // Controller variable
        public $controller                      = null;
        // Action variable
        public $action                          = null;
        // ID variable
        public $id                              = null;
        // Parameters variable
        public $params                          = array();

        /**
         * Constructor.
         *
         * @param       Request         $request        Request model (optional)
         */
        public function __construct(&$request = null) {
                // Set the request when needed
                if ($request) $this->setRequest($request);
        }

        /**
         * Retrieve the request.
         *
         * @return      RequestModel                    Request object
         */
        protected function getRequest() {
                return $this->request;
        }

        /**
         * Set the request.
         *
         * @param       RequestModel    $request        Request object
         */
        protected function setRequest(&$request) {
                // Set the request variable
                $this->request = &$request;

                // Set the module variable
                $this->module = &$request->module;
                // Prepare and set the module path variable
                $reflection = new ReflectionClass(get_called_class());
                $this->modulePath = dirname(dirname($reflection->getFileName())) . DIR_SEPARATOR;
                // Set the controller variable
                $this->controller = &$request->controller;
                // Set the action variable
                $this->action = &$request->action;
                // Set the ID variable
                $this->id = &$request->id;
                // Set the request parameters variable
                $this->params = &$request->params;
        }

        /**
         * Set the locale.
         *
         * @param       string          $locale         Locale
         */
        protected function setLocale($locale) {
                if (REQUEST_LOCALE_ENABLED && $locale) {
                        $this->request->locale = $locale;

                        // Reset locale (needed for dates)
                        setlocale(LC_ALL, "");
                        // Set locale
                        putenv('LC_ALL=' . $locale);
                        setlocale(LC_ALL, $locale);
                        // Set locale location
                        bindtextdomain('messages', DIR_APP_LOCALE);
                        textdomain('messages');
                }
        }

        /**
         * Retrieve the request parameters.
         *
         * @return      array                           Request parameters
         */
        public function getParams() {
                return $this->params;
        }

        /**
         * Check whether a request parameter is present.
         *
         * @param       mixed           $key            Parameter name
         * @return      boolean                         True when present, false otherwise
         */
        public function hasParam($key) {
                return isset($this->params[strtolower($key)]);
        }

        /**
         * Retrieve a parameter.
         *
         * @param       mixed           $key            Parameter name
         * @param       mixed           $defaultValue   Default parameter value when not present
         * @return      boolean                         Parameter when present, false otherwise
         */
        public function getParam($key, $defaultValue = null) {
                $param = $defaultValue;
                if (isset($this->params[strtolower($key)])) {
                        $param = $this->params[strtolower($key)];

                        if (is_string($param)) $param = trim($param);
                }

                return $param;
        }

        /**
         * Retrieve a boolean parameter.
         *
         * @param       string          $key            Parameter name
         * @param       mixed           $defaultValue   Default parameter value when not present
         * @return      mixed                           True or false
         */
        public function getBooleanParam($key, $defaultValue = true) {
                $param = $this->getParam($key);

                $booleanParam = false;
                if ($defaultValue) {
                        $booleanParam = (is_null($param) || ($param !== 'false' && $param !== '0'));
                } else {
                        $booleanParam = (!is_null($param) && ($param !== 'false' && $param !== '0'));
                }

                return $booleanParam;
        }

        /**
         * Set a request parameter.
         *
         * @param       mixed           $key            Parameter name
         * @param       mixed           $value          Parameter value
         * @return      boolean                         True on success
         */
        public function setParam($key, $value) {
                $this->params[strtolower($key)] = $value;

                // Return successful
                return true;
        }

        /**
         * Remove a request parameter.
         *
         * @param       mixed           $key            Parameter name
         * @return      boolean                         True on success
         */
        public function removeParam($key) {
                // In case the parameter is present, remove it
                if (isset($this->params[strtolower($key)]))
                        unset($this->params[strtolower($key)]);

                // Return successful
                return true;
        }

        /**
         * Compare a request parameter to an expected value.
         *
         * @param       mixed           $key            Parameter name
         * @param       mixed           $expectedValue  Expected parameter value
         * @return      boolean                         True when the values match, false otherwise
         */
        public function compareParam($key, $expectedValue) {
                // In case the parameter is not present, return unsuccessful
                if (!isset($this->params[strtolower($key)])) return false;

                // Return the comparison result
                return ($this->params[strtolower($key)] === $expectedValue);
        }

        /**
         * Clear the current session.
         */
        public function clearSession() {
                Session::clearData(REQUEST_PARAMETER_LOGGEDIN);
                Session::clearData(REQUEST_PARAMETER_USER_NAME);
        }
}