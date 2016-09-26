<?php
session_start();
require 'Lib/Dbconnect.php'; //requires the database connection, 'cTicket' class and the functions library.
require 'Lib/cTicket.php';
require 'Lib/Functions.php';

$sAdminToolbar = null; //By default, the admin toolbar is set to null;
$user_logged = checkSession('user_logged'); //gets the value of the session 'user_logged' and places it in a variable.
$user_privilege = checkSession('user_permission'); //gets the value of the user permission session and keeps it in a variable.
$logged_in = username_logged_in(); //gets the name of the user logged in.
$html_head = buildHTMLHead('Create Ticket'); //calls the build html function which builds the htm head.
$user_logout = buildLogoutForm(); //gets the logout form
$nav_bar = buildNavBar($logged_in, $user_logout); //builds the navbar and the person who's logged in and the ogout form is passed into it.
if(! isset($user_logged) || $user_privilege!=1 ) //if there's nouser logged in or there privilege is not admin, redirect them to the login page.
{
    header('location: login.php');
}
else { //else an admin is logged in then set the admin tollbar, which is the bread crumb function.
    $sAdminToolbar =  breadCrumb(array( //breadcrumb function,an array of item is passed, the title of the link, the anchor tag, and a boolean wheter it's the active link. You can add as many links as you want in this array since it loops through them in the function.
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
            'active' => true
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
    ));
}
//If the logout button is pressed, logout the current user.
if(isset($_POST['btn_logout']))
{
    $oUser->logout(); //call the object oUser (created in the 'Dbconnect' file since the logout feature will need to be accessed on any page.
}

//Initializes dropdowns.
$oDropdownCategory = new cDropdown($oConn, "SELECT category_id, category_name FROM tbl_category;");//A new dropdown object is created, connection and the sql is passed into it.
$oDropdownCategory->setName("dropDownCategory"); //sets the name of the dropdown.
$oDropdownCategory->setHTMLID("dropDownCategory"); //sets the dropdown id.
$oDropdownCategory->setIDField("category_id"); //sets the dropdown id field (the value of each dropdown item)
$oDropdownCategory->setDescriptionField("category_name"); //sets the description field of the dropdown (the value that shows up)
$oDropdownCategory->setDefaultID('hard'); //sets the default id, in this case category hardware will be default
$sDropdownCategory = $oDropdownCategory->HTML();

//same as above but for a different table
$oDropdownPriority = new cDropdown($oConn, "SELECT priority_id, priority_name FROM tbl_priority;");
$oDropdownPriority->setName("dropDownPriority");
$oDropdownPriority->setHTMLID("dropDownPriority");
$oDropdownPriority->setIDField("priority_id");
$oDropdownPriority->setDescriptionField("priority_name");
$oDropdownPriority->setDefaultID('high');
$sDropdownPriority = $oDropdownPriority->HTML();

$oDropdownStatus = new cDropdown($oConn, "SELECT status_id, status_name FROM tbl_status;");
$oDropdownStatus->setName("dropDownStatus");
$oDropdownStatus->setHTMLID("dropDownStatus");
$oDropdownStatus->setIDField("status_id");
$oDropdownStatus->setDescriptionField("status_name");
$oDropdownStatus->setDefaultID('open');
$sDropdownStatus = $oDropdownStatus->HTML();

$oDropdownAgent = new cDropdown($oConn, "SELECT agent_id, f_name, agent_privilege_id FROM tbl_agent;");
$oDropdownAgent->setName("my-select[]");
$oDropdownAgent->setHTMLID("my-select");
$oDropdownAgent->setIDField("agent_id");
$oDropdownAgent->setDescriptionField("f_name");
$oDropdownAgent->setBMultiple(true);
$oDropdownAgent->setBRequired(true);
$oDropdownAgent->setSize(6);
$sDropdownAgent = $oDropdownAgent->HTML();

//if(isset($_POST['btn_add_client'])) //REPLACED WITH AJAX 
//{
//    $client_fullname = $_POST['add_client_full_name'];
//    $client_email = $_POST['add_client_email'];
//
//    $oNewClient = new cTicket($oConn);
//    $oNewClient->addClient($client_fullname, $client_email);
//    header('refresh:0');
//}
/*
 * If the add ticket button is pressed, gather the variables in the input fields.
 * Make a new cTicket object and pass the collected data to the method of the cTicket object where it
 * will get processed.
 */
//if the add ticket button is clicked then collect all the values of the fields
if(isset($_POST['btn_add_ticket']))
{
    $ticket_date = $_POST['ticket_date'];
    $ticket_subject = $_POST['ticket_subject'];
    $ticket_body = $_POST['ticket_body'];
    $ticket_location = $_POST['ticket_location'];
    $ticket_client = $_POST['dropDownClient'];
    $ticket_category = $_POST['dropDownCategory'];
    $ticket_priority = $_POST['dropDownPriority'];
    $ticket_status = $_POST['dropDownStatus'];
    $assigned_by = $_SESSION['user_id']; //gets the user id of the admin creating the ticket

    $arr_assigned_agents = $_POST['my-select']; //gets all the selected agents from the selector.

    $oNewTicket = new cTicket($oConn); //creates a new object of cTicket and passes the collected values in the addticket method of cTicket class.
    $oNewTicket->addTicket($ticket_date, $ticket_subject, $ticket_body, $ticket_location, $ticket_client,
                            $ticket_category, $ticket_priority, $ticket_status, $assigned_by, $arr_assigned_agents);

}



