<?php
/**
 * Class VisualizationModel - Import visualization model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Import
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class VisualizationModel extends ImportModel {
        // User variable
        public $user                            = array();

        public function validateRequestParams() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $this->user = Session::getData(REQUEST_PARAMETER_USER_NAME);
                if (!$loggedIn || !isset($this->user['UserName']) || !isset($this->user['Email']))
                        ErrorHandler::error(E_ERROR, 'This action is not allowed');
        }

        public function import() {
                // Set the script execution settings
                $importSize = String::formatBytes(VISUALIZATION_IMPORT_SIZE, 'mB');
                setExecutionSettings($importSize + 256);

                $result = false;
                $error = null;
                $fileName = File::handleUpload(DIR_TEMP, 'importFile', null, array(), VISUALIZATION_IMPORT_SIZE);
                if ($fileName) {
                        $fileInfo = pathinfo($fileName);
                        $fileNameNew = str_replace(' ', '_', substr(strtolower($fileInfo['filename']), 0, 22)) . '_' . Date::format('now', 'Ymd_His') . '.' . $fileInfo['extension'];

                        $destinationDir = '\\\\db-images\data.spotzi.com\import\\' . $this->user['UserName'];
                        if (!is_dir($destinationDir)) mkdir($destinationDir);
                        $destination = $destinationDir . '\\' . $fileNameNew;

                        if (is_dir($destinationDir) && copy(DIR_TEMP . $fileName, $destination)) {
                                $importName = $this->getParam('importName');
                                if (!$importName) $importName = ucwords(substr(str_replace('_', ' ', $fileName), 0, strrpos($fileName, '.')));
                                $dataUrl = 'http://data.spotzi.com/import/' . $this->user['UserName'] . '/' . $fileNameNew;

                                $this->vizDb->insert(self::DB_CONNECTION_VIZ_WRITE, 'VisualizationImport',
                                                     array('Service'            => 'geonovum',
                                                           'UserName'           => $this->user['UserName'],
                                                           'Email'              => $this->user['Email'],
                                                           'Name'               => $importName,
                                                           'DataUrl'            => $dataUrl,
                                                           'DebugImport'        => debugMode()));

                                $webserviceUrl = String::prepare('%svisualization/wo/import?user=%s&password=%s&userName=%s&userKey=%s&callback=%s&format=application/json',
                                                                 WEBSERVICE_URL, WEBSERVICE_USER, WEBSERVICE_PASSWORD, $this->user['UserName'],
                                                                 $this->user['ApiKey'], Url::buildPlatformURL(false, 'import', 'import', 'finish'));
                                Connectivity::runCurlAsync($webserviceUrl);

                                $result = true;
                        } else {
                                $error = __('An error occured while preparing the file');
                        }

                        File::delete(DIR_TEMP . $fileName);
                } else {
                        $error = __('An error occured while uploading the file');
                }

                if ($result === false)
                        ErrorHandler::error(E_NOTICE, "The import failed, file name: %s\nerror: %s", $fileName, $error);

                return array(REQUEST_RESULT     => $result,
                             REQUEST_ERROR      => $error);
        }
}