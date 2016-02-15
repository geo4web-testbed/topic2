<?php
/**
 * Class InspectModel - Visualization inspect model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Visualization
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class InspectModel extends ModuleModel {
        public function validateRequestParams() {

        }

        public function inspect() {
                $inspectResult = $this->getVisualization();

                Session::setData(REQUEST_PARAMETER_VIZ, array(REQUEST_PARAMETER_MYMAP   => $inspectResult[REQUEST_PARAMETER_MYMAP],
                                                              REQUEST_PARAMETER_VIZ_URL => (isset($inspectResult[REQUEST_PARAMETER_VIZ_URL]) ? $inspectResult[REQUEST_PARAMETER_VIZ_URL] : null),
                                                              REQUEST_PARAMETER_VIZ_ID  => (isset($inspectResult[REQUEST_PARAMETER_VIZ_ID]) ? $inspectResult[REQUEST_PARAMETER_VIZ_ID] : null)));

                return array(REQUEST_RESULT     => $inspectResult);
        }
}