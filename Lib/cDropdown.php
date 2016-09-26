<?php

/**
 * Created by PhpStorm.
 * User: Student
 * Date: 18/05/2016
 * Time: 2:00 PM
 */
class cDropdown
    /* Takes in SQL statement and other settings and prepares HTML5 output to display a
    dropdown or list element
    This class was developed in-class (GSIT-Albany Cert IV I.T.) and may be freely used and adapted with
    appropriate acknowledgment of source.
    */
{
    private $oConn;
    private $sHTML_ID           = "";
    private $sSQL               = "";
    private $sName              = "";
    private $iDefaultID         = 0;
    private $iSize              = 1;
    private $bDisplayAll        = false;
    private $sDisplayAllText    = "[Select All]";
    private $sIDField           = "agent_privilege_id";
    private $sHTMLClass         = "form-control";
    private $sDescriptionField  = "privilege_title";
    private $bMultiple          = false;
    private $bRequired          = false;

    public function __construct($i_db, $i_sSQL)
    {
        $this->oConn = $i_db;
        $this->sSQL = $i_sSQL;
    }

    public function setBMultiple($bMultiple)
    {
        $this->bMultiple = $bMultiple;
    }

    public function setBRequired($bRequired)
    {
        $this->bRequired = $bRequired;
    }
    public function setIDField( $sIDField)
    {
        $this->sIDField = $sIDField;
    }

    public function setDescriptionField( $sDescriptionField)
    {
        $this->sDescriptionField = $sDescriptionField;
    }

    public function setDisplayAllText(string $sDisplayAllText)
    {
        $this->sDisplayAllText = $sDisplayAllText;
    }

    public function setDisplayAll(bool $bDisplayAll)
    {
        $this->bDisplayAll = $bDisplayAll;
    }

    public function setDefaultID($iDefaultID)
    {
        $this->iDefaultID = $iDefaultID;
    }

    public function setSize(int $iSize)
    {
        $this->iSize = $iSize;
    }

    public function setHTMLID(string $sHTML_ID)
    {
        $this->sHTML_ID = $sHTML_ID;
    }

    public function setSHTMLClass($sHTMLClass)
    {
        $this->sHTMLClass = $sHTMLClass;
    }
    public function setName(string $sName)
    {
        $this->sName = $sName;
    }

    public function setSQL(string $sSQL)
    {
        $this->sSQL = $sSQL;
    }

    public function HTML() : string
    {
        if($this->bMultiple) {
            $multiple = 'multiple';
        } else {
            $multiple = null;
        }
        if($this->bRequired) {
            $required = 'required';
        } else {
            $required = null;
        }

        if ($this->sHTML_ID)    // If the HTML id has been set, add it as an attribute to the <select> tag...
            $sHTML = "<select class='$this->sHTMLClass' id='$this->sHTML_ID' name='$this->sName' size='$this->iSize' $multiple $required>" . PHP_EOL;
        else
            $sHTML = "<select name='$this->sName' size='$this->iSize'>" . PHP_EOL;

        if ($this->bDisplayAll)
        {
            $sHTML .= "<option value='0'>$this->sDisplayAllText</option>";
        }

        foreach($this->oConn->query($this->sSQL) as $oRow)
        {
            $iID = $oRow[$this->sIDField];
            $sDescription = $oRow[$this->sDescriptionField];

            if ($iID == $this->iDefaultID)
                $sHTML .= "<option value='$iID' selected='selected'>$sDescription</option>" . PHP_EOL;
            else
                $sHTML .= "<option value='$iID'>$sDescription</option>" . PHP_EOL;
        }
        

        $sHTML .= "</select>" . PHP_EOL;

        return $sHTML;

    }
}