<?php
/**
 * Class Session - holds data from the current user session.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Session {
        /**
         * Initialize the session.
         *
         * This function, and every other function that writes session data, starts
         * and closes the session. After closing, retrieving data is still possible.
         * This is needed instead of a single start to avoid session blocks across requests.
         */
        public static function initialize() {
                // Start the session using the session parameter when needed
                if (isset($_REQUEST[REQUEST_PARAMETER_SESSION_ID]) &&
                    $_REQUEST[REQUEST_PARAMETER_SESSION_ID])
                        session_id($_REQUEST[REQUEST_PARAMETER_SESSION_ID]);

                // Start the session
                self::start();

                // Regenerate the session ID if the current request is not AJAX
                if (((!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') &&
                    (!isset($_REQUEST[REQUEST_PARAMETER_AJAX]) ||
                    !$_REQUEST[REQUEST_PARAMETER_AJAX])))
                        self::regenerateId();

                // Close the session
                self::close();
        }

        /**
         * Start or resume a session.
         */
        public static function start() {
                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        }

        /**
         * Close the current session.
         */
        public static function close() {
                session_write_close();
        }

        /**
         * Destroy the current session.
         */
        public static function destroy() {
                self::start();
                session_destroy();
        }

        /**
         * Destroy the current session and start a new one.
         */
        public static function reset() {
                self::destroy();
                self::start();
        }

        /**
         * Retrieve the current session ID.
         *
         * @return      string                          Current session ID
         */
        public static function id() {
                return session_id();
        }

        /**
         * Regenerate the current session ID.
         *
         * @param       booelean        $deleteOld      True to delete the old session
         */
        public static function regenerateId($deleteOld = false) {
                session_regenerate_id($deleteOld);
        }

        /**
         * Retrieve the current session name.
         *
         * @return      string                          Current session name
         */
        public static function name() {
                return session_name();
        }

        /**
         * Check session data presence.
         *
         * @param       mixed           $key            Key to check (optional)
         * @return      mixed                           True when present, false otherwise
         */
        public static function hasData($key = null) {
                return (is_null($key) ? !empty($_SESSION) : isset($_SESSION[$key]));
        }

        /**
         * Retrieve session data.
         *
         * @param       mixed           $key            When set, the requested key value is returned (optional)
         * @return      mixed                           Data when present, false otherwise
         */
        public static function getData($key = null) {
                // Return all data when no key is set
                if (is_null($key)) return $_SESSION;

                // Return empty if the requested key is not present
                if (!isset($_SESSION[$key])) return null;

                // Return key data
                return $_SESSION[$key];
        }

        /**
         * Set session data.
         *
         * @param       mixed           $key            Data key
         * @param       mixed           $value          Data value
         * @return      mixed                           Data
         */
        public static function setData($key, $value) {
                // Resume the session
                self::start();

                // Link data to the given key
                $_SESSION[$key] = $value;

                // Close the session
                self::close();

                // Return data
                return $value;
        }

        /**
         * Add session data.
         *
         * @param       mixed           $key            Data key
         * @param       mixed           $value          Data value
         * @return      mixed                           Data
         */
        public static function addData($key, $value) {
                // Resume the session
                self::start();

                // Add data to the given key
                $_SESSION[$key][] = $value;

                // Close the session
                self::close();

                // Return data
                return $value;
        }

        /**
         * Clear session data.
         *
         * @param       mixed           $key            Key to clear data from (optional)
         */
        public static function clearData($key = null) {
                // Resume the session
                self::start();

                if (is_null($key)) {
                        // Empty session data
                        $_SESSION = array();
                } elseif (isset($_SESSION[$key])) {
                        // Remove the given key from session data
                        unset($_SESSION[$key]);
                }

                // Close the session
                self::close();
        }

        /**
         * Retrieve session messages.
         *
         * @param       mixed           $type           When set, messages of the requested type are returned (optional)
         * @return      mixed                           Messages when present, false otherwise
         */
        public static function getMessages($type = null) {
                // Return all messages when no type is set
                if (is_null($type) && isset($_SESSION['messages'])) return $_SESSION['messages'];

                // Return false if the requested type is not present
                if (!isset($_SESSION['messages'][$type])) return array();

                // Return type messages
                return $_SESSION['messages'][$type];
        }

        /**
         * Add session message.
         *
         * @param       mixed           $type           Message type
         * @param       mixed           $value          Message
         */
        public static function addMessage($type, $value) {
                // Resume the session
                self::start();

                // Create type if the requested type is not present
                if (!isset($_SESSION['messages'][$type])) $_SESSION['messages'][$type] = array();

                // Add message to the given type
                $_SESSION['messages'][$type][] = $value;

                // Close the session
                self::close();

                // Return message
                return $value;
        }

        /**
         * Clear session messages.
         *
         * @param       mixed           $type           Type to clear messages for (optional)
         */
        public static function clearMessages($type = null) {
                // Resume the session
                self::start();

                if (is_null($type)) {
                        // Empty session messages
                        $_SESSION['messages'] = array();
                } elseif (isset($_SESSION['messages'][$type])) {
                        // Remove the given type from session messages
                        unset($_SESSION['messages'][$type]);
                }

                // Close the session
                self::close();
        }
}

Session::initialize();