//Creates a function which is a html page anc can take variables. The function is then echo'd out to show on the screen.
echo HTMLPage($html_head, $nav_bar, $sAdminToolbar, $sDropdownPriority, $sDropdownStatus, $sDropdownCategory,$sDropdownAgent);
function HTMLPage($html_head, $nav_bar, $sAdminToolbar,  $sDropdownPriority, $sDropdownStatus, $sDropdownCategory, $sDropdownAgent)
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
			<h1>Create Ticket</h1>
		</div>
        <form id="frm_add_client" action="$actionpage" method="post"></form>

		<br>
		<form name="frm_new_ticket" action="$actionpage" method="POST" enctype="multipart/form-data">
		<div class="panel panel-default">
		<div class="panel-heading">Client</div>
		 <div class="panel-body">
		    <div class="row">
		        <div class="col-md-3">
		             <div class="form-group">
  				        <label>Select Existing Client</label>
  				        <div id="client_box">
  				           
                        </div>
			        </div>
		        </div>
		        
		        <div class="col-md-3">
		            
		            <div class="form-group">
		                <label>Client's Full Name</label>
		                <input class="form-control" form="frm_add_client" type="text" name="add_client_full_name" id="client_name" required>
		            </div>
		            <div class="form-group">
		                <label>Client's Email</label>
		                <input class="form-control" form="frm_add_client" type="email" name="add_client_email" id="client_email" required>
		            </div>
		            <input class="btn btn-warning" form="frm_add_client" type="submit" name="btn_add_client" id="btn_add_client" value="Add Client">
		        </div>
		    </div>
		  
		</div>
		</div>
		<div class="panel panel-default">
		<div class="panel-heading">Ticket Information</div>
		 <div class="panel-body">
		    <div class="row">
		        <div class="col-md-4">
		            <div class="form-group">
				        <label>Ticket Subject</label>
				        <input class="form-control" type="text" name="ticket_subject" required/>
			        </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
				        <label>Enquirer/Problem Location</label>
				        <input class="form-control" type="text" name="ticket_location" required/>
			        </div>
                 </div>
		        <div class="col-md-2">
		            <div class="form-group">
				        <label>Date Created</label>
				        <input class="form-control" type="date" name="ticket_date" required/>
			        </div>
                </div>
		       
		    </div>
			<div class="form-group">
  				<label>Ticket Body</label>
  				<textarea class="form-control" rows="5" name="ticket_body" maxlength="1999" required></textarea>
			</div>
			<div class="form-group">
			    <label>Add Ticket Attachment(s)</label>
			    <input type="file" name="ticket_attachments[]" multiple>
			</div>
			<hr>
			<div class="row">
			    <div class="col-md-2">
			        <div class="form-group">
  				        <label>Category</label>
  				        $sDropdownCategory
			        </div>
                </div>
                <div class="col-md-2">
			        <div class="form-group">
  				        <label>Priority</label>
  				        $sDropdownPriority
			        </div>
                </div>
                <div class="col-md-2">
			        <div class="form-group">
  				        <label>Status</label>
  				        $sDropdownStatus
			        </div>
                </div>
			</div>
			<div class="row">
			     <div class="col-md-4">
			        <div class="form-group">
  				        <label>Assign Technician(s)</label>
  				        $sDropdownAgent
			        </div>
			     </div>
			     <div class="col-md-12">
			        <p class="help-block">*Choose technicians by clicking from the list box on the left. Chosen technicians will appear on the right list box.</p>
			     </div>
			</div>
			
		</div>
		</div>

			<input class="btn btn-primary" type="submit" name="btn_add_ticket" value="Create Ticket"/>
		</form>

	</div>
	<script src="MultiSelect/js/jquery.multi-select.js"></script>
	<script>
	    $('#my-select').multiSelect();
	    
	     $.ajax({
	        type: 'GET',
	        url: 'Ajax/ajax_add_client.php',
	        success: function(results) {
	                $('#client_box').html(results);
	        } 
	      });
	    
	    $('#btn_add_client').click(function(){
	        var client_name = $('#client_name').val();
	        var client_email = $('#client_email').val();
            
            addClient(client_name, client_email );
	        
	        return false;
	    });
	    
	    function addClient(client_name, client_email) {
	        $.ajax({
	            type: 'POST',
	            url: 'Ajax/ajax_add_client.php',
	            data: {client_name: client_name, client_email: client_email},
	            success: function(results) {
	                $('#client_box').html(results);
	                
                    $('#client_name').val('').focus();
	                $('#client_email').val('');
	            }
	        });
	    }
	    
    </script>
	</body>

	</html>

HTML;
}
