<?php
// Include the configuration file
require_once('app/config/config.php');

/**
 * Geonovum.
 * Entry class for the Geonovum service.
 *
 * @category    Geonovum
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
final class App {
        /**
         * Run the application.
         */
        public static function run() {
                // Initialize the request
                $request = new Request();

                if ($request->admin) {
                        // Execute the redirect
                        header('Location: ' . URL_DASHBOARD_ADMIN);

                        // Exit
                        exit();
                } else {
                        // Initialize and dispatch the request
                        $controller = new CoreController($request);
                        $controller->dispatch();
                }
        }
}