<?php
/**
 * Class Collection - offers functionality related to arrays and collections.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Collection {
        /**
         * Check whether two arrays are equal.
         *
         * @param       array           $a              Array A
         * @param       array           $b              Array B
         * @return      boolean                         True when equal, false otherwise
         */
        public static function equal($a, $b) {
                return (is_array($a) && is_array($b) && array_diff($a, $b) === array_diff($b, $a));
        }

        /**
         * Check whether two arrays are identical.
         *
         * @param       array           $a              Array A
         * @param       array           $b              Array B
         * @return      boolean                         True when identical, false otherwise
         */
        public static function identical($a, $b) {
                return (is_array($a) && is_array($b) && serialize($a) === serialize($b));
        }

        /**
         * Wrap an array in a new array.
         *
         * @param       string          $key            String to use as key
         * @param       array           $array          Array to wrap
         * @return      array                           Result array
         */
        public static function wrap($key, $array) {
                return array($key => $array);
        }

        /**
         * Insert values into an array.
         *
         * @param       array           $array          Array to add values to
         * @param       mixed           $values         Values to add
         * @param       int             $offset         Value offset
         * @return      boolean                         True on success, false otherwise
         */
        public static function insert(&$array, $values, $offset) {
                array_splice($array, $offset, 0, $values);

                // Return successful
                return true;
        }

        /**
         * Diverse an array.
         * Example:
         * array([name] => array(
         *              [0] => 'name1',
         *              [1] => 'name2'
         *       ),
         *       [value] => array(
         *              [0] => 'value1',
         *              [1] => 'value2'
         *       )
         * To:
         * array([0] => array(
         *              [name] => 'name1',
         *              [value] => 'value1'
         *       ),
         *       [1] => array(
         *              [name] => 'name2',
         *              [value] => 'value2'
         *       )
         *
         * @param       array           $array          The array to diverse
         * @return      array                           The result array
         */
        public static function diverse($array) {
                // Abort if the given variable is not an array or empty
                if (!is_array($array) || empty($array)) return array();

                // Reorganize the array
                $result = array();
                foreach ($array as $key1 => $value1) {
                        foreach ($value1 as $key2 => $value2) {
                                $result[$key2][$key1] = $value2;
                        }
                }

                // Return the result
                return $result;
        }

        /**
         * Flatten an array.
         * Example:
         * array(0 => 1,
         *       1 => array('key'  => 10,
         *                  'key2' => 'value'),
         *       2 => 456
         * To:
         * array(0 => 1,
         *       1 => 10,
         *       2 => 'value',
         *       3 => 456)
         *
         * @param       array           $array          The array to flatten
         * @param       array           $result         The result array (optional)
         * @return      mixed                           The result array
         */
        public static function flatten($array, $result = null) {
                // No need to flatten if the given variable is not an array
                if (!is_array($array)) return $array;

                // Process each array entry
                foreach ($array as $key => $value) {
                        if (is_array($value)) {
                                // Use recursion to flatten sub arrays
                                $result = self::flatten($value, $result);
                        } else {
                                // Add the value to the result array
                                $result[] = $value;
                        }
                }

                // Return the result
                return $result;
        }

        /**
         * Search an array for the given key-value pair.
         *
         * @param       array           $array          The array to search
         * @param       mixed           $key            The key to search for
         * @param       mixed           $value          The value to search for
         * @param       array           $result         The result array containing matches
         */
        public static function keyValueSearch($array, $key, $value, $result = array()) {
                // Search for a key-value match
                if (isset($array[$key]) && $array[$key] === $value) $result[] = $array;

                // Use recursion to search for matches in sub arrays
                foreach ($array as $subArray) {
                        if (is_array($subArray))
                                $result = self::keyValueSearch($subArray, $key, $value, $result);
                }

                // Return the result
                return $result;
        }

        /**
         * Sort an array by the given key.
         *
         * @param       array           $array          The array to sort
         * @param       mixed           $key            The key to sort by
         * @param       boolean         $desc           True for descending, false for ascending (optional)
         * @param       boolean         $caseSensitive  True for case sensitive string sort, false otherwise (optional)
         */
        public static function keySort(&$array, $key, $desc = true, $caseSensitive = false) {
                usort($array, function($a, $b) use($key, $desc, $caseSensitive) {
                        if (isset($a[$key], $b[$key])) {
                                if (gettype($key) === 'string') {
                                        $func = ($caseSensitive ? 'strcmp' : 'strcasecmp');
                                        return ($desc ? $func($a[$key], $b[$key]) : $func($b[$key], $a[$key]));
                                } else {
                                        return ($desc ? ($a[$key] - $b[$key]) : ($b[$key] - $a[$key]));
                                }
                        }
                });
        }

        /**
         * Rename a key in an array.
         *
         * @param       array           $array          Array to rename the key in
         * @param       mixed           $oldKey         Old array key
         * @param       mixed           $newKey         New array key
         * @return      boolean                         True on success, false otherwise
         */
        public static function keyRename(&$array, $oldKey, $newKey) {
                // Return unsuccessful if the old array key is not present
                if (!isset($array[$oldKey])) return false;
                // Move the value to the new key
                $array[$newKey] = $array[$oldKey];
                // Delete the old key
                unset($array[$oldKey]);

                // Return successful
                return true;
        }

        /**
         * Format an array as CSV.
         *
         * @param       string          $array          The array to format
         * @param       string          $delimiter      Field delimiter (optional)
         * @param       string          $enclosure      Field enclosure (optional)
         * @return      string                          CSV string
         */
        public static function toCSV($array, $delimiter = ',', $enclosure = '"') {
                $result = '';

                // Prepare output when needed
                if (!empty($array)) {
                        try {
                                // Sanity check
                                if (!is_array($array)) $array = array($array);

                                // Prepare each array entry
                                $first = true;
                                foreach ($array as $line) {
                                        // We should include the field names on the first line
                                        if ($first) {
                                                // Retrieve the CSV line for the field names
                                                $result .= self::toCSVLine(self::flatten(array_keys($line)), $delimiter, $enclosure);
                                                $first = false;
                                        }
                                        // Retrieve the CSV line
                                        $result .= self::toCSVLine(self::flatten($line), $delimiter, $enclosure);
                                }
                        } catch (Exception $e) {}
                }

                // Return the result
                return $result;
        }

        /**
         * Format an array as CSV line.
         * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
         *
         * @param       array           $array          The array to format
         * @param       string          $delimiter      Field delimiter (optional)
         * @param       string          $enclosure      Field enclosure (optional)
         * @param       boolean         $encloseAll     True to enclose all fields, false to only enclose fields
         *                                              containing $delimiter, $enclosure or whitespace (optional)
         * @return      string                          CSV line
         */
        public static function toCSVLine($array, $delimiter = ',', $enclosure = '"', $encloseAll = false) {
                // Quote delimiter and enclosure for use in regular expressions
                $delimiter_esc = preg_quote($delimiter, '/');
                $enclosure_esc = preg_quote($enclosure, '/');

                if (!is_array($array)) $array = array($array);

                $result = array();
                // Prepare each field
                foreach ($array as $field) {
                        // In case the field is empty, add a NULL value
                        if (is_null($field) || $field === '') {
                                $result[] = 'NULL';
                                continue;
                        }

                        // Enclose fields containing $delimiter, $enclosure or whitespace
                        if ($encloseAll || preg_match("/(?:{$delimiter_esc}|{$enclosure_esc}|\s)/", $field)) {
                                $result[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
                        } else {
                                $result[] = $field;
                        }
                }

                // Return the result
                return implode($delimiter, $result) . PHP_EOL;
        }
}