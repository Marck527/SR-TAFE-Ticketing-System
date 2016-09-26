<?php
session_start();
require 'Lib/Dbconnect.php';
require 'Lib/cTicket.php';
require 'Lib/Functions.php';

$user_logged = checkSession('user_logged');
$user_privilege = checkSession('user_permission');
$logged_in = username_logged_in();
$html_head = buildHTMLHead('Edit Ticket');
$user_logout = buildLogoutForm();
$nav_bar = buildNavBar($logged_in, $user_logout);
if(! isset($user_logged) || $user_privilege!=1 )
{
    redirectTo('login');
}

if(isset($_POST['btn_logout']))
{
    $oUser->logout();
}

$ticket_id_to_be_edited = $_GET['iTicketID'];

$oEditTicket = new cTicket($oConn);
$sEditTicket = $oEditTicket->editTicket($ticket_id_to_be_edited);


echo HTMLPage($html_head, $nav_bar, $ticket_id_to_be_edited, $sEditTicket);
function HTMLPage($html_head, $nav_bar, $ticket_id_to_be_edited, $sEditTicket)
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

		<div class="page-header">
			<h1>Edit ticket #$ticket_id_to_be_edited</h1>
		</div>
        $sEditTicket
  		
		
		
	</div>
	
	
	</body>

	</html>

HTML;
}
