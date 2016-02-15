<?php
/**
 * Class InvalidateModel - Invalidate model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Session
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class InvalidateModel extends ModuleModel {
        public function validateRequestParams() {

        }

        public function invalidate() {
                $this->clearSession();

                // Return the invalidation result
                return array(REQUEST_RESULT     => true);
        }
}