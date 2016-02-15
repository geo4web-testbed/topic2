<?php
/**
 * Class GetFeatureModel - Visualization get feature model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Visualization
 * @author      Erik Nagelkerke <erik@spotzi.com>
 */
class GetFeatureModel extends ModuleModel {
        // User variable
        public $user                            = array();
        // Visualization variable
        public $visualization                   = array();

        public $featureId                       = null;
        public $layerId                         = null;

        public function validateRequestParams() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $this->user = Session::getData(REQUEST_PARAMETER_USER_NAME);
                if (!$loggedIn || !isset($this->user['UserName']) || !isset($this->user['Email']))
                        ErrorHandler::error(E_ERROR, 'This action is not allowed');

                $this->visualization  = $this->getVisualization();
                if (!isset($this->visualization[REQUEST_PARAMETER_VIZ_ID]) || !$this->visualization[REQUEST_PARAMETER_VIZ_ID] ||
                    (!$this->visualization[REQUEST_PARAMETER_MYMAP] && (!$this->visualization['map_enabled'] || !$this->visualization['edit_enabled'])))
                        ErrorHandler::error(E_ERROR, 'An invalid visualization was requested');

                $this->featureId = $this->getParam('featureId');
                $this->layerId = $this->getParam('layerId');
                if (!$this->featureId || !$this->layerId)
                        ErrorHandler::error(E_ERROR, 'No feature data was given');
        }

        public function getFeature() {
                $webserviceUrl = WEBSERVICE_URL . 'visualization/wo/map_feature';
                $webserviceParams = array('user'                => WEBSERVICE_USER,
                                          'password'            => WEBSERVICE_PASSWORD,
                                          'userName'            => $this->user['UserName'],
                                          'userKey'             => $this->user['ApiKey'],
                                          'visualizationId'     => $this->visualization[REQUEST_PARAMETER_VIZ_ID],
                                          'layerId'             => $this->layerId,
                                          'featureId'           => $this->featureId,
                                          'format'              => 'application/json');

                $result = false;
                $webserviceResult = Connectivity::runCurl(Url::buildUrl($webserviceUrl, $webserviceParams));
                if ($webserviceResult) {
                        $webserviceContents = json_decode($webserviceResult, true);

                        if (isset($webserviceContents['response']['map_feature']))
                                $result = $webserviceContents['response']['map_feature'];
                }

                return array(REQUEST_RESULT     => $result);
        }
}