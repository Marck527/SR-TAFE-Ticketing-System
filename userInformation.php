<?php
session_start();
require 'Lib/Dbconnect.php';
require 'Lib/cDropdown.php';
require 'Lib/cManageUser.php';
require 'Lib/Functions.php';

$sAdminToolbar = null;
$user_logged = checkSession('user_logged');
$user_privilege = checkSession('user_permission');
$logged_in = username_logged_in();
$html_head = buildHTMLHead('Uer Information');
$user_logout = buildLogoutForm();
$nav_bar = buildNavBar($logged_in, $user_logout);

if(! isset($user_logged) || $user_privilege!=1 )
{
    redirectTo('login');
}
else {
    $sAdminToolbar =  breadCrumb(array(
        [
            'title' => 'My Tickets',
            'anchor' => 'main.php',
            'active' => false
        ],
        [
            'title' => 'Active Tickets',
            'anchor' => 'allTickets.php',
            'active' => false
        ],
        [
            'title' => 'Create Ticket',
            'anchor' => 'addTicket.php',
            'active' => false
        ],
        [
            'title' => 'Closed Ticket',
            'anchor' => 'allClosedTickets.php',
            'active' => false
        ],
        [
            'title' => 'Reports',
            'anchor' => 'reportScreen.php',
            'active' => false
        ],
        [
            'title' => 'User Manager',
            'anchor' => 'userManager.php',
            'active' => false
        ],
        [
            'title' => '>> User Info',
            'anchor' => 'userInformation.php',
            'active' => true
        ]
    ));
}

if(isset($_POST['btn_logout']))
{
    $oUser->logout();
}

$user_id = $_GET['iUserID'];
$oEditUser = new cManageUser($oConn);
$sEditUser = $oEditUser->editUser($user_id);



echo HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sEditUser);
function HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sEditUser)
{
    $actionpage = $_SERVER['PHP_SELF'];

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
		$sAdminToolbar

		<div class="page-header">
			<h1>User Information</h1>
		</div>
		<br>
		$sEditUser

	</div>
	</body>

	</html>

HTML;
}
