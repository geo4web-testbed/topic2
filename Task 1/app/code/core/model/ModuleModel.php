<?php
/**
 * Class ModuleModel - module model implementation.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
abstract class ModuleModel extends AbstractModel {
        const VISUALIZATION_JSON_URL            = 'http://%s.spotzi.me/api/v2/viz/%s/viz.json';

        // Visualization JSON variable
        protected $vizJSON                      = array();

        /**
         * Constructor.
         *
         * @param       RequestModel            $request     Request model
         */
        public function __construct(&$request) {
                // Construct the parent class
                parent::__construct($request);

                // Validate the request parameters
                $this->validateRequestParams();
        }

        public abstract function validateRequestParams();

        protected function getVisualization() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $vizUrl = $this->getParam(REQUEST_PARAMETER_VIZ_URL);

                $inspectResult = array(REQUEST_PARAMETER_MYMAP  => false,
                                       'map_privacy'            => 'private',
                                       'map_privacy_users'      => '',
                                       'map_enabled'            => false,
                                       'edit_privacy'           => 'private',
                                       'edit_privacy_users'     => '',
                                       'edit_enabled'           => false,
                                       'edit_mode'              => false);
                if ($vizUrl) {
                        $sessionUser = Session::getData(REQUEST_PARAMETER_USER_NAME);

                        $inspectResult[REQUEST_PARAMETER_VIZ_URL] = $vizUrl;

                        $urlUser = substr($vizUrl, (strpos($vizUrl, '://') + 3));
                        $inspectResult[REQUEST_PARAMETER_USER_NAME] = substr($urlUser, 0, strpos($urlUser, '.'));
                        if ($loggedIn && $vizUrl !== VISUALIZATION_DEFAULT &&
                            $inspectResult[REQUEST_PARAMETER_USER_NAME] === $sessionUser['UserName']) {
                                $inspectResult[REQUEST_PARAMETER_MYMAP] = true;
                        }

                        $vizId = substr($vizUrl, (strpos($vizUrl, '/viz/') + 5));
                        $inspectResult[REQUEST_PARAMETER_VIZ_ID] = substr($vizId, 0, strpos($vizId, '/'));

                        $vizJSON = $this->getVisualizationJSON();
                        if (isset($vizJSON['map_options']) && $vizJSON['map_options']) {
                                $mapOptions = json_decode($vizJSON['map_options'], true);

                                if ($mapOptions) {
                                        if ($mapOptions['map_privacy'] !== 'private') {
                                                $restrictedMapUsers = explode(',', $mapOptions['map_privacy_users']);
                                                array_walk($restrictedMapUsers, function(&$value) {
                                                        $value = trim($value);
                                                });

                                                $mapOptions['map_enabled'] = ($inspectResult[REQUEST_PARAMETER_MYMAP] || $mapOptions['map_privacy'] === 'public' ||
                                                                              in_array($sessionUser['UserName'], $restrictedMapUsers));
                                        }
                                        if ($mapOptions['edit_privacy'] !== 'private') {
                                                $restrictedEditUsers = explode(',', $mapOptions['edit_privacy_users']);
                                                array_walk($restrictedEditUsers, function(&$value) {
                                                        $value = trim($value);
                                                });

                                                $mapOptions['edit_enabled'] = ($inspectResult[REQUEST_PARAMETER_MYMAP] || $mapOptions['edit_privacy'] === 'public' ||
                                                                               in_array($sessionUser['UserName'], $restrictedEditUsers));
                                        }

                                        $inspectResult = array_merge($inspectResult, $mapOptions);
                                }
                        } else {
                                // @todo: use visualization privacy setting
                        }
                }

                return $inspectResult;
        }

        protected function getVisualizationJSON() {
                if (!$this->vizJSON) {
                        $vizUrl = $this->getParam(REQUEST_PARAMETER_VIZ_URL);

                        if ($vizUrl) {
                                $vizJSONResult = Connectivity::runCurl($vizUrl);
                                if ($vizJSONResult) {
                                        $vizJSON = json_decode($vizJSONResult, true);

                                        if ($vizJSON) $this->vizJSON = $vizJSON;
                                }
                        }
                }

                return $this->vizJSON;
        }
}