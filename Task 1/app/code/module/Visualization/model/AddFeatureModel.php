<?php
/**
 * Class AddFeatureModel - Visualization add feature model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Visualization
 * @author      Erik Nagelkerke <erik@spotzi.com>
 */
class AddFeatureModel extends ModuleModel {
        // User variable
        public $user                            = array();
        // Visualization variable
        public $visualization                   = null;

        public $action                          = null;
        public $featureId                       = null;
        public $layerId                         = null;
        public $the_geom                        = null;
        public $geom_type                       = null;
        public $featureStyle                    = null;

        public function validateRequestParams() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $this->user = Session::getData(REQUEST_PARAMETER_USER_NAME);
                if (!$loggedIn || !isset($this->user['UserName']) || !isset($this->user['Email']))
                        ErrorHandler::error(E_ERROR, 'This action is not allowed');

                $this->visualization  = $this->getVisualization();
                if (!isset($this->visualization[REQUEST_PARAMETER_VIZ_ID]) || !$this->visualization[REQUEST_PARAMETER_VIZ_ID] ||
                    (!$this->visualization[REQUEST_PARAMETER_MYMAP] && (!$this->visualization['map_enabled'] || !$this->visualization['edit_enabled'])))
                        ErrorHandler::error(E_ERROR, 'An invalid visualization was requested');

                $this->action = ($this->getParam('featureAction') ? $this->getParam('featureAction') : null);
                $this->featureId = ($this->getParam('featureId') ? (int) $this->getParam('featureId') : null);
                $this->layerId = ($this->getParam('layerId') ? $this->getParam('layerId') : null);
                $this->the_geom = ($this->getParam('the_geom') ? (array) json_decode($this->getParam('the_geom')) : null);
                $this->geom_type = (in_array($this->getParam('geom_type'), [EDITOR_POINT, EDITOR_LINE, EDITOR_POLYGON]) ? $this->getParam('geom_type') : null);
                $this->featureStyle = ($this->getParam('featureStyle') ? $this->getParam('featureStyle') : []);

                switch ($this->action) {
                        case EDITOR_ACTION_NEW_FEATURE:
                                if (!$this->the_geom || !$this->geom_type || !$this->featureStyle || !$this->getParam('name') || strcmp($this->getParam('name'), '') === 0)
                                        ErrorHandler::error(E_ERROR, 'Feature data missing: expected the_geom, geom_type, featureStyle and name');
                                break;
                        case EDITOR_ACTION_EDIT_DATA:
                                if (!$this->featureId || !$this->layerId || !$this->geom_type || !$this->featureStyle || !$this->getParam('name') || strcmp($this->getParam('name'), '') === 0)
                                        ErrorHandler::error(E_ERROR, 'Feature data missing: expected featureId, layerId, geom_type, featureStyle and name');
                                break;
                        case EDITOR_ACTION_EDIT_GEOM:
                                if (!$this->featureId || !$this->layerId || !$this->the_geom || !$this->geom_type)
                                        ErrorHandler::error(E_ERROR, 'Feature data missing: expected featureId, layerId, the_geom and geom_type');
                                break;
                        case EDITOR_ACTION_DELETE:
                                if (!$this->featureId || !$this->layerId)
                                        ErrorHandler::error(E_ERROR, 'Feature data missing: expected featureId and layerId');
                                break;
                        default:
                                ErrorHandler::error(E_ERROR, String::prepare("Action '%s' not permitted", $this->action));
                }
        }

        public function addFeature() {
                $this->featureArray = [
                        'visualizationId'   => $this->visualization[REQUEST_PARAMETER_VIZ_ID],
                        'featureAction'     => $this->action
                ];

                switch ($this->action) {
                        case EDITOR_ACTION_NEW_FEATURE:
                                $this->featureArray['the_geom'] = $this->the_geom;
                                $this->featureArray['geom_type'] = $this->geom_type;
                                $this->featureArray['featureStyle'] = $this->featureStyle;
                                break;
                        case EDITOR_ACTION_EDIT_DATA:
                                $this->featureArray['featureId'] = $this->featureId;
                                $this->featureArray['layerId'] = $this->layerId;
                                $this->featureArray['geom_type'] = $this->geom_type;
                                $this->featureArray['featureStyle'] = $this->featureStyle;
                                break;
                        case EDITOR_ACTION_EDIT_GEOM:
                                $this->featureArray['featureId'] = $this->featureId;
                                $this->featureArray['layerId'] = $this->layerId;
                                $this->featureArray['the_geom'] = $this->the_geom;
                                $this->featureArray['geom_type'] = $this->geom_type;
                                break;
                        case EDITOR_ACTION_DELETE:
                                $this->featureArray['featureId'] = $this->featureId;
                                $this->featureArray['layerId'] = $this->layerId;
                                break;
                }

                if (in_array($this->action, [EDITOR_ACTION_NEW_FEATURE, EDITOR_ACTION_EDIT_DATA])) {
                        $fileName = File::handleUpload(DIR_TEMP, 'imageurl', null, array(), 26214400); //25 MB
                        $imageurl = '';
                        if ($fileName) {
                                if (exif_imagetype(DIR_TEMP . $fileName)) {
                                        $fileNameNew = Date::format('now', 'YmdHis') . '_' . str_replace(' ', '_', $fileName);

                                        $destinationDir = '\\\\db-images\images.spotzi.com\mapbuilder\\users\\' . $this->user['UserName'];
                                        if (!is_dir($destinationDir)) mkdir($destinationDir);
                                        $destination = $destinationDir . '\\' . $fileNameNew;

                                        if (is_dir($destinationDir) && copy(DIR_TEMP . $fileName, $destination)) {
                                                $importName = substr(str_replace('_', ' ', $fileName), 0, strrpos($fileName, '.'));
                                                $imageurl = 'http://images.spotzi.com/mapbuilder/users/' . $this->user['UserName'] . '/' . $fileNameNew;
                                        }
                                        File::delete(DIR_TEMP . $fileName);
                                } else {
                                        File::delete(DIR_TEMP . $fileName);
                                        ErrorHandler::error(E_ERROR, String::prepare('%s is not an image', $fileName));
                                }
                        } else {
                                $imageurl = $this->getParam('image');
                        }
                        $this->featureArray['name'] = ($this->getParam('name') ? $this->getParam('name') : '');
                        $this->featureArray['description'] = ($this->getParam('description') ? $this->getParam('description') : '');
                        $this->featureArray['imageurl'] = ($imageurl ? $imageurl : '');
                }

                $this->feature = json_encode($this->featureArray);

                $webserviceUrl = WEBSERVICE_URL . 'visualization/wo/map_feature';
                $webserviceParams = array('user'        => WEBSERVICE_USER,
                                          'password'    => WEBSERVICE_PASSWORD,
                                          'userName'    => $this->user['UserName'],
                                          'userKey'     => $this->user['ApiKey'],
                                          'feature'     => $this->feature,
                                          'format'      => 'application/json');

                $result = false;
                $webserviceResult = Connectivity::runCurl($webserviceUrl,
                                                          array(CURLOPT_CUSTOMREQUEST   => 'POST',
                                                                CURLOPT_POSTFIELDS      => $webserviceParams));
                if ($webserviceResult) {
                        $webserviceContents = json_decode($webserviceResult, true);

                        if (isset($webserviceContents['response']['map_feature']))
                                $result = $webserviceContents['response']['map_feature'];
                }

                return array(REQUEST_RESULT     => $result);
        }
}