<?php
/**
 * Class DeleteModel - Visualization delete model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Visualization
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class DeleteModel extends ModuleModel {
        // User variable
        public $user                            = array();
        // Visualization ID variable
        public $visualizationId                 = null;

        public function validateRequestParams() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $this->user = Session::getData(REQUEST_PARAMETER_USER_NAME);
                if (!$loggedIn || !isset($this->user['UserName']) || !isset($this->user['Email']))
                        ErrorHandler::error(E_ERROR, 'This action is not allowed');

                $this->visualizationId = $this->getParam(REQUEST_PARAMETER_VIZ_ID);
                if (!$this->visualizationId)
                        ErrorHandler::error(E_ERROR, 'An invalid visualization was requested');

                $visualization  = $this->getVisualization();
                if (!isset($visualization[REQUEST_PARAMETER_VIZ_ID]) || $this->visualizationId === $visualization[REQUEST_PARAMETER_VIZ_ID])
                        Session::clearData(REQUEST_PARAMETER_VIZ);
        }

        public function delete() {
                $webserviceUrl = WEBSERVICE_URL . 'visualization/wo/visualization';
                $webserviceParams = array('user'                => WEBSERVICE_USER,
                                          'password'            => WEBSERVICE_PASSWORD,
                                          'userName'            => $this->user['UserName'],
                                          'userKey'             => $this->user['ApiKey'],
                                          'visualizationId'     => $this->visualizationId,
                                          'format'              => 'application/json');

                $webserviceResult = Connectivity::runCurl($webserviceUrl . '?' . http_build_query($webserviceParams),
                                                          array(CURLOPT_CUSTOMREQUEST   => 'DELETE'));
                $result = false;
                if ($webserviceResult) {
                        $webserviceContents = json_decode($webserviceResult, true);

                        if (isset($webserviceContents['response']['visualization']))
                                $result = (boolean) $webserviceContents['response'];
                }

                return array(REQUEST_RESULT     => $result);
        }
}