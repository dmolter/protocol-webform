<?php

/**
* Protocol wrapper class.
* Encapsulates an object representing a row within the protocol 
* table. 
* Exposing methods to manipulate a local copy of the row's data.
*
* contract:
* Most methods need to return (multi-)arrays with numerical indices!
* With the exception of "getProtocolFields".
*  
*
* @author Dennis Molter
*/

require_once "ProtocolDatabase.php";



class Protocol
{


/********************************************************************

    MEMBER VARIABLES

********************************************************************/

    const DATE_FORMAT = "d.m.Y";

    private $prot;
    private $owner;
    private $dbFields;
    private $maxRecords;


/********************************************************************

    CONSTRUCTOR

********************************************************************/


    function __construct($protocolRow, $owner) 
    {
        $this->prot = 
            $this -> convertDate($protocolRow, self::DATE_FORMAT);
        
        $this->owner = $owner;

        $dbSchema  = ProtocolDatabase::load();
        $this->dbFields = $dbSchema['protDbFields'];
        $this->maxRecords = $dbSchema['maxRecords'];
    }


/********************************************************************

    PRIVATE

********************************************************************/


    /**
     * Converting the date to the provided format.
     *
     * @return string[]
     **/
    private function convertDate($prot, $dateFormat)
    {
        $prot->date = date($dateFormat, strtotime($prot->date));
        return $prot;
    }

    /**
     * Get row with given id from table. This method ensures the 
     * fetch mode for the select method is set to numerical per 
     * contract.
     *
     * @param string $table String representing the name of the 
     * table.
     * @param integer $id If of the row.
     *
     * @return string[]
     **/
    private function getRow($table, $id)
    {
        return $this->owner -> selectById(
            $table, 
            $id, 
            PDO::FETCH_NUM
        );
    }



    /**
     * Getting a required record. The id will always exist.
     *
     * @param integer $id Id of the record. Assumed to always
     *                    exist.
     * @param string $table Name of the table.
     *
     * @return string[]
     **/
    private function getRequired($id, $table) 
    {
        return $this -> getRow($table, $id); 
    }


    /**
     * Getting a required record. The id will always exist.
     *
     * @param integer $id Id of the record. If null, a dummy record
     * with empty fields is returned. This is necessary because the
     * column names are static and the table needs to be padded if a
     * record does not exist.
     * @param string $table Name of the table.
     *
     * @return string[]
     **/
    private function getOptional($id, $table) 
    {
        return 
            $id !== null 
                ? $this -> getRow($table, $id) 
                : $this -> dummyRecord(
                            count($this->dbFields[$table]), '');
    }


    /**
     * Returns an empty record of a given length.
     *
     * @param integer $length Length of dummy array. 
     * @param string $value String representing the empty field.
     * 
     * @return string[]
     **/
    private function dummyRecord($length, $value)
    {
        $arr = array();
        for ($i = 0; $i < $length; $i++) {
            $arr[] = $value;
        }
        return $arr;
    }


    /**
     * Get a number of records from the same table.
     * Returns null if fetching a row fails.
     *
     * @param string $tableSuffix Suffix of the table. 
     *   
     * @return string[][] | null
     **/
    private function getAllFromTable($tableSuffix) 
    {
        $record = array();
        for ($i = 1; $i <= $this->maxRecords[$tableSuffix]; $i++) {
            $row = $this -> getOptional( 
                    $this->prot->{$tableSuffix . '_id_' . $i}, #id
                    'protocol_' . $tableSuffix 
            );
            if ($row === null) return null; 

            $record[] = $row;
        }
        return $record;
    }


/********************************************************************

    PUBLIC

********************************************************************/


    /**
     * Get the values of all fields within the protocol table, 
     * not including the ids of foreign tables. The keys for these
     * fields are taken from the config.  
     *
     * @return string[]
     **/
    public function getProtocolFields() 
    {
        $prot = array();
        foreach ($this->dbFields['protocol'] as $field) {
            $prot[$field] = $this->prot->$field;
        }
        return $prot;
    }



    /**
     * Get the customer record. There must be exactly one.
     *
     * @return string[]
     **/
    public function getCustomer() 
    {
        return $this -> getRequired(
                            $this->prot->customer_id, 
                            'protocol_customer'
        );
    }


  /**
     * Get all action records. There must be at least one.
     * For every record that does not exist, a dummy will be 
     * returned.
     * The maximum number is provided as a class constant.
     *
     * @return string[][]
     **/
    public function getAllActions() 
    {
        return $this -> getAllFromTable('action');
    }


    /**
     * Get all part records. They are optional.
     * For every record that does not exist, a dummy will be 
     * returned.
     * The maximum number is provided as a class constant.
     *
     * @return string[][]
     **/
    public function getAllParts() 
    {
        return $this -> getAllFromTable('part');
    }



/*-------------------------------------------------------------------

    NOT USED BY A SCRIPT

-------------------------------------------------------------------*/


    /**
     * Get the action record specified by the id. 
     * If it does not exist, a dummy is returned.
     * 
     * @param integer $actionId
     * 
     * @return string[]
     **/
    public function getAction($actionId) 
    {
        return $this -> getOptional($actionId, 'protocol_action');
    }

 

    /**
     * Get the part record specified by the id. 
     * If it does not exist, a dummy is returned.
     *
     * @param integer $partId
     *
     * @return string[]
     **/
    public function getPart($partId) 
    {
        return $this -> getOptional($partId, 'protocol_part');
    }


/********************************************************************

    GETTER

********************************************************************/


    /**
     * Getter for the encapsulated array representing a row 
     * within the protocol table.
     *
     * @return Protocol
     **/
    public function get()
    {
        return $this->protocolRow;
    }



}
