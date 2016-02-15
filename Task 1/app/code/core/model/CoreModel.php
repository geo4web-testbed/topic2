<?php
/**
 * Class CoreModel - offers core functions.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class CoreModel extends AbstractModel {
        /**
         * Validate a request.
         *
         * @return      boolean                         True on success
         */
        public function validateRequest() {
                // In case of a missing request, throw an exception
                if (empty($this->request)) ErrorHandler::error(E_ERROR, 'No request object was found for validation');

                // Validate the endpoint
                $service = $this->validateEndpoint();
                // In case of an invalid endpoint, throw an exception
                if (!$service) ErrorHandler::error(E_ERROR, 'Invalid endpoint specified');

                $this->setLocale(REQUEST_LOCALE_DEFAULT);

                if (Session::getData(REQUEST_PARAMETER_LOGGEDIN)) {
                        $user = Session::getData(REQUEST_PARAMETER_USER_NAME);

                        if (!isset($user['UserName'])) $this->clearSession();

                        $this->setParam('freshLogin', (boolean) Session::getData('freshLogin'));
                        Session::clearData('freshLogin');
                }

                // Return the validation result
                return true;
        }

        /**
         * Validate a requested endpoint.
         *
         * @return      mixed                           Endpoint record on success, false otherwise
         */
        protected function validateEndpoint() {
                // The method call is invalid if we're missing parameters
                if (empty($this->module) || empty($this->action))
                        return false;

                $physicalControllerPath = String::prepare(DIR_APP_MODULE_CONTROLLER, ucfirst($this->module)) .
                                                          ucfirst($this->module) . 'Controller.php';

                return file_exists($physicalControllerPath);
        }
}