/*
Title: JSLib.php
Author: Marck Munoz
Date: 24/07/2016

Comment:
A collection of JavaScript functions.
*/

/***************************************************************************************
Function to validate the registration form, makes sure all fields have been properly
filled out first, username and password greater than certain lengths set and email
address is valid.
****************************************************************************************/
function regValidator() 
{
	if(!frm_register.txt_f_name.value || !frm_register.txt_l_name.value
	|| !frm_register.txt_chosen_pword.value || !frm_register.txt_email.value
		|| !frm_register.txt_chosen_pword_rpt.value )
	{
		alert("Please make sure all fields have been properly filled.");
		return false;
	}
	else if(frm_register.txt_chosen_pword.value.length <= 8)
	{
		alert("Password must be longer than 8 characters");
		return false;
	}
	else if(frm_register.txt_chosen_pword.value !== frm_register.txt_chosen_pword_rpt.value)
	{
		alert("Passwords must match!");
	}
	else if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(frm_register.txt_email.value))  
	{  
		return (true)  
	}  
		alert("You have entered an invalid email address!");
		return (false)

}
/***************************************************************************************
Validates the login form
****************************************************************************************/
function logValidator() 
{
	if(!frm_login.txt_username.value || !frm_login.txt_password.value) 
	{
		alert("Please make sure all fields have been properly filled.");
		return false;
	}
	
}
/***************************************************************************************
Confirms whether or not the user really want to logout
****************************************************************************************/
function logoutOK() {
	  var logout = confirm("Are you sure you want to logout?");
	  if (logout == true)
		  return true;
	  else
		  return false;
	
}