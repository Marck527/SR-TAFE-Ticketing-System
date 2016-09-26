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
$html_head = buildHTMLHead('User Manager');
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
            'active' => true
        ],
    ));
}

if(isset($_POST['btn_logout']))
{
    $oUser->logout();
}

$username_search = "";
$privilege_to_filter = 0;
if(isset($_POST['btn_search_privilege']))
{
    $privilege_to_filter = $_POST['dropDownPrivilege'];
    $default_id = $_POST['dropDownPrivilege'];
    $username_search = $_POST['txt_username_search'];

    
}
else
{
    $default_id = 0;
}

$oDropdownPrivilege = new cDropdown($oConn, "SELECT agent_privilege_id, privilege_title FROM tbl_agent_privilege;");
$oDropdownPrivilege->setName("dropDownPrivilege");
$oDropdownPrivilege->setHTMLID("accessLevel");
$oDropdownPrivilege->setDisplayAll(true);
$oDropdownPrivilege->setDisplayAllText("Any");
$oDropdownPrivilege->setDefaultID($default_id);
$oDropdownPrivilege->setIDField("agent_privilege_id");
$oDropdownPrivilege->setDescriptionField("privilege_title");
$sDropdownPrivilege = $oDropdownPrivilege->HTML();

$oManageUsers = new cManageUser($oConn);
$sManageUsers = $oManageUsers->listUsers($privilege_to_filter, $username_search);



echo HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sDropdownPrivilege, $username_search, $sManageUsers);
function HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sDropdownPrivilege, $username_search, $sManageUsers)
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
			<h1>User Manager</h1>
		</div>
		<br>
		<a href="addUser.php">Add New Technician</a>
		<br>
		<br>
		<form class="form-inline" action="$actionpage" method="post">
		    <div class="form-group">
		        <label for="accessLevel">Access Level</label>
		        $sDropdownPrivilege
		    </div>                  
		    <div class="form-group">
		        <label for="name">Name</label>
				<input class="form-control" id="name" type="text" name="txt_username_search" value="$username_search" placeholder="[Search by username]"/>
		    </div>
		    <input class="btn btn-default" type="submit" name="btn_search_privilege" value="Search">
		 </form>
		 <br>
	
		$sManageUsers
		

	</div>
	</body>

	</html>

HTML;
}
