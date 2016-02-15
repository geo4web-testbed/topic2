<?php
/**
 * Class OverviewController - Overview controller.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Overview
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class OverviewController extends ModuleController {
        public function index() {
                $this->render();
        }

        public function getVisualizationInspectUrl() {
                return Url::buildPlatformURL(false, 'visualization', 'visualization', 'inspect');
        }

        public function getVisualizationFilterUrl() {
                return Url::buildPlatformURL(false, 'visualization', 'visualization', 'filter');
        }

        public function getVisualizationCreateUrl() {
                return Url::buildPlatformURL(false, 'visualization', 'visualization', 'create');
        }

        public function getVisualizationUpdateUrl() {
                return Url::buildPlatformURL(false, 'visualization', 'visualization', 'update');
        }

        public function getVisualizationDeleteUrl() {
                return Url::buildPlatformURL(false, 'visualization', 'visualization', 'delete');
        }

        public function getVisualizationPropositionUrl() {
                return Url::buildPlatformURL(false, 'visualization', 'visualization', 'proposition');
        }

        public function getVisualizationGetFeatureUrl() {
                return Url::buildPlatformURL(false, 'visualization', 'visualization', 'getfeature');
        }

        public function getVisualizationAddFeatureUrl() {
                return Url::buildPlatformURL(false, 'visualization', 'visualization', 'addfeature');
        }

        public function getImportVisualizationUrl() {
                return Url::buildPlatformURL(false, 'import', 'import', 'visualization', null,
                                             array(REQUEST_PARAMETER_AJAX       => true));
        }

        public function getSessionRegisterUrl() {
                return Url::buildPlatformURL(false, 'session', 'session', 'register');
        }

        public function getSessionValidateUrl() {
                return Url::buildPlatformURL(false, 'session', 'session', 'validate');
        }

        public function getSessionInvalidateUrl() {
                return Url::buildPlatformURL(false, 'session', 'session', 'invalidate');
        }

        public function getSessionStatusUrl() {
                return Url::buildPlatformURL(false, 'session', 'session', 'status');
        }

        public function getSessionUserUrl() {
                return Url::buildPlatformURL(false, 'session', 'session', 'user');
        }
}