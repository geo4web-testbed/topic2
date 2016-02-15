<?php
/**
 * Class Mail - offers functionality related to emails.
 *
 * @category    Geonovum
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class Mail {
        // Validator variable
        private static $validator               = null;
        // Mailer variable
        private static $mailers                 = array();

        /**
         * Return the validator instance.
         *
         * @return      SMTP_validateEmail              Validator instance
         */
        public static function getValidator() {
                // Lazy initialization: create the validator object at the first request
                if (empty(self::$validator)) {
                        require_once(DIR_LIB . 'SMTPValidateEmail/smtp_validateEmail.class.php');
                        // Prepare the validator object
                        $validator = new SMTP_validateEmail();
                        $validator->assumeOk = true;
//                        $validator->debug = debugMode();

                        self::$validator = $validator;
                }

                // Reset the validator
                self::resetValidator();

                // Return the validator
                return self::$validator;
        }

        /**
         * Reset the validator instance.
         */
        public static function resetValidator() {
                // Clears domains and from
                self::$validator->domains = array();
                self::$validator->from_user = 'from';
                self::$validator->from_domain = 'localhost';
        }

        /**
         * Validate an email address.
         *
         * @param       string          $mailTo         Email recipient address
         * @param       string          $mailFrom       Email sender address (optional)
         * @return      boolean                         True on success, false otherwise
         */
        public static function validate($mailTo, $mailFrom = null) {
                if (!filter_var($mailTo, FILTER_VALIDATE_EMAIL)) return false;

                if (!empty($mailFrom)) {
                        $validationResult = self::getValidator()->validate(array($mailTo), $mailFrom);

                        return (isset($validationResult[$mailTo]) && $validationResult[$mailTo]);
                }

                return true;
        }

        /**
         * Return a mailer instance.
         *
         * @param       string          $from           Email sender address
         * @return      PHPMailer                       Mailer instance
         */
        public static function getMailer($from) {
                // In case no mailer is available, return unsuccessful
                if (empty(self::$mailers[$from])) return false;

                // Reset the mailer
                self::resetMailer($from);

                // Return the mailer
                return self::$mailers[$from];
        }

        /**
         * Add a mailer instance.
         *
         * @param       string          $host           Email server host
         * @param       string          $port           Email server port
         * @param       string          $from           Email sender address
         * @param       string          $fromPassword   Email sender password
         * @param       string          $fromName       Email sender name
         */
        public static function addMailer($host, $port, $from, $fromPassword, $fromName) {
                // Lazy initialization: create the mailer object at the first request
                if (empty(self::$mailers[$from])) {
                        require_once(DIR_LIB . 'PhpMailer/class.phpmailer.php');
                        // Prepare the mailer object
                        $mailer = new PHPMailer();
                        $mailer->Charset = 'UTF-8';
                        $mailer->isSMTP();                      // Set the mailer to use SMTP
                        $mailer->SMTPAuth = true;               // Turn on SMTP authentication
                        $mailer->Host     = $host;              // Specify the mail server
                        $mailer->Port     = $port;              // Specify the mail server port
                        $mailer->Username = $from;              // SMTP username
                        $mailer->Password = $fromPassword;      // SMTP password
                        $mailer->From     = $from;              // Do NOT fake header
                        $mailer->FromName = $fromName;

                        self::$mailers[$from] = $mailer;
                }
        }

        /**
         * Reset a mailer instance.
         *
         * @param       string          $from           Email sender address
         */
        public static function resetMailer($from) {
                if (!empty(self::$mailers[$from])) {
                        // Clears addresses, CCs and BCCs
                        self::$mailers[$from]->clearAllRecipients();
                        self::$mailers[$from]->clearAttachments();
                        self::$mailers[$from]->clearCustomHeaders();
                        self::$mailers[$from]->clearReplyTos();
                }
        }

        /**
         * Send an email.
         *
         * @param       string          $mailTo         Email recipient address
         * @param       string          $mailFrom       Email sender address
         * @param       string          $subject        Email subject
         * @param       string          $message        Email message
         * @param       boolean         $isHTML         True when the email is HTML (optional)
         * @param       boolean         $addReplyTo     True to add a "Reply To" (optional)
         * @return      boolean                         True on success, false otherwise
         */
        public static function send($mailTo, $mailFrom, $subject, $message, $isHTML = true, $addReplyTo = false) {
                // Retrieve the mailer
                $mailer = self::getMailer($mailFrom);
                // In case no mailer is available, return unsuccessful
                if (!$mailer) return false;

                // Prepare the email
                $mailer->addAddress($mailTo);
                if ($addReplyTo) $mailer->addReplyTo($mailFrom);
                $mailer->Subject  = $subject;
                $mailer->Body     = $message;
                $mailer->isHTML($isHTML);

                // Send the email and return its result
                return ($mailer->send() ? true : $mailer->ErrorInfo);
        }
}