<?php
/**
 * Class PropositionModel - Visualization proposition model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Visualization
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class PropositionModel extends ModuleModel {
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
                if (!isset($this->visualization[REQUEST_PARAMETER_VIZ_ID]) || !$this->visualization[REQUEST_PARAMETER_VIZ_ID])
                        ErrorHandler::error(E_ERROR, 'An invalid visualization was requested');

                if (!$this->visualization[REQUEST_PARAMETER_MYMAP])
                        ErrorHandler::error(E_ERROR, 'Only My Maps are allowed');
        }

        public function setPropositionLayer() {
                $propositionResult = false;
                if ($this->visualization[REQUEST_PARAMETER_MYMAP]) {
                        $vizJSON = $this->getVisualizationJSON();
                        $propositionsLayer = array();
                        if (isset($vizJSON['layers'])) {
                                $layer = end($vizJSON['layers']);

                                if (isset($layer['type']) && $layer['type'] === 'layergroup') {
                                        foreach ($layer['options']['layer_definition']['layers'] as $groupLayer) {
                                                // substr: remove the date from the end of the layer name (Ymd_His)
                                                if (isset($groupLayer['options']['layer_name']) && String::endsWith(substr($groupLayer['options']['layer_name'], 0, -16), '_propose')) {
                                                        $propositionsLayer = $groupLayer;
                                                        break;
                                                }
                                        }
                                }
                        }

                        if (!$propositionsLayer) {
                                $vizResult = Connectivity::runCurl(String::prepare('http://%s.spotzi.me/api/v1/viz/%s?api_key=%s', $this->user['UserName'],
                                                                                   $this->visualization[REQUEST_PARAMETER_VIZ_ID], $this->user['ApiKey']));
                                if ($vizResult) {
                                        $visualization = json_decode($vizResult, true);

                                        if (isset($visualization['related_tables'])) {
                                                $table = reset($visualization['related_tables']);
                                                $tableId = $table['id'];

                                                $tableResult = Connectivity::runCurl(String::prepare('http://%s.spotzi.me/api/v1/tables/%s?api_key=%s', $this->user['UserName'],
                                                                                     $tableId, $this->user['ApiKey']));
                                                if ($tableResult) {
                                                        $originalTable = json_decode($tableResult, true);

                                                        // substr: retrieve the base table name and date from the original table name (Ymd_His)
                                                        $originalName = substr($originalTable['name'], 0, -16);
                                                        $originalDate = substr($originalTable['name'], -15);
                                                        // the_geom_type: geometry, multipolygon, point, multilinestring
                                                        $createTableResult = Connectivity::runCurl(String::prepare('http://%s.spotzi.me/api/v1/tables?api_key=%s', $this->user['UserName'], $this->user['ApiKey']),
                                                                                                   array(CURLOPT_CUSTOMREQUEST  => 'POST',
                                                                                                         CURLOPT_POSTFIELDS     => array('name'                 => substr($originalName, 0, 22) . '_propose_' . $originalDate,
                                                                                                                                         'description'          => __('Update propositions for %s', $originalTable['name']),
                                                                                                                                         'tags'                 => 'update,propose,propositions'
//                                                                                                                                         ,'the_geom_type'       => reset($originalTable['geometry_types'])
                                                                                                                 )));
                                                        if ($createTableResult) {
                                                                $newTable = json_decode($createTableResult, true);
                                                                $newTableName = $newTable['name'];

                                                                Connectivity::runCurl(String::prepare('http://%s.spotzi.me/api/v1/tables/%s?api_key=%s', $this->user['UserName'],
                                                                                                                      $newTable['id'], $this->user['ApiKey']),
                                                                                                      array(CURLOPT_CUSTOMREQUEST       => 'PUT',
                                                                                                            CURLOPT_HTTPHEADER          => array('Content-Type: application/json'),
                                                                                                            CURLOPT_POSTFIELDS          => json_encode(array('privacy'  => 'PUBLIC'))));
                                                                Connectivity::closeCurl();

                                                                $columns = array(' ADD COLUMN user_id text NOT NULL',
                                                                                 ' ADD COLUMN visualization_id text NOT NULL',
                                                                                 ' ADD COLUMN column_data text',
                                                                                 ' ADD COLUMN the_geom_old geometry');
                                                                $columnQuery = "ALTER TABLE \"{$newTableName}\"" . implode(',', $columns) . ';';

                                                                $sqlResult = Connectivity::runCurl(String::prepare('http://%s.spotzi.me/api/v2/sql', $this->user['UserName']),
                                                                                                   array(CURLOPT_CUSTOMREQUEST  => 'POST',
                                                                                                         CURLOPT_POSTFIELDS     => array('q'            => $columnQuery,
                                                                                                                                         'api_key'      => $this->user['ApiKey'])));

                                                                $layerParams = array('kind'             => 'carto',
                                                                                     'order'            => 2,
                                                                                     'options'          => array('table_name'           => $newTableName,
                                                                                                                 'user_name'            => $this->user['UserName'],
                                                                                                                 'interactivity'        => 'cartodb_id',
                                                                                                                 'visible'              => false,
                                                                                                                 'style_version'        => '2.1.1',
                                                                                                                 'tile_style'           => "#{$newTableName} {
        // points
        [mapnik-geometry-type=point] {
                marker-fill: #77BBDD;
                marker-opacity: 0.5;
                marker-width: 12;
                marker-line-color: #222222;
                marker-line-width: 3;
                marker-line-opacity: 1;
                marker-placement: point;
                marker-type: ellipse;
                marker-allow-overlap: true;
        }

        //lines
        [mapnik-geometry-type=linestring] {
                line-color: #77BBDD;
                line-width: 2;
                line-opacity: 0.5;
        }

        //polygons
        [mapnik-geometry-type=polygon] {
                polygon-fill: #77BBDD;
                polygon-opacity: 0.5;
                line-opacity: 1;
                line-color: #222222;
        }
}"));

                                                                $layerCreateResult = Connectivity::runCurl(String::prepare('http://%s.spotzi.me/api/v1/maps/%s/layers?api_key=%s',
                                                                                                                           $this->user['UserName'], $visualization['map_id'], $this->user['ApiKey']),
                                                                                                           array(CURLOPT_CUSTOMREQUEST  => 'POST',
                                                                                                                 CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
                                                                                                                 CURLOPT_POSTFIELDS     => json_encode($layerParams)));

                                                                $propositionResult = (boolean) $layerCreateResult;
                                                        }
                                                }
                                        }
                                }
                        }
                }

                return array(REQUEST_RESULT     => $propositionResult);
        }
}