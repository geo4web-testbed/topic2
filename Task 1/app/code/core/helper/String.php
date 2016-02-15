<?php
/**
 * Class String - offers functionality related to strings.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class String {
        // List of available file size units
        public static $units                    = array('B', 'kB', 'mB', 'gB', 'tB');

        /**
         * Check whether the given string starts with the given value.
         *
         * @param       string          $string         String to check
         * @param       mixed           $needles        Value(s) to check on
         * @return      boolean                         True when string starts with value, false otherwise
         */
        public static function startsWith($string, $needles) {
                if (!is_array($needles)) $needles = array($needles);

                foreach ($needles as $needle) {
                        if (substr($string, 0, strlen($needle)) === $needle) return true;
                }

                return false;
        }

        /**
         * Check whether the given string ends with the given value.
         *
         * @param       string          $string         String to check
         * @param       mixed           $needles        Value(s) to check on
         * @return      boolean                         True when string ends with value, false otherwise
         */
        public static function endsWith($string, $needles) {
                if (!is_array($needles)) $needles = array($needles);

                foreach ($needles as $needle) {
                        if (substr($string, -strlen($needle)) === $needle) return true;
                }

                return false;
        }

        /**
         * Prepare a string using a set of arguments.
         *
         * @return      string                          Result string
         */
        public static function prepare() {
                // Retrieve the method arguments
                $args = func_get_args();

                // Apply method arguments to the text
                $resultText = call_user_func_array('sprintf', $args);

                // Trim and return the result text
                return trim($resultText);
        }

        /**
         * Prepare a string using a set of arguments.
         *
         * @param       string          $string         String to summarize
         * @param       int             $cutOff         String summary cutoff point
         * @param       string          $cutOffSuffix   String suffix to append after the cutoff
         * @return      string                          Summarized string
         */
        public static function summarize($string, $cutOff = 60, $cutOffSuffix = '...') {
                if (!is_string($string) || strlen($string) <= $cutOff) return $string;

                $wrapped = wordwrap($string, $cutOff, '<br>');
                $summaryParts = explode('<br>', $wrapped);

                return reset($summaryParts) . $cutOffSuffix;
        }

        /**
         * Get the substring between two values in a string.
         *
         * @param       string          $string         String to search in
         * @param       string          $start          String to start the search with
         * @param       string          $end            String to end the search with
         * @param       boolean         $caseSensitive  True to perform a case sensitive search (optional)
         * @return      string                          Result string
         */
        public static function between($string, $start = '', $end = '', $caseSensitive = false) {
                $functionName = ($caseSensitive ? 'strpos' : 'stripos');

                $stringStart = $functionName($string, $start) + strlen($start);
                $result = substr($string, $stringStart, strlen($string));
                $stringEnd = $functionName($result, $end);
                if ($stringEnd === 0) $stringEnd = strlen($result);

                return substr($result, 0, $stringEnd);
        }

        /**
         * Convert a camel cased string to human readable form.
         *
         * @param       string          $string         String to process
         * @param       boolean         $ucFirst        True to upper case the first string character (optional)
         * @return      string                          Result string
         */
        public static function fromCamelCase($string, $ucFirst = true) {
                $result = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]|[0-9]{1,}/', ' $0', $string);

                return ($ucFirst ? ucfirst($result) : $result);
        }

        /**
         * Convert a value to boolean.
         *
         * @param       string          $string         String to convert to boolean
         * @return      boolean                         Boolean
         */
        public static function toBoolean($string) {
                return (($string === 'true' || $string == 1) ? true : false);
        }

        /**
         * Prepare a random string of the given length.
         *
         * @param       int             $length         Desired string length
         * @return      string                          Random string
         */
        public static function random($length) {
                return substr(str_shuffle(md5(microtime())), 0, $length);
        }

        /**
         * Calculate the approximate file size from a string.
         *
         * @param       string          $string         String to calculate the file size for
         * @param       string          $unit           File size unit: B, kB, mB, gB or tB (optional)
         * @param       int             $precision      Decimal precision (optional)
         * @return      int                             File size
         */
        public static function calculateBytes($string, $unit = 'B', $precision = 2) {
                // Calculate and format the amount of string bytes
                return self::formatBytes(strlen($string), $unit, $precision);
        }

        /**
         * Format the byte count using the requested unit.
         *
         * @param       int             $bytes          Byte count
         * @param       string          $unit           File size unit: B, kB, mB, gB or tB (optional)
         * @param       int             $precision      Decimal precision (optional)
         * @return      int                             File size
         */
        public static function formatBytes($bytes, $unit = 'kB', $precision = 2) {
                // Determine the exponent for the requested unit
                $pow = array_search($unit, self::$units);
                if (!$pow) $pow = 0;

                // Format and return the byte count using the requested unit
                return round(($bytes / pow(1024, $pow)), $precision);
        }

        /**
         * Returns the width in pixels of the given text in the given TrueType font.
         *
         * @param       int             $fontSize       Font size
         * @param       int             $textAngle      Text angle in degrees
         * @param       string          $fontPath       Relative path to the TTF font file
         * @param       string          $text           Text
         * @return      int                             Text width in pixels
         */
        public static function getTTFTextWidth($fontSize, $textAngle, $fontPath, $text) {
                // Retrieve the text box dimensions
                $textBox = imagettfbbox($fontSize, $textAngle, $fontPath, $text);

                // Calculate and return the text width
                return ($textBox[2] - $textBox[0]);
        }

        /**
         * Returns the height in pixels of the given text in the given TrueType font.
         *
         * @param       int             $fontSize       Font size
         * @param       int             $textAngle      Text angle in degrees
         * @param       string          $fontPath       Relative path to the TTF font file
         * @param       string          $text           Text
         * @return      int                             Text height in pixels
         */
        public static function getTTFTextHeight($fontSize, $textAngle, $fontPath, $text) {
                // Retrieve the text box dimensions
                $textBox = imagettfbbox($fontSize, $textAngle, $fontPath, $text);

                // Calculate and return the text height
                return ($textBox[1] - $textBox[7]);
        }

        /**
         * Replace block quote tags (e.g. [tag]) in the given string by values from the given array.
         *
         * @param       string          $string         String to replace tags in
         * @param       array           $fields         Array containing keys and values
         * @return      string                          Resulting string
         */
        public static function replaceStringTags($string, $fields) {
                // Find all tags in the string
                preg_match_all('/\[[\w ]+?\]/', $string, $tags);

                // Replace each tag by its value
                foreach ($tags[0] as $tag) {
                        // Prepare the tag name
                        $tagName = substr($tag, 1, -1);

                        // Prepare the tag value
                        $replace = (isset($fields[$tagName]) && !empty($fields[$tagName])) ?
                                $fields[$tagName] : '';

                        // Replace the tag by its value
                        $string = str_replace($tag, $replace, $string);
                }

                // Return the result string
                return $string;
        }
}