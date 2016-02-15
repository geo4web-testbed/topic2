<?php
/**
 * Class ValidateModel - Validate model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Session
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class ValidateModel extends ModuleModel {
        public $userName                                = null;
        public $userPassword                            = null;

        public function validateRequestParams() {
                $userName = $this->getParam(REQUEST_PARAMETER_USER_NAME);
                $userPassword = $this->getParam(REQUEST_PARAMETER_USER_PASSWORD);
                if (!$userName || !$userPassword)
                        ErrorHandler::error(E_ERROR, 'Missing user parameters');

                $this->userName = strtolower($userName);
                $password = mcrypt_encrypt(MCRYPT_RIJNDAEL_192, VISUALIZATION_KEY, $userPassword, MCRYPT_MODE_ECB);
                $this->userPassword = urlencode($password);
        }

        public function validate() {
                $webserviceUrl = String::prepare('%svisualization/wo/user?user=%s&password=%s&userName=%s&userPassword=%s&format=application/json',
                                                 WEBSERVICE_URL, WEBSERVICE_USER, WEBSERVICE_PASSWORD, $this->userName, $this->userPassword);

                $requestContents = Connectivity::runCurl($webserviceUrl);
                $validateResult = false;
                $validateError = null;
                if ($requestContents) {
                        $jsonOutput = json_decode($requestContents, true);

                        if (isset($jsonOutput['response']['user'])) {
                                $validateResult = true;

                                Session::setData(REQUEST_PARAMETER_LOGGEDIN, true);
                                Session::setData('freshLogin', true);
                                Session::setData(REQUEST_PARAMETER_USER_NAME, $jsonOutput['response']['user']);
                        }
                }

                if (!$validateResult && empty($validateError)) $validateError = __('Your user name or password is incorrect');

                // Return the validation result
                return array(REQUEST_RESULT     => $validateResult,
                             REQUEST_ERROR      => $validateError);
        }
}