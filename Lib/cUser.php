<?php

/*
 * Author: Marck Munoz
 * Date: 27/07/2016
 * Comments: Trying out OOP in PHP.
 */

class cUser //User class. Handles registration and login.
{
    private $oConn; //declares a private variable which will hold the database object.
    //The constructor. takes an incoming database object.
    function __construct($i_oConn)
    {
        $this->oConn = $i_oConn; //This oConn in this class (private $oConn) will become whatever is passed in the constructor. So '$this->oConn' will now hold our database connection.
    }

    //The register function
    public function register($fname, $lname, $email_addr, $chosen_pword, $privilege ) //Accepts the following user information
    {
        $hashed_pw = password_hash($chosen_pword, PASSWORD_DEFAULT); //The password entered by the user in the registration form is hashed.

      if($this->checkEmail($email_addr)) //Same idea as above.
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

            $oStmt->bindParam(1, $fname); //Binds the question marks in the SQL statement to there actual values.
            $oStmt->bindParam(2, $lname);
            $oStmt->bindParam(3, $email_addr);
            $oStmt->bindParam(4, $hashed_pw);
            $oStmt->bindParam(5, $privilege);
            $oStmt->execute(); //The SQL is executed.

            echo"<script>alert(\"Successfully registered agent!\");</script>";
        }
    }

   public function checkEmail($i_email)
    {
        $sSQL = <<<SQL
        SELECT
          *
        FROM
          tbl_agent
        WHERE
           email = ?;
SQL;
        $oStmt = $this->oConn->prepare($sSQL);
        $oStmt->bindParam(1, $i_email);
        $oStmt->execute();

        if($oStmt->rowCount() > 0)
        {
            return true;
        }
   }
    //The login function. Takes whatever the user entered in the login page and is processed.
  public function login($i_email, $i_password)
  {
      //The SQL statement which checks the database if the username the user have entered in present in the database.
      $sSQL = <<<SQL
			SELECT
				*
			FROM
				tbl_agent
			WHERE
				binary email = binary ?;

SQL;
      $oStmt = $this->oConn->prepare($sSQL); //SQL is prepared to be executed.
      $oStmt->bindParam(1, $i_email); //Binds the first question mark to to the passed username.
      $oStmt->execute(); //SQL is executed
      $userRow = $oStmt->fetch(PDO::FETCH_ASSOC); //Returns a row if SQL is valid

      if($oStmt->rowCount() > 0) //If the row count of the SQL statement above is greater than zer (The username entered exists) execute block of code.
      {   //The password verify function which takes the entered password in the login page, re-hashed and compared to the already hashed password in the database.
          if(password_verify($i_password, $userRow['agent_password'])) //If the password they have entered in the login screen matches the stored hashed password in the database, the user have been successfully logged in.
          {
              echo"<script>alert(\"Successfully Logged in!\");</script>";

              $_SESSION['user_id'] = $userRow['agent_id']; //Set the session ['user_id'] to the logged in users user_id.
              $_SESSION['user_logged'] = $userRow['f_name'] . " " . $userRow['l_name']; //Set the session ['user_logged'] to the logged in users first and last name.
              $_SESSION['user_permission'] = $userRow['agent_privilege_id'];

              header('location: main.php'); //Redirect to the main page.
          }
          else
          {
              echo"<script>alert(\"Incorrect email or password.\");</script>";
          }
      }
      else
      {
          echo"<script>alert(\"Incorrect email or password.\");</script>";
      }
  }
  //The logout function which destroys the current session when called.
  public function logout()
  {
      unset($_SESSION['user_id']);
      session_destroy();
      header('location: login.php');
  }

    
}
