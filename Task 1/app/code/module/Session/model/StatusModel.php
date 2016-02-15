<?php
/**
 * Class StatusModel - Status model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Session
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class StatusModel extends ModuleModel {
        public function validateRequestParams() {

        }

        public function status() {
                $sessionValid = (Session::getData(REQUEST_PARAMETER_LOGGEDIN) && Session::getData(REQUEST_PARAMETER_USER_NAME));

                // Return the status result
                return array(REQUEST_RESULT     => $sessionValid);
        }
}