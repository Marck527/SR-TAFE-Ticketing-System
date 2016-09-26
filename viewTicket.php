<?php
/**
 * Created by PhpStorm.
 * User: Student
 * Date: 3/08/2016
 * Time: 9:37 AM
 */
session_start();
require 'Lib/Dbconnect.php';
require 'Lib/cTicket.php';
require 'Lib/Functions.php';

$user_logged = checkSession('user_logged');
$user_privilege = checkSession('user_permission');
$logged_in = username_logged_in();
$html_head = buildHTMLHead('View Ticket');
$user_logout = buildLogoutForm();
$nav_bar = buildNavBar($logged_in, $user_logout);

$sHTMLAdminEdit = null;

if(! isset($user_logged))
{
    redirectTo('login');
}

if(isset($_POST['btn_logout']))
{
    $oUser->logout();
}


$out_ticket_id = $_GET['i_ticket_id']; //a ticket id is passsed into this page, it is then stored into the variable.

$oViewTicket = new cTicket($oConn);
$sViewTicket = $oViewTicket->viewIndividualTicket($out_ticket_id);

if($user_privilege == 1)
{
    $sHTMLAdminEdit = <<<HTML
    <a href="editTicket.php?iTicketID=$out_ticket_id">Edit ticket</a>
HTML;

}

echo HTMLPage($html_head, $nav_bar, $sViewTicket, $sHTMLAdminEdit);
function HTMLPage($html_head, $nav_bar, $sViewTicket, $sHTMLAdminEdit)
{
    return <<<HTML

	<!DOCTYPE html>

	<!--
	Author: Marck Munoz
	Date: 2016
	-->

	<html lang="en">
    $html_head
	<body>
	<div class="container">
        $nav_bar
		<br>
        $sHTMLAdminEdit
		$sViewTicket
		
	</div>
	</body>

	</html>

HTML;
}