<?php
session_start();
require 'Lib/Dbconnect.php';
require 'Lib/cTicket.php';
require 'Lib/Functions.php';

$sAdminToolbar = null;
$user_logged = checkSession('user_logged');
$user_privilege = checkSession('user_permission');
$logged_in = username_logged_in();
$html_head = buildHTMLHead('Reports');
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
            'active' => true
        ],
        [
            'title' => 'User Manager',
            'anchor' => 'userManager.php',
            'active' => false
        ],
    ));
}

if(isset($_POST['btn_logout']))
{
    $oUser->logout();
}

$oReport7Days = new cTicket($oConn);
$sClosed7Days = $oReport7Days->lastSevenDaysReport("T.status_id = 'close'", "T.closed_date >=DATE(NOW()) - INTERVAL 7 DAY;"); //gets the last 7 days closed ticket report
$sOpen7Days = $oReport7Days->lastSevenDaysReport("T.status_id != 'close'", "T.submitted_date >= CURDATE() - INTERVAL 7 DAY"); //gets the last 7 days open ticket report

//the query for the pi graph
$query_graph =<<<SQL
SELECT 
	COUNT(T.category_id) AS category_count, TC.category_name, T.submitted_date
FROM 
	tbl_ticket T
INNER JOIN 
	tbl_category TC
ON T.category_id = TC.category_id
WHERE
  T.status_id != 'close'
AND
  T.submitted_date >= CURDATE() - INTERVAL 30 DAY
GROUP BY T.category_id


SQL;
//prepares and executed
$stmt = $oConn->prepare($query_graph);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_OBJ);
//gets the results as an object

?>
	<!DOCTYPE html>

	<!--
	Author: Marck Munoz
	Date: 2016
	-->

	<html lang="en">
    <?php echo $html_head ?>
	<body>
	<div class="container">
        <?php echo $nav_bar ?>
		<br>
		<?php echo $sAdminToolbar ?>

		<div class="page-header">
			<h1>Reports</h1>
		</div>
		
		<div class="row">
		    <div class="col-md-4">
		        <h3 class="text-center">Closed tickets in the last 7 days</h3>
		        <h2 class="text-center"><span class="label label-danger"><?php echo $sClosed7Days ?></span></h2>
		        <hr>
		        <h3 class="text-center">Open tickets in the last 7 days: </h3>
		        <h2 class="text-center"><span class="label label-success"> <?php echo $sOpen7Days?></span></h2>
            </div>
             <div class="col-md-8">
                 <div id="piechart" style="width: 900px; height: 500px;"></div>
            </div>
		</div>
	    
	
	</div>
    <!--Google pi chart from https://developers.google.com/chart/       Loops thorough the result of the query above and places the category name and the result creating the chart-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
        function drawChart() {

            var data = google.visualization.arrayToDataTable([
                ['Category', 'Count'],
                <?php
                foreach ($results as  $result) {
                    $category = $result->category_name;
                    $count = $result->category_count;

                    echo "['$category', $count],";

                }
                ?>
            ]);

            var options = {
                title: 'Percentage of Tickets by Category (Last 30 days)'
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart'));

            chart.draw(data, options);
        }
    </script>
	
	</body>

	</html>

