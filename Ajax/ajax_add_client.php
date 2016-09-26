<?php
/**
 * Created by PhpStorm.
 * User: Marck
 * Date: 20/09/2016
 * Time: 9:24 PM
 */

//This is the ajax page, when  new client is added, it goes through this page, creates the dropdown and returns it on the client_box div.
require_once '../Lib/Dbconnect.php';
require_once '../Lib/cDropdown.php';

isset($_GET['client_id']) ? $default_id = $_GET['client_id'] : $default_id = 0; 
isset($_POST['client_name']) ? $client_name = $_POST['client_name'] : $client_name = null;
isset($_POST['client_email']) ? $client_email = $_POST['client_email'] : $client_email = null;

if (!empty($client_name) && !empty($client_email)) {

    $check_email =<<<SQL
      SELECT
        client_email
      FROM
        tbl_client
      WHERE
        client_email = :email_check;
SQL;

    $stmt_check = $oConn->prepare($check_email);
    $stmt_check->execute([
       'email_check' =>  $client_email
    ]);


    if ($stmt_check->rowCount()) {
        echo "<p class='text-danger'>That email already exists</p>";
    } else {

        $sSQL=<<<SQL
        INSERT INTO
	      tbl_client(client_fullname, client_email)
        VALUES
        (:client_fullname, :client_email);
SQL;

        try {
            $oConn->beginTransaction();

            $stmt = $oConn->prepare($sSQL);
            $stmt->execute([
                'client_fullname' => $client_name,
                'client_email'    => $client_email
            ]);

            $last_inserted_client_id = $oConn->lastInsertId();

            if ($stmt) {
                $oConn->commit();
            }

        }catch(PDOException $e) {
            $oConn->rollBack();
        }


    }


}

if (isset($_GET['client_id'])) {
    $default_id = $_GET['client_id'];
} elseif (isset($last_inserted_client_id)) {
    $default_id = $last_inserted_client_id;
} else {
    $default_id = 0;
}

//Initialized the dropdowns.
$oDropdownClient = new cDropdown($oConn, "SELECT client_id, client_fullname FROM tbl_client;");
$oDropdownClient->setName("dropDownClient");
$oDropdownClient->setHTMLID("dropDownClient");
$oDropdownClient->setIDField("client_id");
$oDropdownClient->setDefaultID($default_id);
$oDropdownClient->setDescriptionField("client_fullname");
$oDropdownClient->setSize(8);
$oDropdownClient->setBRequired(true);
echo $sDropdownClient = $oDropdownClient->HTML();