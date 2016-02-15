<?php
/**
 * Class FinishModel - Order finishmodel.
 *
 * @category    Geonovum
 * @package     Module
 * @subpackage  Import
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class FinishModel extends ImportModel {
        public $import                          = null;

        public function validateRequestParams() {
                $this->import = file_get_contents('php://input');
        }

        public function finish() {
                $importContents = json_decode($this->import, true);

                $result = false;
                if (isset($importContents['response']['import'])) {
                        $imports = array();
                        foreach ($importContents['response']['import']['result'] as $import) {
                                $imports[$import['import']] = $import;
                        }

                        if ($imports) {
                                // Retrieve the imports
                                $where = 'UserName=? AND ImportId IN (' . $this->vizDb->prepareWhereIn(array_keys($imports)) . ')';
                                $whereParams = array_keys($imports);
                                array_unshift($whereParams, $importContents['response']['import']['user']);
                                $importsFinished = $this->vizDb->select(self::DB_CONNECTION_VIZ_READ, 'vw_AllImportsFinished',
                                                                        SqlServerDatabase::SELECT_FIELDS_ALL, $where, $whereParams,
                                                                        'DateTime', SqlServerDatabase::SELECT_ORDER_DIR_DESC);

                                $email = end($importsFinished)['Email'];
                                foreach ($importsFinished as $key => $importFinished) {
                                        $importsFinished[$key] = $importFinished['ImportId'];
                                }

                                $importsSuccess = array();
                                $importsError = array();
                                foreach ($imports as $importId => $import) {
                                        if (in_array($importId, $importsFinished))
                                                ($import['result'] ? $importsSuccess[] = $import : $importsError[] = $import);
                                }

                                if ($importsSuccess || $importsError) {
                                        // Retrieve the finish email template
                                        ob_start();
                                        require_once($this->modulePath . DIR_VIEW . 'mail/finish.php');
                                        $message = ob_get_clean();

                                        // Prepare the order mailer
                                        Mail::addMailer(EMAIL_HOST, EMAIL_PORT, EMAIL_FROM, EMAIL_FROM_PASSWORD, BRAND_PRODUCT);
                                        // Send the order complete email
                                        Mail::send($email, EMAIL_FROM, BRAND_PRODUCT . __(' - your import process is finished'),
                                                   $message, true, true);

                                        $result = true;
                                }
                        }
                }

                return $result;
        }
}