<?php
/**
 * Created by PhpStorm.
 * User: Student
 * Date: 31/08/2016
 * Time: 10:43 AM
 */
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/* FUNCTIONS LIBRARY */
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function buildHTMLHead($title) { //builds the html head, takes in the title of the page.
    return <<<HTML
    <head>
		<meta charset="UTF-8">
		<title>$title</title>
		<link rel="stylesheet" type="text/css" href="Bootstrap/css/bootstrap.min.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link href="MultiSelect/css/multi-select.css" media="screen" rel="stylesheet" type="text/css">
        <link rel="stylesheet" type="text/css" href="Css/MyCSS.css">
        <script src="Lib/JSLib.js"></script>
	</head>
HTML;
}
function buildNavBar($logged_name, $logout){ //build the navbar
    return <<<HTML
    <div class="container-fluid">
   <div class="row">
	<img src="Images/logo.png" class="img-responsive">
   </div>
</div>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span> 
            </button>
            <a class="navbar-brand" href="main.php">SR-TAFE</a>
            </div>
            $logout
            <p class="navbar-text navbar-right">Signed in as <a href="#" class="navbar-link">$logged_name</a></p>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav">
                    <li><a href="main.php">My Tickets<span class="sr-only">(current)</span></a></li>
                    <li><a href="Documentation/TicketSystemManual.pdf" target="_blank">Manual<span class="sr-only">(current)</span></a></li>
                </ul>
            </div>
            
            
        </div>
    </nav>
HTML;

}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function username_logged_in() { //return the curent user logged in and there privilege status
    $user_logged = checkSession('user_logged');
    $user_privilege = checkSession('user_permission');
    if ($user_privilege == '1')
        $privilege = 'Admin';
    else
        $privilege = 'Technician';

    return <<<HTML
    $user_logged ($privilege)
HTML;

}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function buildLogoutForm() { //return the logout form
    return <<<HTML
    <form class="navbar-form navbar-right" name="frm_logout" method="POST" onsubmit="return logoutOK()">
		<input class="btn btn-default" type="submit" name="btn_logout" value="Logout"/>
	</form>
HTML;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function breadCrumb($links) { //the create breadcrumb function, loops through the array given to it and create the list.
    $sHTML = "<ul class='breadcrumb'>";
    foreach($links as $link) {
        $title = $link['title'];
        $anchor = $link['anchor'];
        $active = $link['active'];
        if($active) {
            $active = "class='active'";
            $sHTML .= "<li $active>$title</a></li>";
        } else {
            $sHTML .= "<li><a href='$anchor'>$title</a></li>";
        }
    }
    $sHTML .= "</ul>";
    return $sHTML;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function redirectTo($i_page) { //redirect wrapper function
    return header("location:{$i_page}.php");
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function checkSession($i_session) { //checks the session of the session name passed into it
    switch($i_session) {
        case 'user_id':
            return $_SESSION['user_id'];
            break;
        case 'user_logged':
            return $_SESSION['user_logged'];
            break;
        case 'user_permission':
            return $_SESSION['user_permission'];
            break;
    }
}

