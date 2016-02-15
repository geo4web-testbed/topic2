<?php
/**
 * Class CreateModel - Visualization create model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Visualization
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class CreateModel extends InspectModel {
        // User variable
        public $user                            = array();

        public function validateRequestParams() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $this->user = Session::getData(REQUEST_PARAMETER_USER_NAME);
                if (!$loggedIn || !isset($this->user['UserName']) || !isset($this->user['Email']))
                        ErrorHandler::error(E_ERROR, 'This action is not allowed');
        }

        public function create() {
                $webserviceUrl = WEBSERVICE_URL . 'visualization/wo/visualization';
                $webserviceParams = array('user'        => WEBSERVICE_USER,
                                          'password'    => WEBSERVICE_PASSWORD,
                                          'userName'    => $this->user['UserName'],
                                          'userKey'     => $this->user['ApiKey'],
                                          'format'      => 'application/json');

                $visualizationName = $this->getParam('createName');
                if ($visualizationName) $webserviceParams['visualizationName'] = strip_tags($visualizationName);

                $webserviceResult = Connectivity::runCurl($webserviceUrl,
                                                          array(CURLOPT_CUSTOMREQUEST   => 'POST',
                                                                CURLOPT_POSTFIELDS      => $webserviceParams));
                $result = false;
                if ($webserviceResult) {
                        $webserviceContents = json_decode($webserviceResult, true);

                        if (isset($webserviceContents['response']['visualization']))
                                $result = $webserviceContents['response']['visualization'];
                }

                if ($result === false)
                        ErrorHandler::error(E_NOTICE, "The visualization '%s' could not be created, result: %s",
                                            ($visualizationName ? $webserviceParams['visualizationName'] : ''), $webserviceResult);

                return array(REQUEST_RESULT     => $result);
        }
}