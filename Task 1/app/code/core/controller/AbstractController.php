<?php
/**
 * Class AbstractController - a basic controller implementation.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
abstract class AbstractController {
        // Active flag
        public $active                          = true;
        // Metadata variable
        public $meta                            = array();

        // Request variable
        protected $request                      = null;

        // Module variable
        protected $module                       = null;
        // Controller variable
        protected $controller                   = null;
        // Module path variable
        protected $modulePath                   = DIR_APP_CORE;
        // Action variable
        protected $action                       = null;
        // ID variable
        protected $id                           = null;
        // Parameters variable
        protected $params                       = array();

        /**
         * Constructor.
         */
        public function __construct(&$request, $loadCore = false) {
                // Set the request
                $this->setRequest($request);

                // Load the core configuration when needed
                if ($loadCore) $this->loadConfig(DIR_APP_CORE, true);
                // Load the module configuration
                $this->loadConfig();
        }

        /**
         * Load configuration settings from an XML file.
         *
         * @param       string          $configDir      Directory to load the XML file from (optional)
         * @param       boolean         $isCore         True when the core is being loaded, false otherwise (optional)
         * @throws      Exception                       Configuration error
         */
        protected function loadConfig($configDir = null, $isCore = false) {
                // If no config file directory was given, use the default file directory
                if (!$configDir) $configDir = $this->modulePath;
                // For the schema file, use the configuration file directory
                $schemaDir = DIR_APP_CONFIG;
                // Prepare the module message if the module parameter was given
                $moduleMessage = ($isCore ? '' : " in module '{$this->module}'");

                // Load the configuration file
                $xml = file_get_contents("{$configDir}config.xml");
                // In case the configuration file could not be read, throw an exception
                if (!$xml) throw new Exception("Configuration error: configuration file could not be read{$moduleMessage}");

                try {
                        // Surpress errors
                        libxml_use_internal_errors(true);
                        // Load the configuration into a new document object
                        $dom = new DOMDocument();
                        $dom->loadXML($xml);
                        // Validate the configuration using the schema file
                        if (!$dom->schemaValidate("{$schemaDir}config.xsd")) {
                                // Throw an exception with the validation error as message
                                // This exception is caught by the block below
                                throw new Exception(libxml_get_last_error()->message);
                        }

                        // By now we know the configuration is valid, load it into (another) object
                        // We're not using DOMDocument here because it's significantly harder to traverse
                        $config = new SimpleXMLElement($xml);
                } catch (Exception $e) {
                        // Invalid configuration, throw an exception
                        throw new Exception("Configuration error: invalid configuration content{$moduleMessage}, message: {$e->getMessage()}");
                }

                // Load the metadata tag
                $infoTag = $config->meta;
                // Do we have an 'active' tag and is its value not true (i.e.: not 'true' or '1')?
                // Then this controller is inactive
                if (isset($infoTag->active) && (string) $infoTag->active !== 'true' &&
                    (string) $infoTag->active !== '1') {
                        $this->active = false;
                        // We still need includes to handle the response, resume execution
                }
                // Set the metadata variable
                $this->meta[($isCore ? 'core' : 'module')] = (array) $infoTag;

                // Do we have a 'global' tag?
                if (isset($config->global)) {
                        // The tag contains include categories, loop
                        foreach ($config->global->children() as $includeCategory) {
                                // The include categories contain actual file includes, loop
                                foreach ($includeCategory->children() as $include) {
                                        // Include name (i.e. controller, model, helper)
                                        $includeName = $include->getName();
                                        // Include file name
                                        $includeFile = (string) $include;
                                        // Include path
                                        $includePath = $configDir . $includeName . DIRECTORY_SEPARATOR . $includeFile . '.php';

                                        // In case a requested file include does not exist, throw an exception
                                        if (!file_exists($includePath))
                                                throw new Exception("Configuration error: invalid {$includeName} include '{$includeFile}'{$moduleMessage}");

                                        // Include the file
                                        include_once($includePath);
                                }
                        }
                }
        }

        /**
         * Load a language file.
         *
         * @param       string          $language       Language code
         * @param       string          $languagePath   Path to the language file (optional)
         * @return      boolean                         True on success, false otherwise
         */
        protected function loadLanguage($language, $languagePath = DIR_APP_CORE_LANGUAGE) {
                // Prepare requested language file name
                if ($language) {
                        // Convert the language to lower case
                        $language = strtolower($language);

                        // Prepare the language file name
                        $languageFile = "{$languagePath}{$language}.php";
                }

                // In case the language is a base include, multi language support is disabled
                // or the language file does not exist, prepare the the default language file name
                if ($language !== REQUEST_LANGUAGE_BASE && (!REQUEST_LANGUAGE_ENABLED ||
                    !$language || !file_exists($languageFile))) {
                        // Convert the default language to lower case
                        $language = strtolower(REQUEST_LANGUAGE_DEFAULT);

                        // Prepare the default language file name
                        $languageFile = "{$languagePath}{$language}.php";
                }

                // In case the language file does not exist, return unsuccessful
                if (!file_exists($languageFile)) return false;

                // Include the language file
                require_once($languageFile);

                // Return successful
                return true;
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
         * Retrieve the controller instance for the given module.
         *
         * @param       string          $controller     Controller
         * @param       string          $controllerDir  Controller directory (optional)
         * @return      string                          Model instance
         */
        public function getController($controller, $controllerDir = null) {
                $controllerName = ucfirst($controller) . 'Controller';
                if (!class_exists($controllerName)) {
                        $controllerPath = ($controllerDir ? $controllerDir : $this->modulePath . DIR_CONTROLLER) . $controllerName . '.php';
                        if (!file_exists($controllerPath)) return false;

                        require_once($controllerPath);

                        if (!class_exists($controllerName)) return false;
                }

                // Create and return controller object
                return new $controllerName($this->request);
        }

        /**
         * Retrieve the model instance for the given action.
         *
         * @param       string          $modelName      Model name
         * @param       string          $modelDir       Model directory (optional)
         * @return      string                          Model instance
         */
        public function getModel($modelName, $modelDir = null) {
                $modelName = ucfirst($modelName) . 'Model';
                if (!class_exists($modelName)) {
                        $modelPath = ($modelDir ? $modelDir : $this->modulePath . DIR_MODEL) . $modelName . '.php';
                        if (!file_exists($modelPath)) return false;

                        require_once($modelPath);

                        if (!class_exists($modelName)) return false;
                }

                return new $modelName($this->request);
        }

        /**
         * Retrieve the view for the given action.
         *
         * @param       string          $action         Action (optional)
         * @param       string          $viewDir        View directory (optional)
         * @return      string                          View contents
         */
        public function getView($action = null, $viewDir = null) {
                $viewPath = $this->getViewPath($action, $viewDir);
                if (!file_exists($viewPath)) return '';

                // Get and return the view file
                ob_start();
                require_once($viewPath);
                $view = ob_get_clean();

                return $view;
        }

        /**
         * Retrieve the view path for the given action.
         *
         * @param       string          $action         Action (optional)
         * @param       string          $viewDir        View directory (optional)
         * @return      string                          View path
         */
        public function getViewPath($action = null, $viewDir = null) {
                if (empty($viewDir)) $viewDir = $this->modulePath . DIR_VIEW;

                if (file_exists($viewDir . $this->module))
                        $viewDir = $viewDir . $this->module . DIR_SEPARATOR;

                return $viewDir . (empty($action) ? $this->action : $action) . '.php';
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
         * Check whether a parameter is present.
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
         * Set a parameter.
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
         * Remove a parameter.
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
         * Compare a parameter to an expected value.
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
         * Add meta data to the curent page header.
         *
         * @param       string         $key            Attribute key
         * @param       string         $meta           Meta data
         * @param       boolean        $httpEquiv      True to use the HTTP-EQUIV attribute (optional)
         */
        public function addHeaderMeta($key, $meta, $httpEquiv = false) {
                Header::getInstance()->addMeta($key, $meta, $httpEquiv);
        }

        /**
         * Add CSS data to the current page header.
         *
         * @param       string          $css            CSS data
         * @param       boolean         $async          True to load asynchronous (optional)
         * @param       boolean         $isLink         True when referencing a script, false when inline (optional)
         */
        public function addHeaderCSS($css, $async = false, $isLink = true) {
                Header::getInstance()->addCSS($css, $async, $isLink);
        }

        /**
         * Add Javascript data to the current page header.
         *
         * @param       string          $js             Javascript data
         * @param       boolean         $async          True to load asynchronous (optional)
         * @param       boolean         $isLink         True when referencing a script, false when inline (optional)
         */
        public function addHeaderJS($js, $async = false, $isLink = true) {
                Header::getInstance()->addJS($js, $async, $isLink);
        }
}