<?php
session_start();
require 'Lib/Dbconnect.php';
require 'Lib/cTicket.php';
require 'Lib/Functions.php';

$sAdminToolbar = null;
$user_logged = checkSession('user_logged');
$user_privilege = checkSession('user_permission');
$logged_in = username_logged_in();
$html_head = buildHTMLHead('Closed Tickets');
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
            'active' => true
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
$page = isset($_GET['page'])? (int)$_GET['page'] : 1; //if the superblobal GET['Ppage'] is set, put the value into the variable page, else it defaults to page 1.
$perPage = isset($_GET['per-page']) && $_GET['per-page'] <=50 ? (int)$_GET['per-page'] : 10; //perpage sets the value of the results desiplayed per page, if it goes over 50, it defaults to 10 results.
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0; //the start of the page is calculated. 
////////////////////////////////Pagination///////////////////////////////////

$oALLClosedTickets = new cTicket($oConn);
$sAllClosedTickets = $oALLClosedTickets->allClosedTickets($start, $perPage);
$pages = $oALLClosedTickets->getPages(); //PAGINATION, gets the number of pages.

echo HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sAllClosedTickets, $page, $perPage, $pages);
function HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sAllClosedTickets, $page, $perPage, $pages)
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
			<h1>Closed Tickets</h1>
		</div>
		$sAllClosedTickets
		
	

HTML;
    if($pages > 1) { //PAGINATION. If the returned number of pages is greater than one
        for($x=1; $x <= $pages; $x++ ) { //add the link of the next page
            $active = null;
            if($page === $x) { //if the current page you are one will get a class of active.
                $active = "class='active'";
            }
            $sHTML.=<<<HTML
        <ul class="pagination">
            <li $active><a href="?page={$x};&per-page={$perPage}">$x</a></li>
        </ul>   
HTML;

        }
    }
    $sHTML.=<<<HTML
    </div>
	</body>

	</html>
HTML;

    return $sHTML;
}
