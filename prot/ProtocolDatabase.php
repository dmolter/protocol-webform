<?php

/**
* Database subclass containing specifics on the protocol database.
* It encapsulates an array of Protocol objects representing the 
* current state of the protocol table.
*
* 
*
* @author Dennis Molter
*/

require_once "../webform/Database.php";
require_once "Protocol.php";




class ProtocolDatabase extends Database 
{

    
/********************************************************************

    STATIC

********************************************************************/


    static private $protDbFields = array(
        'protocol' => array(
        ),
        'protocol_customer' => array(
        ),
        'protocol_action' => array(
        ),
        'protocol_part' => array(
        )
    );

    static private $maxRecords = array(
        'action' => '',
        'part' => ''
    ); 


    /**
     * Loading configuration
     *
     * @return string[][]
     **/
    static public function store($protDbFields, $maxRecords)
    {
        self::$protDbFields = $protDbFields;
        self::$maxRecords = $maxRecords;
    }

    /**
     * Loading configuration
     *
     * @return string[][]
     **/
    static public function load()
    {
        return array(
            'protDbFields' => self::$protDbFields, 
            'maxRecords' => self::$maxRecords
        );
    }


   


/********************************************************************

    MEMBER VARIABLES

********************************************************************/


    private $protocols;


/********************************************************************

    CONSTRUCTOR

********************************************************************/


    function __construct() 
    {
        parent::__construct();

        $this->protocols = array();
    }


/********************************************************************

    PRIVATE

********************************************************************/


    /**
     * Wraps the provided array of arrays as Protocol object.
     *
     * @param string[][] $arrayOfRows An array of arrays 
     * representing rows of the protocol table.
     *
     * @return Protocol[]
     **/
    private function wrap($arrayOfRows)
    {
        foreach ( $arrayOfRows as $row ) {
            $this->protocols[] = new Protocol($row, $this);
        }
        return $this->protocols;
    }


/********************************************************************

    PUBLIC

********************************************************************/


    /**
     * Fetch result of a select query as an array of objects.
     *
     * @param string $selectQuery SQL select query.
     *
     * @return object[][]
     **/
    public function fetchObj($selectQuery) 
    {
        return $this -> fetch($selectQuery, PDO::FETCH_OBJ);
    }



    /**
     * Retrieves the current state of the protocol table from 
     * the database and calls the wrapper to encapsulate it.
     *
     * @return this
     **/
    public function fetchProtocols()
    {
        $this->protocols = $this -> wrap($this -> fetchObj(
            "SELECT * 
             FROM `{$this->dbname}`.protocol"
        )); 

        return $this;
    }



    /**
     * Returns the encapsulation of the protocol table.
     * Fetches it only if necessary.
     *
     * @return Protocol[]
     **/
    public function getProtocols() 
    {
        if ( empty($this->protocols) ) {
            $this -> fetchProtocols();
        } 
        return $this->protocols;
    }



}




