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
$html_head = buildHTMLHead('Add User');
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
            'title' => 'Closed Tickets',
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
            'title' => '>> Add Technician',
            'anchor' => 'addUser.php',
            'active' => true
        ]
    ));
}

if(isset($_POST['btn_logout']))
{
    $oUser->logout();
}

$oAddUser = new cManageUser($oConn);
$sAddUser = $oAddUser->userViewForm("Register");

if(isset($_POST['btn_pressed']))
{
    $firstname = $_POST['txt_f_name'];
    $lastname = $_POST['txt_l_name'];
    $email = $_POST['txt_email'];
    $password = $_POST['txt_chosen_pword'];
    $privilege = $_POST['dropDownPrivilege'];

    $oAddUser->registerUser($firstname, $lastname, $email, $password, $privilege );
}

echo HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sAddUser);
function HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sAddUser)
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
			<h1>Add User</h1>
		</div>
		<br>
        $sAddUser

	</div>
	</body>

	</html>

HTML;
}
