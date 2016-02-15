<?php
/**
 * Class AbstractDatabase - generic database adapter.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
abstract class AbstractDatabase {
        const NULL_STRING                       = 'NULL';

        // Select settings
        // Constant used to select all available table fields
        const SELECT_FIELDS_ALL                 = '*';
        // Order direction variables
        const SELECT_ORDER_DIR_ASC              = 'ASC';
        const SELECT_ORDER_DIR_DESC             = 'DESC';

        // Schemas
        const SCHEMA_TABLE                      = 'TABLES';
        const SCHEMA_VIEW                       = 'VIEWS';

        const OP_COMP_GT                        = '$gt';        // Greater than
        const OP_COMP_GTE                       = '$gte';       // Greater than or equal to
        const OP_COMP_IN                        = '$in';        // In
        const OP_COMP_LT                        = '$lt';        // Less than
        const OP_COMP_LTE                       = '$lte';       // Less than or equal to
        const OP_COMP_NE                        = '$ne';        // Not equal
        const OP_COMP_NIN                       = '$nin';       // Not in

        const OP_LOG_AND                        = '$and';       // Where and
        const OP_LOG_NOR                        = '$nor';       // Where nor
        const OP_LOG_NOT                        = '$not';       // Where not
        const OP_LOG_OR                         = '$or';        // Where or

        const OP_UP_SET                         = '$set';       // Update set

        // Instances
        private static $instances               = array();

        // Active database connections
        protected $connections                  = array();

        // Connection persistence
        protected $persistConnections           = false;

        /**
         * Constructor.
         */
        private function __construct() {}

        /**
         * Clone.
         */
        private function __clone() {}

        /**
         * Retrieve the database adapter instance.
         *
         * @return      mixed                           Database adapter instance
         */
        public static function getInstance() {
                $class = get_called_class();
                if (!isset(self::$instances[$class])) self::$instances[$class] = new $class();

                return self::$instances[$class];
        }

        /**
         * Create a new database connection.
         *
         * @param       string          $connectionName Name of the new connection
         * @param       string          $dbHost         Database host
         * @param       string          $dbUser         Database user
         * @param       string          $dbPass         Database password
         * @param       string          $dbName         Database name
         * @param       string          $dbPort         Database port (optional)
         * @return      boolean                         True when successful
         */
        public abstract function connect($connectionName, $dbHost, $dbUser, $dbPass, $dbName, $dbPort = null);

        /**
         * Close an existing connection.
         *
         * @param       string          $connectionName Name of the connection to close
         * @return      boolean                         True on success
         */
        public function close($connectionName) {
                // Connection not present so we're all good
                if (!isset($this->connections[$connectionName])) return true;

                // Close connection
                unset($this->connections[$connectionName]);

                // Return successful
                return true;
        }

        /**
         * Retrieve an existing database connection.
         *
         * @param       string          $connectionName Name of the connection to retrieve
         * @return      boolean                         Connection when present, false otherwise
         */
        public function getConnection($connectionName) {
                // An unknown connection was requested, throw an exception
                if (!isset($this->connections[$connectionName])) $this->error('An unknown data source was requested');

                // Return the connection
                return $this->connections[$connectionName];
        }

        /**
         * Handle a database error.
         *
         * @param       string          $message        Error Message
         */
        public function error($message) {
                $message = '<pre>' . print_r($message, true) . '</pre>';
                ErrorHandler::error(E_ERROR, 3, (debugMode() ? $message : 'Invalid data source response'));
        }

        /**
         * Return the ID of the last inserted record of the given connection.
         *
         * @param       string          $connectionName Name of the connection to use
         * @return      int                             ID of the last inserted record
         */
        public function getLastInsertId($connectionName) {
                // Retrieve the requested database connection
                $connection = ($connectionName instanceof PDO ? $connectionName : $this->getConnection($connectionName));

                // Return the ID of the last inserted record
                return $connection->lastInsertId();
        }

        /**
         * Set a connection attribute.
         *
         * @param       string          $connectionName Name of the connection to use
         * @return      boolean                         True when set, false otherwise
         */
        public function setAttribute($connectionName, $attribute, $value) {
                // Retrieve the requested database connection
                $connection = ($connectionName instanceof PDO ? $connectionName : $this->getConnection($connectionName));

                // Return the result of the attribute set
                return $connection->setAttribute($attribute, $value);
        }

        /**
         * Execute a database statement.
         * See http://php.net/manual/en/pdostatement.execute.php
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $query          Query string
         * @param       array           $params         Input parameters (optional)
         * @param       array           $outputParams   Output parameters (optional)
         * @return      mixed                           Data on success, false otherwise
         */
        public abstract function execute($connectionName, $query, $params = array(), $outputParams = array());

        /**
         * Execute a stored procedure.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $procedure      Name of the stored procedure to execute
         * @param       array           $params         Input parameters (optional)
         * @param       array           $outputParams   Output parameters (optional)
         * @return      mixed                           Data on success, false otherwise
         */
        public abstract function storedProcedure($connectionName, $procedure, $params = array(), $outputParams = array());

        /**
         * Execute a select statement.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $table          Name of the table to call
         * @param       mixed           $fields         Fields to select (optional)
         * @param       string          $where          Where clause (optional)
         * @param       array           $params         Input parameters (optional)
         * @param       string          $orderBy        Order by field (optional)
         * @param       string          $orderByDir     Order by direction (optional)
         * @param       int             $limit          Selection limit (optional)
         * @return      mixed                           Data on success, false otherwise
         */
        public abstract function select($connectionName, $table, $fields = self::SELECT_FIELDS_ALL, $where = null, $params = array(),
                                        $orderBy = null, $orderByDir = self::SELECT_ORDER_DIR_DESC, $limit = null);

        /**
         * Execute an insert statement.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $table          Name of the table to call
         * @param       array           $params         Input parameters
         * @param       int             $rowCount       Number of rows to insert (optional)
         * @return      boolean                         True on success, false otherwise
         */
        public abstract function insert($connectionName, $table, $params, $rowCount = 1);

        /**
         * Execute an update statement.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $table          Name of the table to call
         * @param       array           $params         Input parameters
         * @param       string          $where          Where clause (optional)
         * @param       array           $whereParams    Where clause parameters (optional)
         * @param       string          $orderBy        Order by field (optional)
         * @param       string          $orderByDir     Order by direction (optional)
         * @param       int             $limit          Selection limit (optional)
         * @return      boolean                         True on success, false otherwise
         */
        public abstract function update($connectionName, $table, $params, $where = null, $whereParams = array(),
                                 $orderBy = null, $orderByDir = self::SELECT_ORDER_DIR_DESC, $limit = 1);

        /**
         * Execute a delete statement.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $table          Name of the table to call
         * @param       string          $where          Where clause (optional)
         * @param       array           $params         Input parameters (optional)
         * @return      boolean                         True on success, false otherwise
         */
        public abstract function delete($connectionName, $table, $where = null, $params = array());

        /**
         * Execute a statement to check whether the given tables/views exist.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       array           $params         Input parameters
         * @param       string          $schema         Information schema (tables/views)
         * @return      mixed                           Existing function count on success, false otherwise
         */
        public abstract function tableExists($connectionName, $params, $schema = self::SCHEMA_TABLE);

        /**
         * Prepare a statement where clause.
         *
         * @param       array           $params         Where clause parameters
         * @return      array                           Prepared where clause
         */
        protected abstract function prepareWhereValue($params, $first = true);

        /**
         * Prepare an in-value for a where clause, e.g. the part between brackets in
         * WHERE Field IN (?,?,?)
         *
         * @param       array           $params         Parameters to set up the in-value for
         * @return      string                          Prepared in-value
         */
        public function prepareWhereIn($params) {
                if (empty($params)) return '';

                if (!is_array($params)) $params = array($params);

                $in = array();
                foreach ($params as $param) {
                        $in[] = (is_array($param) && isset($param['FieldFunction']) ? $param['FieldFunction'] : '?');
                }

                return implode(',', $in);
        }

        /**
         * Prepare a statement parameter.
         *
         * @param       mixed           $param          Parameter to prepare
         * @return      array                           Prepared parameter
         */
        protected function prepareParam($param) {
                // Make sure the parameter value is a correct array field
                if (!is_array($param)) $param = array('FieldValue' => $param);

                // Prepare the PDO parameter type field if not present
                if (!isset($param['PdoParameterType'])) {
                        $pdoType = null;
                        $valueType = (isset($param['FieldValue']) ? gettype($param['FieldValue']) : '');
                        switch ($valueType) {
                                case 'boolean': // Boolean
                                        $pdoType = 'PARAM_BOOL';
                                        break;
                                case 'integer': // Integer
                                        $pdoType = 'PARAM_INT';
                                        break;
                                case 'NULL':    // Null
                                        $pdoType = 'PARAM_NULL';
                                        break;
                                case 'string':  // String
                                default:        // Double, float, array, object, resource, unknown type
                                        $pdoType = 'PARAM_STR';
                                        break;
                        }
                        $param['PdoParameterType'] = $pdoType;
                }

                // Return the prepared parameter
                return $param;
        }
}