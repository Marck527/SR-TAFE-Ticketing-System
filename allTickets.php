<?php
session_start();
require 'Lib/Dbconnect.php';
require 'Lib/cTicket.php';
require 'Lib/Functions.php';

$sAdminToolbar = null;
$user_logged = checkSession('user_logged');
$user_privilege = checkSession('user_permission');
$html_head = buildHTMLHead('Active Tickets');
$logged_in = username_logged_in();
$user_logout = buildLogoutForm();
$nav_bar = buildNavBar($logged_in, $user_logout);
///////////////////////////////////////////////////////////////////
if(isset($_POST['btn_logout']))
{
    $oUser->logout();
}
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
            'active' => true
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
    ));
}
////////////////////////////////Pagination///////////////////////////////////
$page = isset($_GET['page'])? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per-page']) && $_GET['per-page'] <=50 ? (int)$_GET['per-page'] : 10;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;
////////////////////////////////Pagination///////////////////////////////////

$oActiveTickets = new cTicket($oConn);
$sActiveTickets = $oActiveTickets->allActiveTickets($start, $perPage);
$pages = $oActiveTickets->getPages();

echo HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sActiveTickets, $page, $perPage, $pages);
function HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sActiveTickets, $page, $perPage, $pages)
{
    $sHTML = "";
    $sHTML.= <<<HTML

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
			<h1>Active Tickets</h1>
		</div>
		<br>
		$sActiveTickets 
		

HTML;
    if($pages > 1) {
        for($x=1; $x <= $pages; $x++ ) {
            $active = null;
            if($page === $x) {
                $active = "class='active'";
            }
            $sHTML.=<<<HTML
        <ul class="pagination">
            <li $active><a href="?page={$x};&per-page={$perPage}">$x</a></li>
        </ul>   
HTML;

        }
    }

    $sHTML.= <<<HTML
    </div>
	</body>

	</html>
HTML;


    return $sHTML;
}
