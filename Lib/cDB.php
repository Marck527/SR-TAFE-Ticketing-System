<?php

/**
 * Created by PhpStorm.
 * User: Marck
 * Date: 29/08/2016
 * Time: 5:19 PM
 */

/*
 * Tutorial by Codecourse https://www.youtube.com/watch?v=PaBWDOBFxDc&list=PLfdtiltiRHWF5Rhuk7k4UAU1_yLAZzhWc&index=8
 *
 * This is the database class which creates an instance of a database and provides functions such as fetching, inserting, updating etc.
 */
class cDB
{
    private static $_instance = null; //Defines a private static variable which keeps the intstance of teh database.
    private $_pdo,  //pdo variable which stores the PDO object connection
            $_query,    //query variable which stores the query.
            $_error = false,    //error variable, stores if there is any kind of error. Default value set to false.
            $_results,  //results variable. Stores the query result.
            $_count = 0; //this variable will stores the row count of the query

    private function __construct() //class constructor, will load on a creation of a new object of this class.
    {
        try { //try catch block, catches any errors.
            $this->_pdo = new PDO('mysql:host=127.0.0.1;dbname=TicketDB', 'root', 'Password1'); //initializes the variable __pdo to a new instance of a PDO database.
        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public static function getInstance() //the static getInstance method. this will get called to create an instance of the database and since it's static, an object of this class doesn't need to be created for this to be used.
    {
        if(!isset(self::$_instance)) { //if the var _instance hasn't been set, create a new instance. This will avoid opening multiple connection when multiple objects of this class is created.
            self::$_instance = new cDB(); //since the var _instance is static, it cannot be called using the arrow -> method and self must be used.
        }
        return self::$_instance; //otherwise if the var _instance is already set (Meaning a a connection has already been established, then return that instance.)
    }

    public function query($i_sql, $i_params = array()) { //the general purpose query method. Takes the sql and an array of parameters.
        $this->_error = false; //resets the error to false to prevent showing error if the previous query had an error.

        if($this->_query = $this->_pdo->prepare($i_sql)) { //prepares the sql($i_sql) and stores it in the variable _query
            $x = 1; //sets a counter to 1, this is also the counter for the question marks as the statements will be prepared.
            if(count($i_params)) { //if there are parameters passed
                foreach($i_params as $param) { //loop through the parameters
                    $this->_query->bindValue($x, $param); //binds the each parameter to the associated number (x)
                    $x++; //increments the counter
                }
            }

            if($this->_query->execute()) { //if the query executed,
                $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ); //fetch all on the result and return as object and store the result in the variable _result.
                $this->_count = $this->_query->rowCount(); //count the row(s) of the query result and store it in the _count var.
            } else {
                $this->_error = true; //if the query did not execute, return true to the error.
            }
        }

        return $this; //finally, return th ecurrent object.
    }

    public function action($action, $table, $where = array() ) { //the action method. Takes in the action (e.g. SELECT), the table name, and the where statement as an array.
        if(count($where == 3)) { //As the where array is expecting for 3 parameters(the field, the operator and the value) it checks if there have been 3 parameters passed.
            $operators = array('=', '>', '<', '>=', '<='); //an operators array, stores all the allowed operators.

            $field = $where[0]; //sets the var field to the first (zero) index of the array, the operator as the second and the value as the third.
            $operator = $where[1];
            $value = $where[2];

            if(in_array($operator, $operators)) { //if the operator being checked(passed) is in the allowed 'operators' array, proceed.
                $sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?"; //the SQL query. The variables gets replaces with the passed values, completing the query.

                if( !$this->query($sql, array($value))->error() ) { //queries the action and if there are no errorsm return this.
                    return $this;

                }
            }
        }
        return false; //if the where statement has not parameters passed, or is less than what it's expecting, return false.
    }

    public function get($table, $where) { //this is the get method which utalizes the action method. It acts as a shortcut as it has a predefined action already, Select.
        return $this->action('SELECT *', $table, $where); //The method takes in the table to 'get' results from and also the where statement as an array. It then passes the values into the action method.
    }

    public function delete($table, $where) { //another shortcut method. Deletes rows.
        return $this->action('DELETE', $table, $where);
    }

    public function insert($table, $fields = array()) { //the insert method. Takes the table to insert to aswell as the fields to insert to.
            $keys = array_keys($fields); //defines the array keys of the array $fields and stores it into $keys
            $values = ""; //initializes the variable $values as an empty string
            $x = 1; //initializes the counter to 1.

            foreach($fields as $field) { //for each of the fields in the array $fields,
                $values .= '?'; //ad a question mark.

                if($x < count($fields)) { //while x is less than the count of the fields array,
                    $values .= ', '; //append a apostrophe to the end of each question mark.
                }
                $x++; //incremeent untill the counter is less than the array items.
            }

            $sql = "INSERT INTO $table( " . implode(" , ", $keys). " ) VALUES ({$values});"; //the sql statement. For each of the array keys ($keys), it will get seperated by an apostrophe and the values (question marks) will get added.

            if(!$this->query($sql, $fields)->error()) { //the sql as well as the fields array will get passed into the 'query' method to get binded to the sql query.
                return true; //if it doesn't return any error, return true.
            }

        return false;
    }

    public function update($table, $id, $fields) { //the update method. Takes in the table, id and fields to update.
        $set = ''; //initializes the set statement, this will build the SET in the query.
        $x = 1; //Initializes a counter to 1

        foreach($fields as $name => $value) { //for each of the fields, name, set the value to a question mark.
            $set .= "{$name} = ?"; //append the field name plus the equals and question mark for each field passed into the array.

            if($x < count($fields)) { //while the counter is less than the items of the array, append an apostrophe.
                $set .= ', ';
            }
            $x++; //increment the counter.
        }

        $sql = "UPDATE {$table} SET {$set} WHERE agent_id = {$id};"; //the sql query. the variables will get replaced.

        if(!$this->query($sql, $fields)->error()) { //pass the table name as well as the field array into the query method to get it's real values binded to the statement.
            return true;

        }
        return false;
    }
    public function results() { //This methods return the results stored in the var _results, which is initiated in the 'query()' method after a query is ran.
        return $this->_results;
    }

    public function first() { //returns only the first result of the result() method.
        return $this->results()[0];
    }
    public function error() { //returns the the _error var. Used to check if there are or aren't any errors after a query execution.
        return $this->_error;
    }

    public function count() { //returns the _count variable. Used to see if a row returned a row count.
        return $this->_count;
    }
}