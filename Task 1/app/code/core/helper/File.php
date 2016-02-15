<?php
/**
 * Class File - offers functionality related to files.
 *
 * @category    Spotzi Dashboard
 * @package     Core
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */
class File {
        /**
         * Determine whether the given file exists.
         *
         * @param       string          $fileName       File name
         * @return      boolean                         True when file exists, false otherwise
         */
        public static function exists($fileName) {
                return file_exists($fileName);
        }

        /**
         * Read data from a file.
         *
         * @param       string          $fileName       File name
         * @return      mixed                           File data on success, false otherwise
         */
        public static function read($fileName) {
                if (!self::exists($fileName)) return false;

                return file_get_contents($fileName);
        }

        /**
         * Write data to a file.
         *
         * @param       string          $fileName       File name
         * @param       mixed           $data           Data
         * @return      mixed                           Written bytes on success, false otherwise
         */
        public static function write($fileName, $data) {
                return file_put_contents($fileName, $data);
        }

        /**
         * Delete a file.
         *
         * @param       string          $fileName       File name
         * @return      boolean                         True on success, false otherwise
         */
        public static function delete($fileName) {
                return (self::exists($fileName) ? unlink($fileName) : true);
        }

        /**
         * Send a file download to the client.
         *
         * @param       string          $file           File path or content
         * @param       string          $fileName       Desired file name
         * @param       boolean         $isPath         True when downloading an existing file, false otherwise
         * @param       string          $contentType    Content type (optional)
         */
        public static function sendDownload($file, $fileName, $isPath = true, $contentType = null) {
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: public');
                header('Content-Description: File Transfer');
                header('Content-Type: ' . ($contentType ? $contentType : 'application/octet-stream'));
                header('Content-Disposition: attachment; filename=' . $fileName);
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . ($isPath ? filesize($file) : strlen($file)));

                if ($isPath) {
                        readfile($file);
                        exit();
                } else {
                        exit($file);
                }
        }

        /**
         * Handle a file uploaded by the client.
         *
         * @param       string          $targetDirectory        Target directory
         * @param       string          $parameterName          Parameter name as used in the form element
         * @param       string          $fileName               Target file name (optional)
         * @param       array           $allowedTypes           List containing allowed file types (optional)
         * @param       int             $sizeLimit              File size limit in bytes (optional)
         * @return      boolean                                 True on success, false otherwise
         */
        public static function handleUpload($targetDirectory, $parameterName, $fileName = null, $allowedTypes = array(), $sizeLimit = 512000) {
                if (!String::endsWith($targetDirectory, '/')) $targetDirectory = "{$targetDirectory}/";

                $files = $_FILES;
                if (empty($files) || (!file_exists($targetDirectory) &&
                    !mkdir($targetDirectory, 0777, true))) return false;

                foreach ($files as $index => $fileCollection) {
                        if (is_array($files[$index]['name']))
                                $files[$index] = Collection::diverse($fileCollection);
                }

                $bracketStartPos = strpos($parameterName, '[');
                if ($bracketStartPos) {
                        $bracketEndPos = strpos($parameterName, ']');
                        $parameterCollection = substr($parameterName, 0, $bracketStartPos);
                        $parameterName = substr($parameterName, ($bracketStartPos + 1), ($bracketEndPos - $bracketStartPos - 1));

                        $file = (isset($files[$parameterCollection][$parameterName]) ? $files[$parameterCollection][$parameterName] : false);
                } else {
                        $file = (isset($files[$parameterName]) ? $files[$parameterName] : false);
                }

                if (!$file || $file['error'] !== UPLOAD_ERR_OK ||
                    (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) ||
                    ($sizeLimit > 0 && $file['size'] > $sizeLimit)) return false;

                if (!$fileName) $fileName = basename($file['name']);

                return (move_uploaded_file($file['tmp_name'], $targetDirectory . $fileName) ? $fileName : false);
        }
}