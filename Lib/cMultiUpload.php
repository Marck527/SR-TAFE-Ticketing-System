<?php

/**
 * Created by PhpStorm.
 * User: Marck
 * Date: 3/09/2016
 * Time: 12:38 PM
 */

/*
 * Multi upload class file - Video tutorial - https://www.youtube.com/watch?v=MxMWEYJMcmk
 * Turned into class file by Marck Munoz.
 */
class cMultiUpload
{
    private $_db_instance = null; //keeps the current database instance
    private $_sql = ''; //will keep the sql
    private $_form_name = ''; //the form name
    private $_successful_uploads = array(); //an array which will keep track of all the successfull uploads
    private $_failed_uploads = array(); //an array which will keep track of the failed uploads
    private $_allowed_extensions = array('txt', 'jpg', 'png', 'docx', 'pdf', 'zip', 'rar'); //an aray of all the allowed extensions
    private $_upload_destination = 'uploads/'; //keeps the destination of the uploads
    private $_size_limit = 2097152; //sets the size limit

    function __construct($i_form_name) //the constructor takes in the name of the form being targeted
    {
        $this->_form_name = $i_form_name;
    }
    function getDB() { //gets a database connection
        if(!isset($_db_instance)) { //is instance is not set
            $_db_instance = new PDO("mysql:host=127.0.0.1; dbname=TicketDB; charset=utf8", 'root', 'Password1'); //set it to the a new pdo connection
        }
        return $_db_instance; //else if it already is set, return that connection.
    }

    /**
     * @param string $sql
     */
    public function setSql(string $sql)
    {
        $this->_sql = $sql;
    }
    /**
     * @param string $lastID
     */
    public function setLastID(string $lastID)
    {
        $this->_lastID = $lastID;
    }
    /**
     * @param array $allowed_extensions
     */
    public function setAllowedExtensions($allowed_extensions)
    {
        $this->_allowed_extensions = $allowed_extensions;
    }

    /**
     * @param string $upload_destination
     */
    public function setUploadDestination($upload_destination)
    {
        $this->_upload_destination = $upload_destination;
    }

    /**
     * @param int $size_limit
     */
    public function setSizeLimit($size_limit)
    {
        $this->_size_limit = $size_limit;
    }

    /**
     * @return array
     */
    public function getFailedUploads()
    {
        return $this->_failed_uploads;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->_allowed_extensions;
    }

    /**
     * @return array
     */
    public function getSuccessfulUploads()
    {
        return $this->_successful_uploads;
    }

    public function upload() {
        if(!empty($_FILES[$this->_form_name]['name'][0])) { //if the upload form being targets is not empty
            $files = $_FILES[$this->_form_name];

            foreach ($files['name'] as $position => $file_name) { //foreach of the name (name of the files tryig to be uploaded in the array)

                $file_tmp = $files['tmp_name'][$position]; //get the tmp name and the current index of te fle we are looping through
                $file_size = $files['size'][$position]; //get the size
                $file_error = $files['error'][$position]; //get the error status

                $file_ext = explode('.', $file_name); //get the extension of the file being uploaded by seperating it with a period and turning it into an array.
                $file_ext = strtolower(end($file_ext)); //go to the end of the array (which will return the extension e.g txt)

                if(in_array($file_ext, $this->_allowed_extensions)) { //if the extension of the file trying to be uploaded is in the allowed array

                    if ($file_error == 0) { //if the file being looped through didn't error

                        if($file_size <= $this->_size_limit) { //if the file being looped through's file size is less than the set limit

                            $file_name_new = uniqid('', true) . '.' . $file_ext; //provide it with a random name and add the file extension
                            $file_destination = $this->_upload_destination . $file_name_new; //set the file destination tot he set upload destination plus the filen name, this will give us the path of the file uploaded.

                            if(move_uploaded_file($file_tmp, $file_destination)) { //if the file were looping thorugh has been successfully moved to the destination folder

                                if($this->insertToDatabase($file_name_new, $file_destination)) { //insert the info to the database.
                                    $this->_successful_uploads[$position] = $file_destination; //add it into the successful array with it's position
                                } else {
                                    $this->_failed_uploads[$position] = "[{$file_name}] failed to upload to the database."; //else it failed to be inserted into the database, add the file name to the failed array.
                                }

                            } else {
                                $this->_failed_uploads[$position] = "[{$file_name}] failed to upload."; //add to failed array if it failed to upload
                            }

                        } else {
                            $this->_failed_uploads[$position] = "[{$file_name}] is too large."; //add to failed array if file is too large
                        }

                    } else {
                        $this->_failed_uploads[$position] = "[{$file_name}] encountered an error. {$file_error}.";
                    }

                } else {
                    $this->_failed_uploads[$position] = "[{$file_name}] is not a supported attachment.";
                }
            }
        }
        return true;
    }

    private function insertToDatabase($i_file_name, $i_file_destination) { //insert into database method

        $oStmt = $this->getDB()->prepare($this->_sql); //prepares the set sql, binds and eexecutes
        $oStmt->bindParam(':file_name', $i_file_name); 
        $oStmt->bindParam(':file_location', $i_file_destination);
        $oStmt->execute();
//        echo '<pre>' . $this->_sql .  '</pre>';

        return true;
    }
}