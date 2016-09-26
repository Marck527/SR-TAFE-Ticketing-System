<?php

/**
 * Created by PhpStorm.
 * User: Marck
 * Date: 19/08/2016
 * Time: 8:53 PM
 */


class cManageUser
{
    private $oConn; //declares a private variable which will hold the database object.
    //The constructor. takes an incoming database object.
    function __construct($i_oConn)
    {
        $this->oConn = $i_oConn; //This oConn in this class (private $oConn) will become whatever is passed in the constructor. So '$this->oConn' will now hold our database connection.
    }
    //this method displays all the users, also handles the filtering.
    public function listUsers($i_privilege_id, $i_username_search)
    {
        if($i_privilege_id == '0') //if the privilege id passed is zero (default)
        {
            $i_privilege_id = "'%'"; //then search the database with a privilege id of wildcard, meaning it will show all.
        }
        if(isset($i_username_search)) //if the something is entered in the search user field,
        {
            $search_username_clause = "AND A.f_name LIKE '%$i_username_search%' "; //add this to the query to try and find a match
        }
        else //else if it's empty, don't include the username clause.
        {
            $search_username_clause = null;
        }

        $sSQL=<<<SQL
        SELECT
	      A.agent_id, A.f_name, A.l_name, A.agent_privilege_id, AP.privilege_title
        FROM
	      tbl_agent A
        INNER JOIN
	      tbl_agent_privilege AP
        ON
	      A.agent_privilege_id = AP.agent_privilege_id
	    WHERE
	      A.agent_privilege_id LIKE $i_privilege_id
	      $search_username_clause;

	      
SQL;
        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->execute();

        if($oStmt->rowCount())
        {
            $sHTML=<<<HTML
        
        <div class ="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>Username</th>
                <th>Access</th>
                <th>Action</th>
            </tr>
            
HTML;

            foreach ($this->oConn->query($sSQL) as $oRow)
            {
                $user_id = $oRow['agent_id'];
                $user_name = $oRow['f_name'] . ' ' . $oRow['l_name'];
                $user_privilege = $oRow['privilege_title'];

                $sHTML.=<<<HTML
            <tr>
                <td>$user_name</td>
                <td>$user_privilege</td>
                <td><a href="userInformation.php?iUserID=$user_id">View / Edit</a> </td>
            </tr>
HTML;
            }

            $sHTML .=<<<HTML
            
        </table>
        </div>
HTML;
        }
        else
        {
            $sHTML="<p class='help-block'>No results found</p>";
        }

    return $sHTML;
    }
    public function userViewForm($i_submit_value = null, $i_first_name = null, $i_last_name = null, $i_email = null,  $i_privilege_id = null ) //user view form, multi use. Used in the register page as well as the edit user page.
    {

        $oDropdownPrivilege = new cDropdown($this->oConn, "SELECT agent_privilege_id, privilege_title FROM tbl_agent_privilege;");
        $oDropdownPrivilege->setName("dropDownPrivilege");
        $oDropdownPrivilege->setHTMLID("privilege");
        $oDropdownPrivilege->setDefaultID($i_privilege_id);
        $oDropdownPrivilege->setIDField("agent_privilege_id");
        $oDropdownPrivilege->setDescriptionField("privilege_title");
        $oDropdownPrivilege->setBRequired(true);
        $sDropdownPrivilege = $oDropdownPrivilege->HTML();

        return <<<HTML
        <form name="frm_register"  method="POST" onsubmit= " return regValidator()">
            <div class="row">
            <div class="col-md-6">
                <div class="form-group">
				<label for="firstName">First Name</label>
				<input class="form-control" id="firstName" type="text" name="txt_f_name" placeholder="eg. John" value="$i_first_name"  required>
			    </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
				<label for="lastName">Last Name</label>
				<input class="form-control" id="lastName" type="text" name="txt_l_name" placeholder="eg. Doe" value="$i_last_name" required>
			    </div>
            </div>
            </div>
			<div class="row">
			    <div class="col-md-6">
			        <div class="form-group">
				    <label for="emailAddress">Email address</label>
				    <input class="form-control" id="emailAddress" type="email" name="txt_email" placeholder="Rocket.Dog@example.com" value="$i_email" required>
			        </div>
			    </div>
			</div>
			<div class="row">
			    <div class="col-md-6">
			       
			    </div>
			</div>
			<div class="row">
			    <div class="col-md-6">
			       <div class="form-group">
				    <label for="passWord">Password</label>
				    <input class="form-control" id="passWord" type="password" name="txt_chosen_pword" placeholder="Choose a password"  required>
			        </div>
			    </div>
			</div>
			<div class="row">
			    <div class="col-md-6">
			       <div class="form-group">
				    <label for="passWordRepeat">Re-enter Password</label>
				    <input class="form-control" id="passWordRepeat" type="password" name="txt_chosen_pword_rpt" placeholder="Repeat password"  required>
			        </div>
			    </div>
			</div>
			<div class="row">
			    <div class="col-md-2">
			      <div class="form-group">
				    <label for="privilege">Select Privilege</label>
				    $sDropdownPrivilege
			        </div>
			    </div>
			</div>
			
			
			
			
			
			
			<input class="btn btn-primary" type="submit" name="btn_pressed" value="$i_submit_value"/>
		</form>
HTML;

    }
    public function deleteUser($i_user_id) //delete user function
    {
        
        $sSQL =<<<SQL
        DELETE FROM 
          tbl_agent
        WHERE
          tbl_agent.agent_id = $i_user_id;
SQL;


        if(isset($_POST['btn_delete_user']))
        {
            $oStmt = $this->oConn->prepare($sSQL);
            $oStmt->execute();

            header('location: userManager.php');


        }

        return <<<HTML
        <form method="post" onsubmit= " return logoutOK()">
            <input class="btn btn-danger" type="submit" name="btn_delete_user" value="Delete User">
        </form>
HTML;

    }
    
