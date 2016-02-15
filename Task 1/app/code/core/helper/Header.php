<?php
/**
 * Class Header - offers header related functions.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Header {
        // Meta data
        protected $meta                         = array();
        // CSS data
        protected $css                          = array();
        // Javascript
        protected $js                           = array();

        // Instance variable
        private static $instance                = null;

        /**
         * Private constructor prevents other classes from initializing the class,
         * forcing the use of getInstance().
         */
        private function __construct() {}

        /**
         * Return an instance of Header.
         *
         * @return      Header                          Instance of Header
         */
        public static function getInstance() {
                // Lazy initialization: create the object at the first request
                if (empty(self::$instance)) {
                        // Create an instance of Header
                        self::$instance = new self();
                }

                // Return the instance
                return self::$instance;
        }

        /**
         * Add meta data to the curent page header.
         *
         * @param       string         $key            Attribute key
         * @param       string         $meta           Meta data
         * @param       boolean        $httpEquiv      True to use the HTTP-EQUIV attribute (optional)
         */
        public function addMeta($key, $meta, $httpEquiv = false) {
                // Only set meta data when the key is valid
                if ($key) $this->meta[] = self::prepareMeta($key, $meta, $httpEquiv);
        }

        /**
         * Retrieve current page header meta data.
         *
         * @return      array                           Meta data
         */
        public function getMeta() {
                // Return meta data
                return $this->meta;
        }

        /**
         * Prepare meta HTML data.
         *
         * @param       string         $key            Attribute key
         * @param       string         $meta           Meta data
         * @param       boolean        $httpEquiv      True to use the HTTP-EQUIV attribute (optional)
         */
        public static function prepareMeta($key, $meta, $httpEquiv = false) {
                // Determine the key attribute name
                $keyAttr = ($httpEquiv ? 'http-equiv' : 'name');
                // Prepare the meta data content
                return "<meta {$keyAttr}=\"{$key}\" content=\"{$meta}\">\n";
        }

                /**
         * Add CSS data to the current page header.
         *
         * @param       string          $css            CSS data
         * @param       boolean         $async          True to load asynchronous (optional)
         * @param       boolean         $isLink         True when referencing a script, false when inline (optional)
         */
        public function addCSS($css, $async = false, $isLink = true) {
                // Add CSS
                $this->css[] = self::prepareCSS($css, $async, $isLink);
        }

        /**
         * Retrieve current page header CSS data.
         *
         * @return      array                           CSS data
         */
        public function getCSS() {
                // Return CSS data
                return $this->css;
        }

        /**
         * Prepare CSS HTML data.
         *
         * @param       string          $css            CSS data
         * @param       boolean         $async          True to load asynchronous (optional)
         * @param       boolean         $isLink         True when referencing a script, false when inline (optional)
         * @return      string                          CSS HTML data
         */
        public static function prepareCSS($css, $async = false, $isLink = true) {
                $asyncAttr = ($async ? ' async' : '');

                return ($isLink ? "<link href=\"{$css}\" rel=\"stylesheet\"{$asyncAttr}>\n" : "<style{$asyncAttr}>{$css}</style>\n");
        }

        /**
         * Add Javascript data to the current page header.
         *
         * @param       string          $js             Javascript data
         * @param       boolean         $async          True to load asynchronous (optional)
         * @param       boolean         $isLink         True when referencing a script, false when inline (optional)
         */
        public function addJS($js, $async = false, $isLink = true) {
                // Add Javascript
                $this->js[] = self::prepareJS($js, $async, $isLink);
        }

        /**
         * Retrieve current page header Javascript data.
         *
         * @return      array                           Javascript data
         */
        public function getJS() {
                // Return Javascript data
                return $this->js;
        }

        /**
         * Prepare Javascript HTML data.
         *
         * @param       string          $js             Javascript data
         * @param       boolean         $async          True to load asynchronous (optional)
         * @param       boolean         $isLink         True when referencing a script, false when inline (optional)
         * @return      string                          Javascript HTML data
         */
        public static function prepareJS($js, $async = false, $isLink = true) {
                $asyncAttr = ($async ? ' async' : '');

                return ($isLink ? "<script src=\"{$js}\"{$asyncAttr}></script>\n" : "<script{$asyncAttr}>{$js}</script>\n");
        }

        /**
         * Execute a header redirect.
         *
         * @param       string          $url            URL to redirect to
         */
        public static function redirect($url) {
                // Execute the redirect
                header("Location: {$url}");

                // Exit
                exit();
        }
}