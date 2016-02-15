<?php
/**
 * Class ModuleController - module controller implementation.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
abstract class ModuleController extends AbstractController {
        /**
         * Load the module language file.
         *
         * @param       string          $language       Language code
         * @param       string          $languagePath   Path to the language file (optional)
         * @return      boolean                         True on success, false otherwise
         */
        protected function loadLanguage($language, $languagePath = null) {
                // In case no language path is given, prepare the module language path
                if (empty($languagePath)) $languagePath = String::prepare(DIR_APP_MODULE_LANGUAGE, $this->module);

                // Load the language file and return the result
                return parent::loadLanguage($language, $languagePath);
        }

        /**
         * Load the module Model and View files by action.
         *
         * @param       string          $action         Module action to load (optional)
         */
        protected function render($action = null) {
                // In case no action is given, load the one given in the request
                if (empty($action)) $action = $this->action;

                // Load the model
                $model = $this->getModel($action);
                // In case the model could not be loaded, throw an error
                if (!$model) ErrorHandler::error(E_ERROR, 'Invalid module model');

                // Retrieve the module view path for the given action
                $viewPath = $this->getViewPath($action);
                // In case the view file does not exist, throw an error
                if (!file_exists($viewPath)) ErrorHandler::error(E_ERROR, 'Invalid module view');

                // Prepare the scope variables for easy accessibility in the view file
                // $this and $model are also be available

                // Include the view file
                require_once($viewPath);
        }

        /**
         * Load the session messages.
         */
        public function loadMessages() {
                // Retrieve the session messages
                $messages = Session::getMessages();

                // Retrieve the view path for the given action
                $messageViewPath = $this->getViewPath('messages', DIR_APP_CORE_VIEW);

                // Include the view file
                require_once($messageViewPath);

                // Clear the session messages
                Session::clearMessages();
        }
}