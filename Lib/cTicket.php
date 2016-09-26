<?php

/**
 * Created by PhpStorm.
 * User: Marck
 * Date: 31/07/2016
 * Time: 3:18 PM
 */

require 'cDropdown.php';
require_once 'cMultiUpload.php';

class cTicket
{
    private $oConn;
    private $pages = null;
    
    function __construct($i_db) //a constructor which takes in a db 
    {
        $this->oConn = $i_db; //the db object passed will be placed in the private variable oConn.
    }

    /**
     * @return null
     */
    public function getPages() //for pagination purposes, return the pages necessary for pagination.
    {
        return $this->pages;
    }
    //add ticket function
    public function addTicket($i_ticket_date, $i_ticket_subject, $i_ticket_body, $i_ticket_location, $i_ticket_client, $i_ticket_category, $i_ticket_priority, $i_ticket_status, $i_assigned_by, $i_arr_assigned_agents)
    {
        if($i_ticket_status == 'close') //if the ticket statuses passed value is equal to 'closed' it means they have closed the ticket therefore get the current date and put it in the closed date field.
        {
            $closed_status = date('Y-m-d H:i:s');
        }
        else
        {
            $closed_status = null; //else it's not closed then passa  null value in the closed date field.
        }
        //insert sql
        $sSQL = <<<SQL
        INSERT INTO
          tbl_ticket(submitted_date, ticket_subject, ticket_body, location, closed_date, client_id, category_id, priority_id, status_id, assigned_by)
        VALUES
          (:submitted_date, :ticket_subject, :ticket_body, :ticket_location, :closed_status, :client_id, :ticket_category, :ticket_priority, :ticket_status, :assigned_by);

        SET @TICKET_ID = LAST_INSERT_ID();

SQL;
        //foreach loop which goes through all the selected agents and inserts each agent in the agent ticket table as well as the ticket id.
        foreach($i_arr_assigned_agents as $agent)
        {
            $sSQL .=<<<SQL
          
           INSERT INTO tbl_agent_ticket(agent_id, ticket_id)VALUES($agent, @TICKET_ID);
SQL;
        }
        //prepares, binds the values and then executes the query.
        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->bindParam(':submitted_date', $i_ticket_date);
        $oStmt->bindParam(':ticket_subject', $i_ticket_subject);
        $oStmt->bindParam(':ticket_body', $i_ticket_body);
        $oStmt->bindParam(':ticket_location', $i_ticket_location);
        $oStmt->bindParam(':closed_status', $closed_status);
        $oStmt->bindParam(':client_id', $i_ticket_client);
        $oStmt->bindParam(':ticket_category', $i_ticket_category);
        $oStmt->bindParam(':ticket_priority', $i_ticket_priority);
        $oStmt->bindParam(':ticket_status', $i_ticket_status);
        $oStmt->bindParam(':assigned_by', $i_assigned_by);
        $oStmt->execute();
        $oLastID = $this->oConn->lastInsertId(); //gets the last inserted id.
        
        //calls the multi upload class to handle attachments and if there are any attachments, insert each attachment to the attachment table and the ticket attachment table.
        $oUploadTicketAttachments = new cMultiUpload('ticket_attachments');
        $oUploadTicketAttachments->setSql("
            INSERT INTO
          tbl_attachment(attachment_name, attachment_location)
            VALUES(:file_name, :file_location);

        SET @ATT_ID = LAST_INSERT_ID();

        INSERT INTO
          tbl_ticket_attachment(ticket_id, attachment_id)
          VALUES($oLastID, @ATT_ID);
        ");
        if($oUploadTicketAttachments->upload()) { //uploads the attachments
//            $successfulUploads = $oUploadTicketAttachments->getSuccessfulUploads();
//            if(!empty($successfulUploads)) {
//                foreach ($successfulUploads as $success) {
//                    echo $success, '<br>';
//                }
//            }
            $failedUploads = $oUploadTicketAttachments->getFailedUploads(); //gets the errors if there are any
            if(!empty($failedUploads)) { //if there are any errors, loop through them and display each error.
                foreach ($failedUploads as $failure) {
                    echo "<div class='alert alert-warning'>
                            <strong>Warning!</strong>$failure
                          </div>";
                }

            }
        }

        echo "<div class='alert alert-success'><strong>Success!</strong> Ticket Successfully Submitted! Note: Please don't refresh the page to avoid ticket's doubling up.</div>"; //display this when the ticket is created.


    }
    //shows he agent's ticket function
    public function showAgentTicket($start, $perPage)
    {
        $agent_logged = $_SESSION['user_id']; //gets the currently logged in agent


        //the sql, gets all the ticket info associated with the agent logged in.
        $sSQL = <<<SQL
        SELECT 
	      SQL_CALC_FOUND_ROWS T.ticket_id, T.assigned_by, CONCAT(f_name, " ", l_name) as agent_assigned, T.submitted_date,  T.ticket_subject, C.category_name,  P.priority_id, P.priority_name, S.status_name
        FROM
	      tbl_ticket T
        INNER JOIN
	      tbl_priority P
        ON 
	      T.priority_id = P.priority_id
        INNER JOIN
	      tbl_status S
        ON
	      T.status_id = S.status_id
        INNER JOIN 
	      tbl_category C
        ON 
	      T.category_id = C.category_id
        INNER JOIN
	      tbl_agent_ticket AGT
        ON 
	      T.ticket_id = AGT.ticket_id
        INNER JOIN 
	      tbl_agent A
        ON 
	      AGT.agent_id = A.agent_id
        WHERE 
	      AGT.agent_id = $agent_logged
        AND 
	      S.status_id != 'close'
	    ORDER BY
	      T.submitted_date DESC, T.ticket_id DESC
	    LIMIT
	      {$start}, {$perPage};

SQL;
        //prepares and execute.
        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->execute();

        $total = $this->oConn->query("SELECT FOUND_ROWS() as total")->fetch()['total']; //for pagination. gets the total rows found.
        $this->pages = ceil($total / $perPage); //pages is the total rows found divided by hte perpage rows you want to display.

        if($oStmt->rowCount()) //if theres result found (meaning this person has atleast one ticket) display that ticket.
        {
            $sHTML =<<<HTML
            <div class ="table-responsive">
            <table class="table table-hover ">
                <tr>
                    <th>Ticket ID</th>
                    <th>Assigned by</th>
                    <th>Assigned agent(s)</th>
                    <th>Submitted Date</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>View</th>
                </tr>
               
HTML;

            foreach ($this->oConn->query($sSQL) as $oRow)
            {
                $ticket_id = $oRow['ticket_id'];
                $submitted_date = $oRow['submitted_date'];
                $ticket_subject = $oRow['ticket_subject'];
                $ticket_category = $oRow['category_name'];
                $ticket_priority = $oRow['priority_name'];
                $ticket_status_name = $oRow['status_name'];

                $sSQLAssignedBy = <<<SQL
                SELECT 
                  CONCAT(f_name, " ", l_name) as assigned_by
                FROM
                  tbl_ticket T
                INNER JOIN
                  tbl_agent A
                ON 
                  T.assigned_by = A.agent_id
                WHERE
                  T.ticket_id = $ticket_id ;
SQL;


                $sSQLAssignedTo=<<<SQL
                SELECT
	              CONCAT(f_name, " ", l_name) AS agent_name
                FROM
	              tbl_ticket T
                INNER JOIN
	              tbl_agent_ticket AGT
                ON
	              T.ticket_id = AGT.ticket_id
                INNER JOIN
	              tbl_agent A
                ON
	              AGT.agent_id = A.agent_id
                WHERE
	              T.ticket_id = $ticket_id;

          
SQL;


                $sHTML.=<<<HTML
                <tr>
                <td>$ticket_id</td>
                <td>
                
HTML;
                foreach ($this->oConn->query($sSQLAssignedBy) as $oRow)
                {
                    $assigned_by = $oRow['assigned_by'];
                    $sHTML.="$assigned_by";
                }
                $sHTML.=" </td>";


                $sHTML.="<td><ul>";
                foreach ($this->oConn->query($sSQLAssignedTo) as $oRow) {
                    $agents = $oRow['agent_name'];
                    $sHTML.="<li>$agents</li>";
                }

                $sHTML.="</ul></td>";


                $sHTML.= <<<HTML
                <td>$submitted_date</td>
                <td>$ticket_subject</td>
                <td>$ticket_category</td>
                <td>$ticket_priority</td>
                <td>$ticket_status_name</td>
                <td>
                    <a href="viewTicket.php?i_ticket_id=$ticket_id">View</a>
                </td>
            </tr>
HTML;

            }

            $sHTML .=<<<HTML
            </table>
            </div>
            
HTML;
            return $sHTML;
        }
        else //else return this text because there are no tickets for the user logged in.
        {
            return "<h3 class='text-muted'>Nothing to see here..</h3>";
        }


    }
    public function viewIndividualTicket($i_ticket_id)//view individual ticket function, display individual ticket.
    {
        $sSQL = <<<SQL
        SELECT 
	      T.ticket_id, T.submitted_date, T.ticket_subject, T.ticket_body, T.location, T.category_id, C.category_name, T.priority_id, P.priority_name, T.status_id, S.status_name, CONCAT(A.f_name, " ", A.l_name) AS agent_name, CL.client_fullname, CL.client_email  
        FROM
	      tbl_ticket T
        INNER JOIN
	      tbl_priority P
        ON 
	      T.priority_id = P.priority_id
        INNER JOIN
	      tbl_status S
        ON
	      T.status_id = S.status_id
        INNER JOIN 
	      tbl_category C
        ON 
	      T.category_id = C.category_id
        INNER JOIN
	      tbl_agent_ticket AGT
        ON 
	      T.ticket_id = AGT.ticket_id
        INNER JOIN 
	      tbl_agent A
        ON 
	      AGT.agent_id = A.agent_id
	    INNER JOIN
	      tbl_client CL
	    ON
	      T.client_id = CL.client_id
        WHERE
	      T.ticket_id = $i_ticket_id;
	   
SQL;

        $sSQLAssignedBy = <<<SQL
        SELECT 
          CONCAT(f_name, " ", l_name) as assigned_by
        FROM
          tbl_ticket T
        INNER JOIN
          tbl_agent A
        ON 
          T.assigned_by = A.agent_id
        WHERE
          T.ticket_id = $i_ticket_id ;
SQL;


        //pagination for comment, couldn't get the other pagination working for the ticket comments so this one is just forward and back arrows.
        define("Kbatchsize", 6);
        $negative =  (isset($_POST['cmd_prev']));

        if ($negative )
            $batchsize = Kbatchsize*-1;
        else
            $batchsize = Kbatchsize;

        if(isset($_POST['start_pos']))
        {
            $startlimit = $_POST['start_pos'] + $batchsize;
        }
        else
        {
            $startlimit = 0;
        }


        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->execute();
        $comment_box = $this->buildCommentBox($i_ticket_id); //calls the build comment box and the ticket id clicked on is passed so it knows which ticket to place the comment on.
        $show_comments = $this->showTicketComments($i_ticket_id, $startlimit, Kbatchsize); //calls the showticket comment method and passes the ticket id clicked on and retrieves any comment for that tiket if there are any.

        $sHTML = "";
        $actionpage = "";
        $ticket_id = "";
        $submitted_date = "";
        $ticket_subject = "";
        $ticket_body = "";
        $ticket_location = "";
        $ticket_category_id = "";
        $ticket_priority_id = "";
        $ticket_status_id = "";
        $ticket_client = "";
        $client_email = "";

        foreach ($this->oConn->query($sSQL) as $oRow)
        {
            $ticket_id = $oRow['ticket_id'];
            $ticket_client = $oRow['client_fullname'];
            $client_email = $oRow['client_email'];
            $submitted_date = $oRow['submitted_date'];
            $ticket_subject = $oRow['ticket_subject'];
            $ticket_body = $oRow['ticket_body'];
            $ticket_location = $oRow['location'];
            $ticket_category_id = $oRow['category_id'];
            $ticket_priority_id = $oRow['priority_id'];
            $ticket_status_id = $oRow['status_id'];

        }

        //sql for displaying the ticket attachment
        $sSQLTicketAttachment=<<<SQL
        SELECT
	      A.attachment_name, A.attachment_location
        FROM
	      tbl_attachment A
        INNER JOIN
	      tbl_ticket_attachment TA
        ON
	      A.attachment_id = TA.attachment_id
        INNER JOIN
	      tbl_ticket T
        ON
	      TA.ticket_id = T.ticket_id
        WHERE
	      T.ticket_id = $ticket_id;
SQL;

        $oStmtTicketAttachments = $this->oConn->prepare($sSQLTicketAttachment);
        $oStmtTicketAttachments->execute();

        //intializes the dropdowns
        $oDropdownCategory = new cDropdown($this->oConn, "SELECT category_id, category_name FROM tbl_category;");
        $oDropdownCategory->setName("dropDownCategory");
        $oDropdownCategory->setHTMLID("dropDownCategory");
        $oDropdownCategory->setIDField("category_id");
        $oDropdownCategory->setDescriptionField("category_name");
        $oDropdownCategory->setDefaultID($ticket_category_id);
        $sDropdownCategory = $oDropdownCategory->HTML();

        $oDropdownPriority = new cDropdown($this->oConn, "SELECT priority_id, priority_name FROM tbl_priority;");
        $oDropdownPriority->setName("dropDownPriority");
        $oDropdownPriority->setHTMLID("dropDownPriority");
        $oDropdownPriority->setIDField("priority_id");
        $oDropdownPriority->setDescriptionField("priority_name");
        $oDropdownPriority->setDefaultID($ticket_priority_id);
        $sDropdownPriority = $oDropdownPriority->HTML();

        $oDropdownStatus = new cDropdown($this->oConn, "SELECT status_id, status_name FROM tbl_status;");
        $oDropdownStatus->setName("dropDownStatus");
        $oDropdownStatus->setHTMLID("dropDownStatus");
        $oDropdownStatus->setIDField("status_id");
        $oDropdownStatus->setDescriptionField("status_name");
        $oDropdownStatus->setDefaultID($ticket_status_id);
        $sDropdownStatus = $oDropdownStatus->HTML();



        $sHTML .=<<<HTML
            <div class="page-header">
			<h1><span class="label label-warning">#$ticket_id</span> | $ticket_subject</h1>
		    </div>
		    <div class="row">
		    <div class="col-md-2">
		    <h4>Assigned By</h4>
		    
HTML;
        foreach ($this->oConn->query($sSQLAssignedBy) as $oRow) //gets the agentwho created the ticket and displays it.
        {
            $assigned_by = $oRow['assigned_by'];
            $sHTML .=<<<HTML
            
            <h4 ><span class="label label-danger">$assigned_by</span></h4>
            </div>
            
HTML;
        }

        $sHTML.=<<<HTML
            <div class="col-md-6">
            <h4>Assigned Technician(s):</h4>
HTML;

        foreach ($this->oConn->query($sSQL) as $oRow) { //loops through each technicians asigned to the ticket and dsiplays them
            $agent_name = $oRow['agent_name'];

            $sHTML .=<<<HTML
            <h4>
                <span class="label label-info">$agent_name</span>
            </h4>
HTML;

        }

        $sHTML.=<<<HTML
        </div>
        </div>
HTML;
        //dsiplays ticket information
        $sHTML .= <<<HTML
            <br>
          <div class="panel panel-default">
          <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <h4><b>Client:</b></h4>
                    <h3> $ticket_client</h3>
                    <p class="help-block">$client_email</p>
                </div>
            </div>
            <hr>
            
            <div class="row">
                <div class="col-md-3">
                    <h5>Subject</h5>
                    <h4><b>$ticket_subject</b></h4>
                </div>
                <div class="col-md-3">
                    <h5>Submitted</h5>
                    <h4><b>$submitted_date</b></h4>
                 </div>
                <div class="col-md-3">
                    <h5>Location</h5>
                    <h4><b>$ticket_location</b></h4>
                </div>
            </div>
            <br>
            <br>
            <h5>Body</h5>
            <div class="well well-lg">
                <div class="wordwrap">
                   <blockquote>$ticket_body</blockquote>
                </div>
            </div>
            <br>    
            
HTML;
        // if there are any ticket attahcments for the ticket clicked, display each one.
        if($oStmtTicketAttachments->rowCount())
        {
            $sHTML.=<<<HTML
            <hr>
            <p class="text-muted">Ticket Attachments:</p>
            <div class="row">
            <div class="col-md-3">
            <div class="list-group">
HTML;

            foreach ($this->oConn->query($sSQLTicketAttachment) as $oRow)
            {
                $ticket_attachment_name = $oRow['attachment_name'];
                $ticket_attachment_location = $oRow['attachment_location'];

                $sHTML.=<<<HTML
                 <a class="list-group-item list-group-item-info" href='$ticket_attachment_location' target="_blank">
                         <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                         $ticket_attachment_name
                 </a>
HTML;
            }

            $sHTML.=<<<HTML
            </div>
            </div>
            </div>
            
HTML;
        }


        $sHTML.=<<<HTML
            <hr>
            <form  method="post">
                <div class="row">
                    <div class="col-md-3"><label>Category:</label>$sDropdownCategory</div>
                    <div class="col-md-3"><label>Priority:</label>$sDropdownPriority</div>
                    <div class="col-md-3"><label>Status:</label>$sDropdownStatus</div>
                    <div class="col-md-3"><input class="btn btn-default" type="submit" name="btn_update" value="Update"></div>
                </div>
            </form>
          </div>
          </div>
HTML;



        $sHTML.=<<<HTML
        $comment_box
        $show_comments
        <form method="POST">
            <input type='hidden' value='$startlimit' name='start_pos'>
          	<input class="btn btn-default" type='submit' name='cmd_prev' value='<'>
			<input class="btn btn-default" type='submit' name='cmd_next' value='>'>
        </form>
HTML;


        //for the updating the ticket category, status and priority. if it's clicked, update them.
        if (isset($_POST['btn_update']))
        {
            $new_category = $_POST['dropDownCategory'];
            $new_priority= $_POST['dropDownPriority'];
            $new_status = $_POST['dropDownStatus'];

            $this->updateCurrentTicket($ticket_id, $new_category, $new_priority, $new_status ); //calls the update current ticket method and passes the new values and the ticket id.
        }
        return $sHTML;


    }
    //updates ticket category, priority and status
    public function updateCurrentTicket($i_ticket_id, $i_new_category, $i_new_priority, $i_new_status)
    {

        if($i_new_status == 'close')
        {
            $closed_status = date('Y-m-d H:i:s');
        }
        else
        {
            $closed_status = null;
        }

        $sSQL =<<<SQL
        UPDATE
	      tbl_Ticket T
        SET
	      T.category_id = :new_category, T.priority_id = :new_priority, T.status_id = :new_status, T.closed_date = :closed_date
        WHERE
	      T.ticket_id = :ticket_id;

SQL;

        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->bindParam(':new_category', $i_new_category);
        $oStmt->bindParam(':new_priority', $i_new_priority);
        $oStmt->bindParam(':new_status', $i_new_status);
        $oStmt->bindParam(':closed_date', $closed_status);
        $oStmt->bindParam(':ticket_id', $i_ticket_id);
        $oStmt->execute();

        header('refresh:0');
        echo"<script>alert('Ticket successfully updated!')</script>";

    }
    //builds the comment box to post comment on.
    public function buildCommentBox($i_ticket_id)
    {

        $agent_id = $_SESSION['user_id'];


        $oDropdownCommentType = new cDropdown($this->oConn, "SELECT comment_type_id, comment_type_name FROM tbl_comment_type;");
        $oDropdownCommentType->setName("dropDownCommentType");
        $oDropdownCommentType->setHTMLID("dropDownCommentType");
        $oDropdownCommentType->setIDField("comment_type_id");
        $oDropdownCommentType->setDescriptionField("comment_type_name");
        $oDropdownCommentType->setDefaultID('cmnt');
        $sDropdownCommentType = $oDropdownCommentType->HTML();


        if(isset($_POST['post_ticket_comment']) &&(!empty($_POST['ticket_comment'])))
        {
            $comment_type = $_POST['dropDownCommentType'];
            $comment = $_POST['ticket_comment'];

            $sSQL = <<<SQL
            INSERT INTO
	          tbl_comment(user_comment, comment_datetime, comment_type_id, agent_id)
              VALUES(:comment, now(), :comment_type, :agent);
    
            SET @COMMENT_ID = LAST_INSERT_ID();

            INSERT INTO
	          tbl_ticket_comment(comment_id, ticket_id)
              VALUES(@COMMENT_ID, $i_ticket_id);
SQL;
            $oStmt = $this->oConn->prepare($sSQL);
            $oStmt->bindParam(':comment', $comment);
            $oStmt->bindParam(':comment_type', $comment_type);
            $oStmt->bindParam(':agent', $agent_id);
            $oStmt->execute();
            $oLastCommentID = $this->oConn->lastInsertID();


            $oUploadCommentAttachments = new cMultiUpload('comment_attachment');
            $oUploadCommentAttachments->setSql("
            INSERT INTO
              tbl_attachment(attachment_name, attachment_location)
                VALUES(:file_name, :file_location);
                                      
            SET @ATT_ID = LAST_INSERT_ID();
                                    
            INSERT INTO
              tbl_attachment_comment(attachment_id, comment_id)
                VALUES(@ATT_ID, $oLastCommentID);
        ");
            $oUploadCommentAttachments->upload();


        }

        return $sHTML =<<<HTML
        
        <div class="panel panel-default">
         <div class="panel-body">
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
  				<label>Comment</label>
  				<textarea class="form-control" rows="5" name="ticket_comment"></textarea>
			</div>
			<div class="form-group">
			    <div class="row">
                    <div class="col-md-2">
                        <label>Comment Type</label>
                        $sDropdownCommentType 
                    </div>
                    <div class="col-md-2">
                        <label>Add attachments</label>
                        <input type="file" name="comment_attachment[]" multiple>
                    </div>
                </div>
            </div>
			<input class="btn btn-info" type="submit" name="post_ticket_comment" value="Post">
        </form>
        <br>
       
        </div>
        </div>
        <hr>
        
HTML;

    }
    //shows each ticket comment for a certain ticket.
    public function showTicketComments($i_ticket_id, $startlimit, $batchsize)
    {
        $sSQLComments = <<<SQL
        SELECT 
	      T.ticket_id, C.comment_id, C.comment_datetime,CT.comment_type_name, C.user_comment, CONCAT(A.f_name, " ", A.l_name) as agent_name, ATT.attachment_location
        FROM
	      tbl_ticket T
        INNER JOIN
	      tbl_ticket_comment TC
        ON
	      T.ticket_id = TC.ticket_id
        INNER JOIN
	      tbl_comment C
        ON
	      TC.comment_id = C.comment_id
        INNER JOIN
	      tbl_agent A
        ON
	      C.agent_id = A.agent_id
        INNER JOIN
	      tbl_comment_type CT
        ON
	      C.comment_type_id = CT.comment_type_id
        LEFT JOIN
	      tbl_attachment_comment AC
        ON
	      C.comment_id = AC.comment_id
        LEFT JOIN
	      tbl_attachment ATT
        ON
	      AC.attachment_id = ATT.attachment_id
        WHERE
	      T.ticket_id = $i_ticket_id
        GROUP BY
	      C.comment_id
        ORDER BY
	      CT.comment_type_id DESC, C.comment_datetime DESC
	    LIMIT
	      $startlimit, $batchsize;
        
        
SQL;

        $oStmtComments = $this->oConn->prepare($sSQLComments);
        $oStmtComments->execute();

        $sHTML = "";
        if($oStmtComments->rowCount())
        {

            foreach ($this->oConn->query($sSQLComments) as $oRow) {
                $comment_id = $oRow['comment_id'];
                $agent_name = $oRow['agent_name'];
                $comment = $oRow['user_comment'];
                $comment_date = $oRow['comment_datetime'];
                $comment_type = $oRow['comment_type_name'];


                $sSQL2 = <<<SQL
                SELECT
	              A.attachment_name, A.attachment_location, C.comment_id
                FROM 
	              tbl_attachment A
                INNER JOIN
	              tbl_attachment_comment AC
                ON
                  A.attachment_id = AC.attachment_id
                INNER JOIN
	              tbl_comment C
                ON
                  AC.comment_id = C.comment_id
                WHERE
                  C.comment_id = $comment_id; 
                  
                 
SQL;

                $oStmtAttachments = $this->oConn->prepare($sSQL2);
                $oStmtAttachments ->execute();




                if($comment_type=="Solution")
                {
                    $sHTML .= <<<HTML
                <h4><span class="label label-success"><span class="glyphicon glyphicon-star" aria-hidden="true"> </span>$comment_type</span></h4><p> <b>$agent_name</b> on <small>$comment_date</small></p>
                <div class="wordwrap">
                    <blockquote>$comment <br></blockquote>
                </div>
                
HTML;
                }
                else
                {
                    $sHTML .= <<<HTML
                    <h4><span class="label label-info">$comment_type</span></h4><p> <b>$agent_name</b> on <small>$comment_date</small></p>
                    <div class="wordwrap">
                    <blockquote>$comment <br></blockquote>
                    </div>
HTML;
                }

                if($oStmtAttachments ->rowCount())
                {
                    $sHTML .=<<<HTML
                <p class="text-muted">Attachments:</p>
                <div class="row">
                <div class="col-md-3">
                <div class="list-group">
HTML;
                    foreach ($this->oConn->query($sSQL2) as $oRow) {
                        $attachment_location = $oRow['attachment_location'];
                        $attachment_name = $oRow['attachment_name'];

                        $sHTML .=<<<HTML
                     
                    <a class="list-group-item list-group-item-info" href='$attachment_location' target="_blank">
                         <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                        $attachment_name
                    </a>
                    
                    
HTML;
                    }

                    $sHTML .=<<<HTML
                </div>
                </div>
                </div>

HTML;
                }

                $sHTML.="<hr>";

            }
        }
        else
        {
            $sHTML .= "<h4 class='text-muted'>No comments yet.</h4>";
        }

        return $sHTML;
    }
    //add client function, replaced with ajax
//    public function addClient($i_client_fullname, $i_client_email)
//    {
//        $sSQL=<<<SQL
//        INSERT INTO
//	      tbl_client(client_fullname, client_email)
//        VALUES
//        (:client_fullname, :client_email);
//SQL;
//
//        $oStmt = $this->oConn->prepare($sSQL);
//        $oStmt->bindParam(':client_fullname', $i_client_fullname);
//        $oStmt->bindParam(':client_email', $i_client_email);
//        $oStmt ->execute();
//
//        echo "<script>alert('Client successfully added!')</script>";
//    }
    //alla ctive tickets method, just displays all the active ticket.
    public function allActiveTickets($start, $perPage)
    {
        $sSQL =<<<SQL
       SELECT 
	      SQL_CALC_FOUND_ROWS T.ticket_id, T.submitted_date,  T.ticket_subject, C.category_id, C.category_name,  P.priority_id, P.priority_name, S.status_id, S.status_name, A.f_name
        FROM
	      tbl_ticket T
        INNER JOIN
	      tbl_priority P
        ON 
	      T.priority_id = P.priority_id
        INNER JOIN
	      tbl_status S
        ON
	      T.status_id = S.status_id
        INNER JOIN 
	      tbl_category C
        ON 
	      T.category_id = C.category_id
        INNER JOIN
	      tbl_agent_ticket AGT
        ON 
	      T.ticket_id = AGT.ticket_id
        INNER JOIN 
	      tbl_agent A
        ON 
	      AGT.agent_id = A.agent_id
        WHERE 
	      S.status_id != 'close'
	    GROUP BY
	      T.ticket_id
	    ORDER BY
	      T.submitted_date DESC, T.ticket_id DESC
	    LIMIT 
	      {$start}, {$perPage};

SQL;
        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt ->execute();

        $total = $this->oConn->query("SELECT FOUND_ROWS() as total")->fetch()['total'];
        $this->pages = ceil($total / $perPage);

        if($oStmt->rowCount())
        {
            $sHTML =<<<HTML
            <div class ="table-responsive">
            <table class="table table-hover ">
                <tr>
                    <th>Ticket ID</th>
                    <th>Assigned by</th>
                    <th>Assigned agent(s)</th>
                    <th>Submitted Date</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>View</th>
                
                </tr>
               
HTML;

            foreach ($this->oConn->query($sSQL) as $oRow) {
                $ticket_id = $oRow['ticket_id'];
                $submitted_date = $oRow['submitted_date'];
                $ticket_subject = $oRow['ticket_subject'];
                $ticket_category = $oRow['category_name'];
                $ticket_priority = $oRow['priority_name'];
                $ticket_status_name = $oRow['status_name'];


                $sSQLAssignedTo=<<<SQL
            SELECT
	          CONCAT(f_name, " ", l_name) AS agent_name
            FROM
	          tbl_ticket T
            INNER JOIN
	          tbl_agent_ticket AGT
            ON
	          T.ticket_id = AGT.ticket_id
            INNER JOIN
	          tbl_agent A
            ON
	          AGT.agent_id = A.agent_id
            WHERE
	          T.ticket_id = $ticket_id;

          
SQL;

                $sSQLAssignedBy = <<<SQL
        SELECT 
          CONCAT(f_name, " ", l_name) as assigned_by
        FROM
          tbl_ticket T
        INNER JOIN
          tbl_agent A
        ON 
          T.assigned_by = A.agent_id
        WHERE
          T.ticket_id = $ticket_id ;
SQL;


                $sHTML.= <<<HTML
            
               <tr>
                <td>$ticket_id</td>
                
                
                
HTML;
                $sHTML.="<td><ul>";
                foreach ($this->oConn->query($sSQLAssignedBy) as $oRow)
                {
                    $assigned_by = $oRow['assigned_by'];
                    $sHTML.="<li>$assigned_by</li>";
                }
                $sHTML.="</ul></td>";

                $sHTML.="<td><ul>";
                foreach ($this->oConn->query($sSQLAssignedTo) as $oRow) {
                    $agents = $oRow['agent_name'];
                    $sHTML.="<li>$agents</li>";
                }
                $sHTML.="</ul></td>";

                $sHTML.=<<<HTML
            
            <td>$submitted_date</td>
                <td>$ticket_subject</td>
                <td>$ticket_category</td>
                <td>$ticket_priority</td>
                <td>$ticket_status_name</td>
HTML;

                $sHTML.=<<<HTML
            <td>
                <a href="viewTicket.php?i_ticket_id=$ticket_id">View</a>
            </td>
            </tr>
HTML;
            }





            $sHTML .=<<<HTML
            </table>
            </div>
HTML;
        } else {
            $sHTML = "<h3 class='help-block'>Nothing to see here..</h3>";
        }



        return $sHTML;
    }
    //same as above except it's for the closed tickets and includes the closed date field.
    public function allClosedTickets($start, $perPage)
    {
        $sSQL =<<<SQL
       SELECT 
	      SQL_CALC_FOUND_ROWS T.ticket_id, T.submitted_date,  T.ticket_subject, T.closed_date, C.category_id, C.category_name,  P.priority_id, P.priority_name, S.status_id, S.status_name, A.f_name
        FROM
	      tbl_ticket T
        INNER JOIN
	      tbl_priority P
        ON 
	      T.priority_id = P.priority_id
        INNER JOIN
	      tbl_status S
        ON
	      T.status_id = S.status_id
        INNER JOIN 
	      tbl_category C
        ON 
	      T.category_id = C.category_id
        INNER JOIN
	      tbl_agent_ticket AGT
        ON 
	      T.ticket_id = AGT.ticket_id
        INNER JOIN 
	      tbl_agent A
        ON 
	      AGT.agent_id = A.agent_id
        WHERE 
	      S.status_id = 'close'
	    GROUP BY
	      T.ticket_id
	    ORDER BY
	      T.closed_date DESC, T.ticket_id DESC
	    LIMIT 
          {$start}, {$perPage};

SQL;
        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt ->execute();

        $total = $this->oConn->query("SELECT FOUND_ROWS() as total")->fetch()['total'];
        $this->pages = ceil($total / $perPage);

        if($oStmt->rowCount())
        {
            $sHTML =<<<HTML
            <div class ="table-responsive">
            <table class="table table-hover ">
                <tr>
                    <th>Ticket ID</th>
                    <th>Assigned by</th>
                    <th>Assigned agent(s)</th>
                    <th>Submitted Date</th>
                    <th>Closed Date</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>View</th>
                
                </tr>
               
HTML;

            foreach ($this->oConn->query($sSQL) as $oRow) {
                $ticket_id = $oRow['ticket_id'];
                $submitted_date = $oRow['submitted_date'];
                $closed_date = $oRow['closed_date'];
                $ticket_subject = $oRow['ticket_subject'];
                $ticket_category = $oRow['category_name'];
                $ticket_priority = $oRow['priority_name'];
                $ticket_status_name = $oRow['status_name'];


                $sSQLAssignedTo=<<<SQL
            SELECT
	          CONCAT(f_name, " ", l_name) AS agent_name
            FROM
	          tbl_ticket T
            INNER JOIN
	          tbl_agent_ticket AGT
            ON
	          T.ticket_id = AGT.ticket_id
            INNER JOIN
	          tbl_agent A
            ON
	          AGT.agent_id = A.agent_id
            WHERE
	          T.ticket_id = $ticket_id;

          
SQL;

                $sSQLAssignedBy = <<<SQL
        SELECT 
          CONCAT(f_name, " ", l_name) as assigned_by
        FROM
          tbl_ticket T
        INNER JOIN
          tbl_agent A
        ON 
          T.assigned_by = A.agent_id
        WHERE
          T.ticket_id = $ticket_id ;
SQL;


                $sHTML.= <<<HTML
            <tr>
                <td>$ticket_id</td>
                
                
                
HTML;
                $sHTML.="<td><ul>";
                foreach ($this->oConn->query($sSQLAssignedBy) as $oRow)
                {
                    $assigned_by = $oRow['assigned_by'];
                    $sHTML.="<li>$assigned_by</li>";
                }
                $sHTML.="</ul></td>";

                $sHTML.="<td><ul>";
                foreach ($this->oConn->query($sSQLAssignedTo) as $oRow) {
                    $agents = $oRow['agent_name'];
                    $sHTML.="<li>$agents</li>";
                }
                $sHTML.="</ul></td>";

                $sHTML.=<<<HTML
            
            <td>$submitted_date</td>
            <td>$closed_date</td>
                <td>$ticket_subject</td>
                <td>$ticket_category</td>
                <td>$ticket_priority</td>
                <td>$ticket_status_name</td>
HTML;

                $sHTML.=<<<HTML
            <td>
                <a href="viewTicket.php?i_ticket_id=$ticket_id">View</a>
            </td>
            </tr>
HTML;
            }





            $sHTML .=<<<HTML
            </table>
            </div>
HTML;
        } else {
            $sHTML = "<h3 class='help-block'>Nothing to see here..</h3>";
        }




        return $sHTML;
    }
    //edit ticket method
    public function editTicket($i_ticket_id)
    {
        //loads the default ticket values first
        $sSQL = <<<SQL
        SELECT 
	      T.ticket_id, T.client_id,CL.client_fullname, CL.client_email, T.submitted_date, T.ticket_subject, T.location, T.ticket_body, T.category_id, C.category_name,  T.priority_id, P.priority_name, T.status_id, S.status_name, CONCAT(A.f_name, " ", A.l_name) AS agent_name
        FROM
	      tbl_ticket T
        INNER JOIN
	      tbl_priority P
        ON 
	      T.priority_id = P.priority_id
        INNER JOIN
	      tbl_status S
        ON
	      T.status_id = S.status_id
        INNER JOIN 
	      tbl_category C
        ON 
	      T.category_id = C.category_id
        INNER JOIN
	      tbl_agent_ticket AGT
        ON 
	      T.ticket_id = AGT.ticket_id
        INNER JOIN 
	      tbl_agent A
        ON 
	      AGT.agent_id = A.agent_id
	    INNER JOIN
	      tbl_client CL
	    ON
	      T.client_id = CL.client_id
        WHERE
	      T.ticket_id = $i_ticket_id;
	   
SQL;


        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->execute();




        $sHTML = "";
        $ticket_id = "";
        $client_id = "";
        $submitted_date = "";
        $ticket_subject = "";
        $ticket_location = "";
        $ticket_body = "";
        $ticket_category_id = "";
        $ticket_priority_id = "";
        $ticket_status_id = "";
        foreach ($this->oConn->query($sSQL) as $oRow)
        {
            $ticket_id = $oRow['ticket_id'];
            $client_id = $oRow['client_id'];
            $submitted_date = $oRow['submitted_date'];
            $ticket_subject = $oRow['ticket_subject'];
            $ticket_location = $oRow['location'];
            $ticket_body = $oRow['ticket_body'];
            $ticket_category_id = $oRow['category_id'];
            $ticket_priority_id = $oRow['priority_id'];
            $ticket_status_id = $oRow['status_id'];

        }

        $oDropdownCategory = new cDropdown($this->oConn, "SELECT category_id, category_name FROM tbl_category;");
        $oDropdownCategory->setName("dropDownCategory");
        $oDropdownCategory->setHTMLID("dropDownCategory");
        $oDropdownCategory->setIDField("category_id");
        $oDropdownCategory->setDescriptionField("category_name");
        $oDropdownCategory->setDefaultID($ticket_category_id);
        $sDropdownCategory = $oDropdownCategory->HTML();

        $oDropdownPriority = new cDropdown($this->oConn, "SELECT priority_id, priority_name FROM tbl_priority;");
        $oDropdownPriority->setName("dropDownPriority");
        $oDropdownPriority->setHTMLID("dropDownPriority");
        $oDropdownPriority->setIDField("priority_id");
        $oDropdownPriority->setDescriptionField("priority_name");
        $oDropdownPriority->setDefaultID($ticket_priority_id);
        $sDropdownPriority = $oDropdownPriority->HTML();

        $oDropdownStatus = new cDropdown($this->oConn, "SELECT status_id, status_name FROM tbl_status;");
        $oDropdownStatus->setName("dropDownStatus");
        $oDropdownStatus->setHTMLID("dropDownStatus");
        $oDropdownStatus->setIDField("status_id");
        $oDropdownStatus->setDescriptionField("status_name");
        $oDropdownStatus->setDefaultID($ticket_status_id);
        $sDropdownStatus = $oDropdownStatus->HTML();

        $oDropdownAgent = new cDropdown($this->oConn, "SELECT agent_id, f_name, agent_privilege_id FROM tbl_agent;");
        $oDropdownAgent->setName("my-select[]");
        $oDropdownAgent->setHTMLID("my-select");
        $oDropdownAgent->setIDField("agent_id");
        $oDropdownAgent->setDescriptionField("f_name");
        $oDropdownAgent->setBMultiple(true);
        $oDropdownAgent->setBRequired(true);
        $oDropdownAgent->setSize(6);
        $sDropdownAgent = $oDropdownAgent->HTML();


        $sHTML .=<<<HTML
          
		    <br>
		    <b>Assigned Technician(s)</b>
HTML;

        foreach ($this->oConn->query($sSQL) as $oRow) {
            $agent_name = $oRow['agent_name'];

            $sHTML .="<h4><span class=\"label label-info\">$agent_name</span></h4>";
        }



        $sHTML .= <<<HTML
         <form id="frm_add_client" method="post"></form>
		<br>
		<form name="frm_new_ticket" method="POST" enctype="multipart/form-data">
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
				        <input class="form-control" type="text" name="new_ticket_subject" value="$ticket_subject" required/>
			        </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
				        <label>Enquirer/Problem Location</label>
				        <input class="form-control" type="text" name="new_ticket_location" value="$ticket_location" required/>
			        </div>
                 </div>
		        <div class="col-md-2">
		            <div class="form-group">
				        <label>Date Created</label>
				        <input class="form-control" type="date" name="new_ticket_date" value="$submitted_date" required/>
			        </div>
                </div>
		       
		    </div>
			<div class="form-group">
  				<label>Ticket Body</label>
  				<textarea class="form-control"  rows="5" name="new_ticket_body" required>$ticket_body</textarea>
			</div>
			<div class="form-group">
			    <label>Add Ticket Attachment(s)</label>
			    <input type="file" name="edit_attachment[]" multiple>
			</div>
HTML;

        $sSQLTicketAttachment=<<<SQL
        SELECT
	      A.attachment_name, A.attachment_location, A.attachment_id, T.ticket_id
        FROM
	      tbl_attachment A
        INNER JOIN
	      tbl_ticket_attachment TA
        ON
	      A.attachment_id = TA.attachment_id
        INNER JOIN
	    tbl_ticket T
        ON
	      TA.ticket_id = T.ticket_id
        WHERE
	      T.ticket_id = $i_ticket_id;
SQL;
        $oStmtTicketAttachments = $this->oConn->prepare($sSQLTicketAttachment);
        $oStmtTicketAttachments->execute();

        if($oStmtTicketAttachments->rowCount()) // if there are any attachments, display each one with a remove checkbox next to each one.
        {
            $sHTML.=<<<HTML
            <hr>
            <p class="text-muted">Ticket Attachments (Tick each attachment and click update to remove.)</p>
            <div class="row">
            <div class="col-md-3">
            <div class="list-group">
HTML;

            foreach ($this->oConn->query($sSQLTicketAttachment) as $oRow)
            {
                $ticket_attachment_id = $oRow['attachment_id'];
                $ticket_attachment_name = $oRow['attachment_name'];
                $ticket_attachment_location = $oRow['attachment_location'];

                $sHTML.=<<<HTML
                 <a class="list-group-item list-group-item-info" href='$ticket_attachment_location'>
                         <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                         $ticket_attachment_name
                 </a>
                 <p class="help-block">Remove<input type="checkbox" name="attachment[]" value="$ticket_attachment_id"></p>
                  
HTML;
            }

            $sHTML.=<<<HTML
            </div>
            </div>
            </div>
            
HTML;
        }


        $sHTML.=<<<HTML
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
		
         <p class="help-block">Warning: Once the update button is clicked, the ticket will be updated this action cannot be reversed.</p>
          <input class="btn btn-warning" type="submit" name="btn_update" value="Update Ticket">
          
		</form>
		<br>
		
		<script src="MultiSelect/js/jquery.multi-select.js"></script> 
	<script>
	    $('#my-select').multiSelect();
	    
	     $.ajax({
	        type: 'GET',
	        data: 'client_id='+$client_id,
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
	            }
	        });
	    }
	    
    </script>
           
HTML;

//        if(isset($_POST['btn_add_client'])) //replaces with ajax.
//        {
//            $client_fullname = $_POST['add_client_full_name'];
//            $client_email = $_POST['add_client_email'];
//
//            $this->addClient($client_fullname, $client_email);
//            header('refresh:0');
//        }
        if (isset($_POST['btn_update'])) //if the update button is clicked
        {

            if(isset($_POST['attachment'])) //if a ticket attachment remove checkbox is ticked remove all the ticked ones.
            {
                $arr_attachment_to_remove = $_POST['attachment'];
                foreach($arr_attachment_to_remove as $attachment)
                {
                    $this->removeIndividualTicketAttachment($attachment);
                }
                echo "<script>alert('Atttachment(s) removed!')</script>";
            }

            if(!isset($_POST['my-select'])) //if there is no agent selected, display alert and kill page (just for backup, as it's a required field)
            {
                echo"<script>alert('You must re-assign new agents to this ticket.')</script>";
                die();

            }
            else
            {
                $new_agents = $_POST['my-select']; //gets the newly selected agents
            }

            //collects all the edited (or unedited) info
            $new_client = $_POST['dropDownClient'];
            $new_date = $_POST['new_ticket_date'];
            $new_subject = $_POST['new_ticket_subject'];
            $new_location = $_POST['new_ticket_location'];
            $new_body = $_POST['new_ticket_body'];
            $new_category = $_POST['dropDownCategory'];
            $new_priority= $_POST['dropDownPriority'];
            $new_status = $_POST['dropDownStatus'];

            $this->adminEditTicket($ticket_id, $new_client, $new_date, $new_subject, $new_location, $new_body, $new_agents, $new_category, $new_priority, $new_status); //pass them in edit ticket method which updates the ticket info.
            
            //For attachments, if a user selected files in the edit ticket, upload them.
            $oUploadEditTicketAttachments = new cMultiUpload('edit_attachment');
            $oUploadEditTicketAttachments->setSql("
            INSERT INTO
              tbl_attachment(attachment_name, attachment_location)
              VALUES(:file_name, :file_location);

            SET @ATT_ID = LAST_INSERT_ID();

            INSERT INTO
            tbl_ticket_attachment(ticket_id, attachment_id)
            VALUES($ticket_id, @ATT_ID);
        ");
            $oUploadEditTicketAttachments->upload();



        }
        return $sHTML;


    }
    //edit ticket method, just updates the current ticket.
    public function adminEditTicket($ticket_id, $new_client, $new_date, $new_subject, $new_location, $new_body, $new_agents, $new_category, $new_priority, $new_status)
    {
        if($new_status == 'close')
        {
            $closed_status = date('Y-m-d H:i:s');
        }
        else
        {
            $closed_status = null;
        }
        $sSQL =<<<SQL
        UPDATE
	      tbl_Ticket T
        SET
	      T.submitted_date = :new_date, T.ticket_subject = :new_subject, T.location = :new_location, T.closed_date = :closed_date, T.ticket_body = :new_body, T.client_id = :new_client, T.category_id = :new_category, T.priority_id = :new_priority, T.status_id = :new_status
        WHERE
	      T.ticket_id = :ticket_id;
	      
	    DELETE FROM
	      tbl_agent_ticket
        WHERE
	      ticket_id = :ticket_id;
	      
	    
        
SQL;

        foreach($new_agents as $new_agent)
        {
            $sSQL.=<<<SQL
            INSERT INTO
	          tbl_agent_ticket(agent_id, ticket_id)
            VALUES
            ($new_agent, $ticket_id);
          
SQL;

        }

        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->bindParam(':new_date', $new_date);
        $oStmt->bindParam(':new_subject', $new_subject);
        $oStmt->bindParam(':new_location', $new_location);
        $oStmt->bindParam(':closed_date', $closed_status);
        $oStmt->bindParam(':new_body', $new_body);
        $oStmt->bindParam(':new_client', $new_client );
        $oStmt->bindParam(':ticket_id', $ticket_id);
        $oStmt->bindParam(':new_category', $new_category);
        $oStmt->bindParam(':new_priority', $new_priority);
        $oStmt->bindParam(':new_status', $new_status);
        $oStmt->bindParam(':ticket_id', $ticket_id);
        $oStmt->execute();

        header('refresh:0');

    }
    //removes each ticket attachment
    public function removeIndividualTicketAttachment($i_attachment_id)
    {
        $sSQL=<<<SQL
        DELETE 
	    FROM
          tbl_ticket_attachment
        WHERE attachment_id = $i_attachment_id;
SQL;
        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->execute();

    }

    //returns the last seven days report, takes in the where (open or close) and the and clause.
    function lastSevenDaysReport($i_where_clause, $i_andclause)
    {
        $sSQL =<<<SQL
          
        SELECT
	      COUNT(*) AS the_counter
        FROM
	      tbl_ticket T
        WHERE
	      $i_where_clause
        AND
	      $i_andclause
SQL;

        foreach ($this->oConn->query($sSQL) as $oRow)
        {
            $numberOfTickets = $oRow['the_counter'];
        }

        $sHTML =<<<HTML
        $numberOfTickets
HTML;

        return $sHTML;
    }

}
