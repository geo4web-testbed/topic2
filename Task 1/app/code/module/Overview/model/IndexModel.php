<?php
/**
 * Class IndexModel - Overview index model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Visualization
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class IndexModel extends ModuleModel {
        public $visualization                           = null;
        public $visualizationSet                        = true;

        /**
         * Constructor.
         *
         * @param       RequestModel            $request     Request model
         */
        public function __construct(&$request) {
                // Construct the parent class
                parent::__construct($request);

                $this->prepareVisualization();
        }

        public function validateRequestParams() {

        }

        /**
         *
         * @return string
         */
        protected function prepareVisualization() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $sessionViz = Session::getData(REQUEST_PARAMETER_VIZ);
                $user = Session::getData(REQUEST_PARAMETER_USER_NAME);

                $visualization = array('communityMaps'  => array(),
                                       'myMaps'         => array());

                // Community maps
                $webserviceUrl = String::prepare('%svisualization/wo/community?user=%s&password=%s&userName=%s&userKey=%s&format=application/json',
                                                 WEBSERVICE_URL, WEBSERVICE_USER, WEBSERVICE_PASSWORD, $user['UserName'], $user['ApiKey']);

                $requestContents = Connectivity::runCurl($webserviceUrl);
                if ($requestContents) {
                        $jsonOutput = json_decode($requestContents, true);

                        if (isset($jsonOutput['response']['community'])) $visualization['communityMaps'] = $jsonOutput['response']['community'];
                }

                // My maps
                if ($loggedIn) {
                        $webserviceUrl = String::prepare('%svisualization/wo/visualization?user=%s&password=%s&userName=%s&userKey=%s&format=application/json',
                                                         WEBSERVICE_URL, WEBSERVICE_USER, WEBSERVICE_PASSWORD, $user['UserName'], $user['ApiKey']);

                        $requestContents = Connectivity::runCurl($webserviceUrl);
                        if ($requestContents) {
                                $jsonOutput = json_decode($requestContents, true);

                                if (isset($jsonOutput['response']['visualization'])) $visualization['myMaps'] = $jsonOutput['response']['visualization'];
                        }
                }

                // Set the default visualization
                $vizUrl = (isset($sessionViz[REQUEST_PARAMETER_VIZ_URL]) ? $sessionViz[REQUEST_PARAMETER_VIZ_URL] : '');

                // Fallback for the default visualization
                if (empty($visualization['defaultVisualization'])) {
                        if (!$vizUrl) $vizUrl = ($loggedIn && isset($sessionViz[REQUEST_PARAMETER_VIZ_URL]) ? $sessionViz[REQUEST_PARAMETER_VIZ_URL] : null);

                        $visualization['defaultVisualization'] = array('Url'    => ($vizUrl ? $vizUrl : VISUALIZATION_DEFAULT));

                        if (!$vizUrl) $this->visualizationSet = false;
                }

                $this->visualization = $visualization;
        }
}