<?php
/**
 * Class RegisterModel - Register model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Session
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class RegisterModel extends SessionModel {
        const NEWSLETTER_API_URL                = 'http://spotzi.com/nl/wp-admin/admin-ajax.php?action=newsletters_api';
        const NEWSLETTER_API_KEY                = 'B1F8D0F11745B942B6CFE9D707BBBC28';

        const NEWSLETTER_MAILINGLIST_NL         = 3;
        const NEWSLETTER_MAILINGLIST_EN         = 4;

        public $userName                        = null;
        public $userEmail                       = null;
        public $userPassword                    = null;

        public function validateRequestParams() {
                $userName = $this->getParam(REQUEST_PARAMETER_USER_NAME);
                $userEmail = $this->getParam(REQUEST_PARAMETER_USER_EMAIL);
                $userPassword = $this->getParam(REQUEST_PARAMETER_USER_PASSWORD);
                if (!$userName || !$userEmail || !$userPassword)
                        ErrorHandler::error(E_ERROR, 'Missing user parameters');

                $this->userName = strtolower($userName);
                if (!Mail::validate($userEmail))
                        ErrorHandler::error(E_ERROR, 'An invalid email address was given');
                $this->userEmail = strtolower($userEmail);
                $password = mcrypt_encrypt(MCRYPT_RIJNDAEL_192, VISUALIZATION_KEY, $userPassword, MCRYPT_MODE_ECB);
                $this->userPassword = urlencode($password);
        }

        public function register() {
                $webserviceUrl = String::prepare('%svisualization/wo/user', WEBSERVICE_URL);
                $webserviceParams = array('user'                => WEBSERVICE_USER,
                                          'password'            => WEBSERVICE_PASSWORD,
                                          'userName'            => $this->userName,
                                          'userEmail'           => $this->userEmail,
                                          'userPassword'        => $this->userPassword,
                                          'format'              => 'application/json');

                $requestContents = Connectivity::runCurl($webserviceUrl, array(CURLOPT_CUSTOMREQUEST    => 'POST',
                                                                               CURLOPT_POSTFIELDS       => $webserviceParams));
                $user = false;
                $registerError = null;
                if ($requestContents) {
                        $jsonOutput = json_decode($requestContents, true);

                        if (isset($jsonOutput['response']['user']) && $jsonOutput['response']['user']) {
                                $userOutput = $jsonOutput['response']['user'];

                                if ($userOutput['user'] && !$userOutput['error']) {
                                        $user = $userOutput['user'];
                                } else {
                                        if (is_array($userOutput['error'])) {
                                                $userErrors = array();
                                                foreach ($userOutput['error'] as $field => $errors) {
                                                        $fieldPresent = !empty($webserviceParams[$field]);
                                                        switch ($field) {
                                                                case REQUEST_PARAMETER_USER_NAME:
                                                                        $field = __('User name');
                                                                        break;
                                                                case REQUEST_PARAMETER_USER_EMAIL:
                                                                        $field = __('Email address');
                                                                        break;
                                                                case REQUEST_PARAMETER_USER_PASSWORD:
                                                                        $field = __('Password');
                                                                        break;
                                                        }

                                                        foreach ($errors as $error) {
                                                                if ($error === 'is not present' && $fieldPresent) continue;

                                                                $userErrors[] = '<b>' . $field . '</b> ' . $error;
                                                        }
                                                }
                                                $registerError = implode('<br>', $userErrors);
                                        } else {
                                                $registerError = $userOutput['error'];
                                        }
                                }
                        }
                }

                if ($user) {
                        $saltSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
                        $salt = base64_encode(mcrypt_create_iv($saltSize, MCRYPT_RAND));

                        $this->vizDb->insert(self::DB_CONNECTION_VIZ_WRITE, 'VisualizationUser',
                                             array('Id'                 => $user['Id'],
                                                   'Name'               => $user['UserName'],
                                                   'Password'           => hash('sha256', $salt . $this->userPassword),
                                                   'PasswordSalt'       => $salt,
                                                   'Email'              => $user['Email'],
                                                   'ApiKey'             => $user['ApiKey']));

                        Session::setData(REQUEST_PARAMETER_LOGGEDIN, true);
                        Session::setData('freshLogin', true);
                        Session::setData(REQUEST_PARAMETER_USER_NAME, $user);

                        // Retrieve the register email template
                        ob_start();
                        require_once($this->modulePath . DIR_VIEW . 'mail/register.php');
                        $message = ob_get_clean();

                        // Prepare the register mailer
                        Mail::addMailer(EMAIL_HOST, EMAIL_PORT, EMAIL_FROM, EMAIL_FROM_PASSWORD, BRAND_PRODUCT);
                        // Send the register email
                        Mail::send($this->userEmail, EMAIL_FROM, __('%s - your Spotzi Mapbuilder account', BRAND_PRODUCT),
                                   $message, true, true);

                        // Add the user to the newsletter subscription list
                        $this->registerNewsletterSubscription($this->userEmail);
                } elseif (empty($registerError)) {
                        $registerError = __('An unknown error occured while registering');
                }

                // Return the register result
                return array(REQUEST_RESULT     => ($user ? true : false),
                             REQUEST_ERROR      => $registerError);
        }

        protected function registerNewsletterSubscription($email) {
                $isNL = ($this->request->locale === 'nl_NL');

                // Add the user to the newsletter subscription list
                $curlData = json_encode(array(
                        'api_method'    => 'subscriber_add',
                        'api_key'       => self::NEWSLETTER_API_KEY,
                        'api_data'      => array(
                                'email'         => $email,
                                'list_id'       => array($isNL ? self::NEWSLETTER_MAILINGLIST_NL : self::NEWSLETTER_MAILINGLIST_EN)
                        )
                ));
                Connectivity::runCurl(self::NEWSLETTER_API_URL, array(
                        CURLOPT_CUSTOMREQUEST   => 'POST',
                        CURLOPT_POSTFIELDS      => $curlData,
                        CURLOPT_HTTPHEADER      => array(
                                'Content-Type: application/json',
                                'Content-Length: ' . strlen($curlData)
                        )
                ));
        }
}