    public function editUser($i_user_id) //edit user function
    {

        if(isset($_POST['btn_pressed']))
        {
            $updated_firstname = $_POST['txt_f_name'];
            $updated_lastname = $_POST['txt_l_name'];
            $updated_email = $_POST['txt_email'];
            $updated_password = $_POST['txt_chosen_pword'];
            $updated_privilege = $_POST['dropDownPrivilege'];

            $this->updateExitingUser($i_user_id, $updated_firstname, $updated_lastname, $updated_email, $updated_password, $updated_privilege);
        }
        $sSQL=<<<SQL
        SELECT
          A.agent_id, A.f_name, A.l_name, A.email, A.agent_privilege_id, A.agent_password
        FROM
          tbl_agent A
        WHERE
          A.agent_id = $i_user_id;
SQL;
        foreach ($this->oConn->query($sSQL) as $oRow)
        {
            $first_name = $oRow['f_name'];
            $last_name = $oRow['l_name'];
            $password = $oRow['agent_password'];
            $email = $oRow['email'];
            $privilege_id = $oRow['agent_privilege_id'];
        }
        $view_form = $this->userViewForm("Update", $first_name, $last_name, $email, $privilege_id );
        $delete_user = $this->deleteUser($i_user_id);
        $sHTML =<<<HTML
        $view_form
        <hr>
        $delete_user
        
        
HTML;

        return $sHTML;
    }

    public function updateExitingUser($i_user_id, $i_new_firstname, $i_new_lastname, $i_new_email, $i_new_password, $i_new_privilege_id)
    {
        $hashed_pw = password_hash($i_new_password, PASSWORD_DEFAULT);

            $sSQL =<<<SQL
        UPDATE
	      tbl_agent A
        SET
	      A.f_name = :new_f_name, A.l_name = :new_l_name, A.email = :new_email, A.agent_password = :new_password, A.agent_privilege_id = :new_privilege_id
        WHERE
	      A.agent_id = :user_id;
SQL;
            $oStmt = $this->oConn->prepare($sSQL);

            $oStmt->bindParam(':new_f_name', $i_new_firstname); //Binds the question marks in the SQL statement to there actual values.
            $oStmt->bindParam(':new_l_name', $i_new_lastname);
            $oStmt->bindParam(':new_email', $i_new_email);
            $oStmt->bindParam(':new_password', $hashed_pw);
            $oStmt->bindParam(':new_privilege_id', $i_new_privilege_id);
            $oStmt->bindParam(':user_id', $i_user_id);
            $oStmt->execute();


    }
    public function registerUser($i_fname, $i_lname, $i_email_addr, $i_chosen_pword, $i_privilege) //Accepts the following user information
    {
        $hashed_pw = password_hash($i_chosen_pword, PASSWORD_DEFAULT); //The password entered by the user in the registration form is hashed.

        if($this->checkEmail($i_email_addr)) //Same idea as above.
        {
            echo"<script>alert(\"Oops! that email is already taken !\");</script>";
        }
        else //Else the username and email is not already in the database, insert the entered info in the database.
        {
            //The SQL insert statement
            $sSQL = <<<SQL
            INSERT INTO
              tbl_agent
              (f_name, l_name, email, agent_password, agent_privilege_id)
            VALUES
              (?, ?, ?, ?, ?);
              
SQL;
            $oStmt = $this->oConn->prepare($sSQL); //The SQL statement above is prepared.

            $oStmt->bindParam(1, $i_fname); //Binds the question marks in the SQL statement to there actual values.
            $oStmt->bindParam(2, $i_lname);
            $oStmt->bindParam(3, $i_email_addr);
            $oStmt->bindParam(4, $hashed_pw);
            $oStmt->bindParam(5, $i_privilege);
            $oStmt->execute(); //The SQL is executed.

            echo"<script>alert(\"Successfully registered agent!\");</script>";
        }
    }
    //This functions takes the username entered by the user in the registration form and checks for duplicates, if there is a duplicate, it return true.

    public function checkEmail($i_email) //checks for any doubled up email addresses.
    {
        $sSQL = <<<SQL
        SELECT
          *
        FROM
          tbl_agent A
        WHERE
           A.email = ?;
SQL;
        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->bindParam(1, $i_email);
        $oStmt->execute();

        if($oStmt->rowCount() > 0)
        {
            return true;
        }
    }

}