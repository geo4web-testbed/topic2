<?php
/**
 * Class UserModel - User model.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Session
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class UserModel extends SessionModel {
        // User variable
        public $user                            = array();

        public function validateRequestParams() {
                $loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
                $this->user = Session::getData(REQUEST_PARAMETER_USER_NAME);
                if (!$loggedIn || !isset($this->user['UserName']) || !isset($this->user['Email']))
                        ErrorHandler::error(E_ERROR, 'This action is not allowed');

                // @todo: update so this class can deal with other update fields like email
                $passwordOld = $this->getParam('passwordOld');
                $passwordNew = $this->getParam('passwordNew');
                $passwordConfirm = $this->getParam('passwordConfirm');

                if (!$passwordOld || !$passwordNew || !$passwordConfirm)
                        ErrorHandler::error(E_ERROR, 'Invalid password input');

                $passwordHashOld = mcrypt_encrypt(MCRYPT_RIJNDAEL_192, VISUALIZATION_KEY, $passwordOld, MCRYPT_MODE_ECB);
                $this->passwordOld = urlencode($passwordHashOld);
                $passwordHashNew = mcrypt_encrypt(MCRYPT_RIJNDAEL_192, VISUALIZATION_KEY, $passwordNew, MCRYPT_MODE_ECB);
                $this->passwordNew = urlencode($passwordHashNew);
                $passwordHashConfirm = mcrypt_encrypt(MCRYPT_RIJNDAEL_192, VISUALIZATION_KEY, $passwordConfirm, MCRYPT_MODE_ECB);
                $this->passwordConfirm = urlencode($passwordHashConfirm);
        }

        public function updateUser() {
                $user = false;
                $updateError = null;
                if ($this->passwordOld !== $this->passwordNew) {
                        $webserviceUrl = String::prepare('%svisualization/wo/user', WEBSERVICE_URL);
                        $webserviceParams = array('user'                => WEBSERVICE_USER,
                                                  'password'            => WEBSERVICE_PASSWORD,
                                                  'userName'            => $this->user['UserName'],
                                                  'userKey'             => $this->user['ApiKey'],
                                                  'userPasswordOld'     => $this->passwordOld,
                                                  'userPasswordNew'     => $this->passwordNew,
                                                  'userPasswordConfirm' => $this->passwordConfirm,
                                                  'format'              => 'application/json');

                        $requestContents = Connectivity::runCurl($webserviceUrl, array(CURLOPT_CUSTOMREQUEST    => 'PUT',
                                                                                       CURLOPT_POSTFIELDS       => http_build_query($webserviceParams)));

                        if ($requestContents) {
                                $jsonOutput = json_decode($requestContents, true);

                                if (isset($jsonOutput['response']['user']) && $jsonOutput['response']['user']) {
                                        $userOutput = $jsonOutput['response']['user'];

                                        if ($userOutput['user'] && !$userOutput['error']) {
                                                $user = $userOutput['user'];
                                        } else {
                                                $updateError = (is_array($userOutput['error']) ? implode('<br>', Collection::flatten($userOutput['error'])) : $userOutput['error']);
                                        }
                                }
                        }

                        if ($user) {
                                $saltSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
                                $salt = base64_encode(mcrypt_create_iv($saltSize, MCRYPT_RAND));

                                $this->vizDb->update(self::DB_CONNECTION_VIZ_WRITE, 'VisualizationUser',
                                                     array('Password'           => hash('sha256', $salt . $this->passwordNew),
                                                           'PasswordSalt'       => $salt),
                                                     'Name=?', array($this->user['UserName']));

                                Session::setData(REQUEST_PARAMETER_USER_NAME, $user);
                        } elseif (empty($updateError)) {
                                $updateError = __('An unknown error occured while updating');
                        }
                } else {
                        $updateError = __('The new password can not be equal to the old password');
                }

                // Return the user update result
                return array(REQUEST_RESULT     => ($user ? true : false),
                             REQUEST_ERROR      => $updateError);
        }
}