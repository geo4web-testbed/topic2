<?php
// Set the default timezone
define('TIMEZONE_DEFAULT', date_default_timezone_get());
/**
 * Class Date - offers functionality related to dates.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Date {
        /**
         * Get the default timezone.
         *
         * @return      string                          Default timezone
         */
        public static function getDefaultTimezone() {
                return TIMEZONE_DEFAULT;
        }

        /**
         * Get the default timezone offset.
         * @param       boolean         $textFormat     True to format as text, false otherwise (optional)
         * @param       string          $hourSeparator  Hour separator (optional)
         * @return      mixed                           Default timezone offset
         */
        public static function getDefaultTimezoneOffset($textFormat = false, $hourSeparator = ':') {
                return self::getTimezoneOffset($textFormat, $hourSeparator, self::getDefaultTimezone());
        }

        /**
         * Set the default timezone.
         *
         * @return      boolean                         Default timezone validity
         */
        public static function setDefaultTimezone() {
                return self::setTimeZone(TIMEZONE_DEFAULT);
        }

        /**
         * Get the current timezone.
         *
         * @return      string                          Current timezone
         */
        public static function getTimezone() {
                return date_default_timezone_get();
        }

        /**
         * Get the current timezone offset.
         *
         * @param       boolean         $textFormat     True to format as text, false otherwise (optional)
         * @param       string          $hourSeparator  Hour separator (optional)
         * @param       string          $timezone       Timezone (optional)
         * @return      mixed                           Default timezone offset
         */
        public static function getTimezoneOffset($textFormat = false, $hourSeparator = ':', $timezone = null) {
                // Retrieve the current timezone offset
                $currentTimezone = new DateTimeZone($timezone ? $timezone : self::getTimezone());
                $now = new DateTime('now', $currentTimezone);
                $offset = $now->getOffset();

                // Prepare the offset
                if ($textFormat) $offset = sprintf("%+03d{$hourSeparator}%02u", ($offset / 3600), (abs($offset) % 3600 / 60));

                return $offset;
        }

        /**
         * Set the current timezone.
         *
         * @param       string          $timezone       Current timezone
         * @return      boolean                         Current timezone validity
         */
        public static function setTimezone($timezone) {
                return date_default_timezone_set($timezone);
        }

        /**
         * Retrieve a date object.
         *
         * @param       string          $date           Date to format (optional)
         * @param       string          $timezone       Timezone (optional)
         * @return      string                          Date object
         */
        public static function get($date = 'now', $fromFormat = null, $timezone = null) {
                // Prepare the timezone
                $timezone = new DateTimeZone($timezone ? $timezone : self::getDefaultTimezone());

                // Prepare the date object
                if ($fromFormat) {
                        $dateObj = DateTime::createFromFormat($fromFormat, $date, $timezone);
                } else {
                        $isDate = ($date === 'now' || self::isValidDate($date));

                        $dateObj = new DateTime($isDate ? $date : 'now', $timezone);
                        if (!$isDate) $dateObj->setTimestamp(intval($date));
                }

                // Return the date object
                return $dateObj;
        }

        /**
         * Retrieve a date timestamp.
         *
         * @param       string          $date           Date to format (optional)
         * @param       string          $timezone       Timezone (optional)
         * @return      string                          Date object
         */
        public static function getTimestamp($date = 'now', $fromFormat = null, $timezone = null) {
                // Retrieve the date object
                $dateObj = self::get($date, $fromFormat, $timezone);

                // Return the date
                return $dateObj->getTimestamp();
        }

        /**
         * Format a date.
         *
         * @param       string          $date           Date to format (optional)
         * @param       string          $format         Date format (optional)
         * @param       string          $timezone       Timezone (optional)
         * @return      string                          Formatted date
         */
        public static function format($date = 'now', $format = 'Y-m-d H:i:s', $fromFormat = null, $timezone = null) {
                // Retrieve the date object
                $dateObj = self::get($date, $fromFormat, $timezone);

                // Format and return the date
                return $dateObj->format($format);
        }

        /**
         * Format a date using the active timezone.
         *
         * @param       string          $date           Date to format (optional)
         * @param       string          $format         Date format (optional)
         * @return      string                          Formatted date
         */
        public static function timezoneFormat($date = 'now', $format = 'Y-m-d H:i:s', $fromFormat = null) {
                return self::format($date, $format, $fromFormat, self::getTimezone());
        }

        /**
         * Check if a string is a valid date(time).
         *
         * @param       mixed           $string         Date to check
         * @return      boolean                         True if the value is a valid date, false otherwise
         */
        public static function isValidDate($string) {
                // Parse the date
                $date = date_parse($string);
                // Return the parse result
                return ($date['error_count'] == 0 && checkdate($date['month'], $date['day'], $date['year']));
        }

        /**
         * Check if the passed value is a valid timestamp.
         *
         * @param       mixed           $timestamp      Timestamp to check
         * @return      boolean                         True if the value is a valid timestamp, false otherwise
         */
        public static function isValidTimeStamp($timestamp) {
                return ((string) (int) $timestamp === (string) $timestamp)
                        && ($timestamp <= PHP_INT_MAX)
                        && ($timestamp >= ~PHP_INT_MAX);
        }
}