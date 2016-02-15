<?php
/**
 * Class CoreController - the central class to process report requests.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class CoreController extends AbstractController {
        /**
         * Constructor.
         */
        public function __construct(&$request) {
                // Construct the parent class
                parent::__construct($request, true);
        }

        /**
         * Dispatch the request.
         */
        public function dispatch() {
                if ($this->active) {
                        // Initialize the core model
                        $coreModel = new CoreModel($this->request);
                        // Validate the request
                        $requestValid = $coreModel->validateRequest();

                        $output = '';
                        if ($requestValid) {
                                // Retrieve the correct module controller
                                $controllerObj = $this->getRequestController();
                                // In case the controller could not be initialized, throw an exception
                                if (!$controllerObj) ErrorHandler::error(E_ERROR, 'The requested endpoint could not be initialized');

                                // In case the module is inactive or the requested method does not exist, throw an exception
                                if (!$controllerObj->active || !method_exists($controllerObj, $this->action))
                                        ErrorHandler::error(E_ERROR, "The requested action '%s' is not available", $this->action);

                                // Start an output buffer to catch request content
                                ob_start();

                                // Execute the before action when present
                                $beforeMethodName = 'before' . ucfirst($this->action);
                                if (method_exists($controllerObj, $beforeMethodName)) $controllerObj->{$beforeMethodName}();

                                // Execute the requested action
                                $controllerObj->{$this->action}();

                                // Execute the after action when present
                                $afterMethodName = 'after' . ucfirst($this->action);
                                if (method_exists($controllerObj, $afterMethodName)) $controllerObj->{$afterMethodName}();

                                // Retrieve the output buffer result
                                $result = ob_get_clean();

                                // In case the request is AJAX, output the request result directly
                                if ($this->request->ajax) {
                                        // Retrieve the header include content
                                        $header = $this->getHeaderIncludeHTML();

                                        $output = $header . $result;
                                } else {
                                        // Retrieve the output
                                        ob_start();
                                        require_once($this->modulePath . DIR_VIEW . 'index.php');
                                        $output = ob_get_clean();
                                }
                        }
                } else {
                        $output = $this->getMaintenanceView();
                }


                // Set the output character set
                header('Content-type: text/html; charset=utf-8');
//                header('Cache-Control: max-age=3600');
                // Send the output
                exit($output);
        }

        /**
         * Retrieve the requested module controller.
         *
         * @return      ModuleController                Module controller
         */
        protected function getRequestController() {
                // Create the controller object
                $requestController = $this->getController($this->module, String::prepare(DIR_APP_MODULE_CONTROLLER, ucfirst($this->module)));
                // Set the core metadata when needed
                if ($requestController) $requestController->meta['core'] = $this->meta['core'];

                // Return the controller object
                return $requestController;
        }

        /**
         * Retrieve the maintenance view.
         *
         * @return      string                          Maintenance view content
         */
        protected function getMaintenanceView() {
                return $this->getView('maintenance', DIR_BASE);
        }

        /**
         * Retrieve the page include HTML.
         *
         * @return      string                          Page include HTML
         */
        public function getHeaderIncludeHTML() {
                // Retrieve the header object
                $header = Header::getInstance();
                // Retrieve the meta, CSS and Javascript data
                $meta = $header->getMeta();
                $css = $header->getCSS();
                $js = $header->getJS();

                // Prepare the include HTML
                $includeHTML = '';
                // Include the meta, CSS and Javascript data
                if ($meta) $includeHTML .= implode('', $meta);
                if ($css) $includeHTML .= implode('', $css);
                if ($js) $includeHTML .= implode('', $js);

                // Return the complete page include HTML
                return $includeHTML;
        }

        /**
         * Retrieve the page header HTML.
         *
         * @return      string                          Page header HTML
         */
        public function getHeaderHTML() {
                // Retrieve the header HTML
                $headerHTML = $this->getView('header');
                // Retrieve and include the header include HTML
                $headerHTML .= $this->getHeaderIncludeHTML();

                // Return the complete page header HTML
                return $headerHTML;
        }
}