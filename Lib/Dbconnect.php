<?php
/**
 * Created by PhpStorm.
 * User: Marck
 * Date: 27/07/2016
 * Time: 9:29 PM
 */
//This is the database connection file.
$db_host = "localhost"; //sets up db info
$db_name = "TicketDB";
$db_user = "root";
$db_password = "Password1";

try
{
  $oConn = new PDO("mysql:host=$db_host; dbname=$db_name; charset=utf8", $db_user, $db_password, array(PDO::ATTR_PERSISTENT=>TRUE)); //return a new PDO DB object
}
catch(PDOException $e)
{
  echo "A database error has occured.";
}

require_once 'cUser.php'; //Includes the 'cUser' class file
$oUser = new cUser($oConn); //An instance of cUser is created and the object 'oConn' is passed in its constructor. This way, we can reuse the same object on multiple pages as long as we include this database file which we need to anyway.
