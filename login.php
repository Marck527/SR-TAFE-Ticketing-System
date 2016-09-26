<?php
session_start();
require 'Lib/Dbconnect.php';
require 'Lib/Functions.php';

$html_head = buildHTMLHead('Login');
if(isset($_POST['btn_login'])) //If the login button is pressed
{
	$email = $_POST['txt_username']; //Take whatever is entered in the username and password field
	$password = $_POST['txt_password'];

	$oUser->login($email, $password); //And using the created $oUser object, pass its method login. (The $oUser object is first created in the 'Dbconnect.php' and the same object is still being used.
}

echo HTMLPage($html_head);
function HTMLPage($html_head)
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
		  <div class="container-fluid">
   			<div class="row">
			<img src="Images/logo.png" class="img-responsive">
   		</div>
		<div class="page-header text-center">
			<h1>Ticket System Login</h1>
		</div>
		<div class="row">
        	<div class="col-md-6 col-md-offset-3">
        		<form name="frm_login" action="$actionpage" method="POST" onsubmit=" return logValidator()">
				<div class="form-group">
					<label>Email</label>
					<input class="form-control" type="email" name="txt_username" placeholder="eg. RocketDog@gmail.com" required/>
				</div>
				<div class="form-group">
					<label>Password</label>
					<input class="form-control" type="password" name="txt_password" required/>
				</div>
			<input class="btn btn-primary" type="submit" name="btn_login" value="Login"/>
		</form>
        	</div>
        </div>
        
		

	</div>
	</body>

	</html>

HTML;
}
