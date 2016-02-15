<?php
/**
 * Class ImportModel - Import model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Import
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class ImportModel extends ModuleModel {
        // Visualization read connection specifics
        const DB_CONNECTION_VIZ_READ            = 'VizRead';
        const DB_CONNECTION_VIZ_READ_USER       = 'usrSpotziApiRead';
        const DB_CONNECTION_VIZ_READ_PASS       = 'File0pDeM@asvlakte';
        // Visualization write connection specifics
        const DB_CONNECTION_VIZ_WRITE           = 'VizWrite';
        const DB_CONNECTION_VIZ_WRITE_USER      = 'usrWriteSpotziApi';
        const DB_CONNECTION_VIZ_WRITE_PASS      = 'HetF0uteUur';
        // Visualization connection basics
        const DB_CONNECTION_VIZ_HOST            = 'db-data2';
        const DB_CONNECTION_VIZ_DB              = 'Visualization';

        // Visualization database variable
        public $vizDb                           = null;

        /**
         * Constructor.
         */
        public function __construct(&$request) {
                // Construct the parent class
                parent::__construct($request);

                // Set the Visualization database variable
                $this->vizDb = SqlServerDatabase::getInstance();
                // Connect to the Visualization database as a read-only user and as a write user
                $this->vizDb->connect(self::DB_CONNECTION_VIZ_READ, self::DB_CONNECTION_VIZ_HOST, self::DB_CONNECTION_VIZ_READ_USER,
                                      self::DB_CONNECTION_VIZ_READ_PASS, self::DB_CONNECTION_VIZ_DB);
                $this->vizDb->connect(self::DB_CONNECTION_VIZ_WRITE, self::DB_CONNECTION_VIZ_HOST, self::DB_CONNECTION_VIZ_WRITE_USER,
                                      self::DB_CONNECTION_VIZ_WRITE_PASS, self::DB_CONNECTION_VIZ_DB);
        }

        public function validateRequestParams() {

        }
}