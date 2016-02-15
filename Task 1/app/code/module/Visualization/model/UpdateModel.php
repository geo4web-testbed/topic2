<?php
/**
 * Class UpdateModel - Visualization update model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Visualization
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class UpdateModel extends ModuleModel {
        // User variable
        public $user                            = array();
        // Visualization variable
        public $visualization                   = null;

        public function validateRequestParams() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $this->user = Session::getData(REQUEST_PARAMETER_USER_NAME);
                if (!$loggedIn || !isset($this->user['UserName']) || !isset($this->user['Email']))
                        ErrorHandler::error(E_ERROR, 'This action is not allowed');

                $this->visualization = $this->getVisualization();
                if (!$this->visualization)
                        ErrorHandler::error(E_ERROR, 'An invalid visualization was requested');

                if (!$this->visualization[REQUEST_PARAMETER_MYMAP])
                        ErrorHandler::error(E_ERROR, 'Only My Maps are allowed');
        }

        public function update() {
                $result = false;
                if (isset($this->visualization[REQUEST_PARAMETER_VIZ_ID])) {
                        $mapPrivacy = $this->getParam('mapPrivacy');
                        $mapPrivacyUsers = $this->getParam('mapPrivacyUsers');
                        $editPrivacy = $this->getParam('editPrivacy');
                        $editPrivacyUsers = $this->getParam('editPrivacyUsers');
                        $editMode = ($this->getParam('editMode') === 'on');

                        $apiUrl = String::prepare('http://%s.spotzi.me/api/v1/viz/%s?api_key=%s', $this->user['UserName'],
                                                  $this->visualization[REQUEST_PARAMETER_VIZ_ID], $this->user['ApiKey']);
                        $apiParams = array('map_options'        => json_encode(array('map_privacy'              => $mapPrivacy,
                                                                                     'map_privacy_users'        => $mapPrivacyUsers,
                                                                                     'edit_privacy'             => $editPrivacy,
                                                                                     'edit_privacy_users'       => ($editPrivacyUsers ? $editPrivacyUsers : $mapPrivacyUsers),
                                                                                     'edit_mode'                => $editMode)));

                        Connectivity::runCurl($apiUrl, array(CURLOPT_CUSTOMREQUEST      => 'PUT',
                                                             CURLOPT_HTTPHEADER         => array('Content-Type: application/json'),
                                                             CURLOPT_POSTFIELDS         => json_encode($apiParams)));

                        $httpCode = Connectivity::getCurlInfo(CURLINFO_HTTP_CODE);
                        $result = ($httpCode === 200);
                }

                // Return the update result
                return array(REQUEST_RESULT     => $result);
        }
}