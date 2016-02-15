<?php
/**
 * Class SqlServerDatabase - generic Sql Server database adapter.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class SqlServerDatabase extends AbstractDatabase {
        // The ODBC driver can be up to 50% slower
        public $odbc                            = false;

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
        public function connect($connectionName, $dbHost, $dbUser, $dbPass, $dbName, $dbPort = 1433) {
                if (!isset($this->connections[$connectionName])) {
                        // Attempt to connect using the given parameters
                        try {
                                $driver = ($this->odbc ? 'odbc:Driver={SQL Server Native Client 10.0};' : 'sqlsrv:');

                                if ($dbPort) $dbPort = ",{$dbPort}";
                                $connection = new PDO("{$driver}Server={$dbHost}{$dbPort};Database={$dbName};", $dbUser, $dbPass,
                                                      array(PDO::ATTR_PERSISTENT        => (!$this->odbc && $this->persistConnections)));
                        } catch (PDOException $e) {
                                $this->error('A required data source is unavailable: ' . $e->getMessage());
                        }

                        // Add the new connection to the collection
                        $this->connections[$connectionName] = $connection;
                }
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
        public function execute($connectionName, $query, $params = array(), $outputParams = array()) {
                // Retrieve the requested database connection
                $connection = ($connectionName instanceof PDO ? $connectionName : $this->getConnection($connectionName));

                // Prepare the statement
                $stmt = $connection->prepare($query);

                // Bind input parameters
                $paramCount = count($params);
                for ($i = 1; $i <= $paramCount; $i++) {
                        // Retrieve and prepare parameter
                        $param = $this->prepareParam($params[$i - 1]);

                        // Set field value and PDO::PARAM_<type> variables
                        $fieldValue = $param['FieldValue'];
                        $pdoType = constant("PDO::{$param['PdoParameterType']}");

                        // Bind parameter
                        $stmt->bindValue($i, $fieldValue, $pdoType);
                }

                // Bind output parameters
                $outputVars = array();
                $outputParamCount = count($outputParams);
                for ($j = 1; $j <= $outputParamCount; $j++) {
                        // Retrieve and prepare parameter
                        $param = $this->prepareParam($outputParams[$j - 1]);

                        // Set field name and PDO::PARAM_<type> variables
                        $varName = $param['FieldName'];
                        $pdoType = constant("PDO::{$param['PdoParameterType']}");

                        // Bind parameter
                        // $param['PdoParameterLength'] e.g. varchar(50) => 50
                        $stmt->bindParam(($i - 1) + $j, ${$varName}, $pdoType|PDO::PARAM_INPUT_OUTPUT, $param['PdoParameterLength']);

                        // Pass output variable by reference so output values are set automatically
                        $outputVars[$varName] = &${$varName};
                }

                // In case an error occured during statement execution, throw an exception
                if (!$stmt->execute()) $this->error($stmt->errorInfo());

                // Fetch all results
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Return an empty array if required output is empty
                if (empty($data) && empty($outputVars)) return array();

                // In case output variables are present, include them
                if (!empty($data) && !empty($outputVars)) $result = array('output' => $outputVars, 'data' => $data);
                if (!empty($data) && empty($outputVars)) $result = $data;
                if (empty($data) && !empty($outputVars)) $result = $outputVars;

                // Return the final result
                return $result;
        }

        /**
         * Execute a stored procedure.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $procedure      Name of the stored procedure to execute
         * @param       array           $params         Input parameters (optional)
         * @param       array           $outputParams   Output parameters (optional)
         * @return      mixed                           Data on success, false otherwise
         */
        public function storedProcedure($connectionName, $procedure, $params = array(), $outputParams = array()) {
                // Prepare the field list
                $procedureFieldList = array();
                foreach (array_merge($params, $outputParams) as $param) {
                        $fieldName = $param['FieldName'];
                        $procedureFieldList[] = "@{$fieldName}=?";
                }
                $procedureFields = implode(',', $procedureFieldList);
                if (!empty($procedureFields)) $procedureFields = " ({$procedureFields})";

                // Prepare the query string
                $query = "{CALL $procedure$procedureFields}";

                // Execute the statement and return its results
                return $this->execute($connectionName, $query, $params, $outputParams);
        }

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
        public function select($connectionName, $table, $fields = self::SELECT_FIELDS_ALL, $where = null, $params = array(),
                               $orderBy = array(), $orderByDir = self::SELECT_ORDER_DIR_DESC, $limit = null) {
                // Convert fields to string when needed
                if (is_array($fields)) $fields = implode(',', $fields);
                // Prepare where clause
                if (!empty($where)) $where = " WHERE {$where}";
                // Make sure the statement parameters are in array form
                if (!is_array($params)) $params = array($params);
                // Prepare the limit clause
                if (!empty($limit) && is_numeric($limit)) $limit = " TOP {$limit}";

                // Prepare the order clause
                $order = '';
                if (!empty($orderBy)) {
                        // Prepare the order fields
                        if (!is_array($orderBy)) $orderBy = array($orderBy);
                        // Prepare the order directions
                        if (!is_array($orderByDir)) $orderByDir = array($orderByDir);

                        // Prepare the order clause
                        $order = ' ORDER BY';
                        foreach ($orderBy as $index => $orderField) {
                                // Prepare the order direction
                                $orderDir = (isset($orderByDir[$index]) ? $orderByDir[$index] : reset($orderByDir));
                                // Prepare the order clause separator
                                $separator = ($index === 0 ? '' : ',');
                                // Append the order clause
                                $order .= "{$separator} {$orderField} {$orderDir}";
                        }
                }

                // Prepare the query string
                $query = "SELECT{$limit} {$fields} FROM {$table}{$where}{$order}";

                // Execute the statement and return its results
                return $this->execute($connectionName, $query, array_values($params));
        }

        /**
         * Execute an insert statement.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $table          Name of the table to call
         * @param       array           $params         Input parameters
         * @param       int             $rowCount       Number of rows to insert (optional)
         * @return      boolean                         True on success, false otherwise
         */
        public function insert($connectionName, $table, $params, $rowCount = 1) {
                // Make sure the statement parameters are in array form
                if (empty($params) || !is_array($params))
                        $this->error('Invalid insert parameters found, expecting an array of key-value pairs');

                // Prepare the value string
                $query = '';
                // Determine whether we are inserting multiple rows
                if ($rowCount > 1) {
                        // In case of an invalid parameter count, throw an exception
                        if ($rowCount !== count($params)) $this->error('Invalid insert row count');

                        // Prepare the values clause
                        foreach ($params as $fieldParams) {
                                $fields = implode(',', array_keys($fieldParams));

                                $valueString = $this->prepareWhereIn($fieldParams);

                                // Prepare the query string
                                $query .= "INSERT INTO {$table} ({$fields}) VALUES ({$valueString});";
                        }

                        // Flatten the parameter array
                        $params = Collection::flatten($params);
                } else {
                        $fields = implode(',', array_keys($params));

                        // We are inserting a single row
                        $valueString = $this->prepareWhereIn($params);

                        // Prepare the query string
                        $query = "INSERT INTO {$table} ({$fields}) VALUES ({$valueString})";
                }

                // Execute the statement and return its results
                return $this->execute($connectionName, $query, array_values($params));
        }

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
        public function update($connectionName, $table, $params, $where = null, $whereParams = array(),
                               $orderBy = null, $orderByDir = self::SELECT_ORDER_DIR_DESC, $limit = 1) {
                // Make sure the statement parameters are in array form
                if (empty($params) || !is_array($params))
                        $this->error('Invalid update parameters found, expecting an array of key-value pairs');

                // Prepare where clause
                if (!empty($where)) $where = " WHERE {$where}";
                // Make sure the where clause parameters are in array form
                if (!empty($whereParams) && !is_array($whereParams)) $whereParams = array($whereParams);
                if (!empty($orderBy) && is_numeric($limit)) {
                        $keyWord = ($where ? ' AND' : 'WHERE');
                        $where = "{$where}{$keyWord} {$orderBy} IN (SELECT TOP {$limit} {$orderBy} FROM {$table}{$where} ORDER BY {$orderBy} {$orderByDir})";
                        $whereParams = array_merge($whereParams, $whereParams);
                }

                // Prepare the fields clause
                $fieldsClause = '';
                $first = true;
                // We are updating a single row
                foreach ($params as $key => $param) {
                        $keyWord = ($first ? ' SET' : ',');

                        $paramValue = (is_array($param) && isset($param['FieldFunction']) ? $param['FieldFunction'] : '?');

                        $fieldsClause .= "{$keyWord} {$key}={$paramValue}";
                        if ($first) $first = false;
                }

                // Prepare the query string
                $query = "UPDATE {$table}{$fieldsClause}{$where}";

                // Execute the statement and return its results
                return $this->execute($connectionName, $query, array_values(array_merge($params, $whereParams)));
        }

        /**
         * Execute a delete statement.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       string          $table          Name of the table to call
         * @param       string          $where          Where clause (optional)
         * @param       array           $params         Input parameters (optional)
         * @return      boolean                         True on success, false otherwise
         */
        public function delete($connectionName, $table, $where = null, $params = array()) {
                // Prepare where clause
                if (!empty($where)) $where = " WHERE {$where}";
                // Make sure the statement parameters are in array form
                if (!is_array($params)) $params = array($params);

                // Prepare the query string
                $query = "DELETE FROM {$table}{$where}";

                // Execute the statement and return its results
                return $this->execute($connectionName, $query, array_values($params));
        }

        /**
         * Execute a statement to check whether the given tables/views exist.
         *
         * @param       string          $connectionName Name of the connection to use
         * @param       array           $params         Input parameters
         * @param       string          $schema         Information schema (tables/views)
         * @return      mixed                           Existing function count on success, false otherwise
         */
        public function tableExists($connectionName, $params, $schema = self::SCHEMA_TABLE) {
                // Make sure the statement parameters are in array form
                if (!is_array($params)) $params = array($params);

                $valueString = $this->prepareWhereIn($params);

                // Prepare the query string
                $exists = "SELECT COUNT(*) AS Count FROM INFORMATION_SCHEMA.{$schema} WHERE table_name IN({$valueString})";

                // Execute the statement
                $result = $this->execute($connectionName, $exists, array_values($params));

                // Return the result
                return ($result ? reset($result)['Count'] : false);
        }

        /**
         * Prepare a statement where clause.
         *
         * @param       array           $params         Where clause parameters
         * @return      array                           Prepared where clause
         */
        protected function prepareWhereValue($params, $first = true) {
                $stmtClause = '';
                $stmtClauseParams = array();

                if ($params) {
                        if ($first) $stmtClause = ' WHERE';

                        foreach ($params as $whereIndex => $whereField) {
                                $whereKeyword = ' ';
                                if (!$first)
                                        $whereKeyword .= ($whereIndex === self::OP_LOG_OR ? ' OR' : ' AND');

                                $indexIsOp = ($whereIndex === self::OP_LOG_AND || $whereIndex === self::OP_LOG_OR);
                                if ($indexIsOp) {
                                        list($whereClauseAdd, $whereClauseParamAdd) = $this->prepareWhereValue(null, $whereField, false);
                                        $stmtClause .= "({$whereClauseAdd})";
                                        $stmtClauseParams = array_merge($stmtClauseParams, $whereClauseParamAdd);
                                }

                                $whereFieldClause = ' ';
                                switch ($whereIndex) {
                                        case self::OP_COMP_GT:
                                                $whereFieldClause .= " > ?";
                                                break;
                                        case self::OP_COMP_GTE:
                                                $whereFieldClause .= " >= ?";
                                                break;
                                        case self::OP_COMP_IN:
                                                $whereFieldInClause = $this->prepareWhereIn($whereField);
                                                $whereFieldClause .= " IN ({$whereFieldInClause})";
                                                break;
                                        case self::OP_COMP_LT:
                                                $whereFieldClause .= " < ?";
                                                break;
                                        case self::OP_COMP_LTE:
                                                $whereFieldClause .= " <= ?";
                                                break;
                                        case self::OP_COMP_NE:
                                                $whereFieldClause .= ' IS NOT ?';
                                                break;
                                        case self::OP_COMP_NIN:
                                                $whereFieldInClause = $this->prepareWhereIn($whereField);
                                                $whereFieldClause .= " NOT IN ({$whereFieldInClause})";
                                                break;
                                        default:
                                                $whereFieldClause .= ' = ?';
                                }

                                if (is_null($whereField)) {
                                        $whereField = self::NULL_STRING;
                                } elseif (is_array($whereField)) {
                                        array_walk_recursive($whereField, function (&$value, $index) {
                                                if (is_null($value)) $value = self::NULL_STRING;
                                        });
                                }

                                if ($whereFieldClause) {
                                        $stmtClause .= " {$whereKeyword}({$whereIndex}{$whereFieldClause})";
                                        $stmtClauseParams[] = $whereField;
                                }
                        }
                }

                return array($stmtClause, $stmtClauseParams);
        }
}