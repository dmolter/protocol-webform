<?php

/**
* Managing connection and exposing database operations.
*
* @author Dennis Molter
*/


class Database 
{

    static protected $connection;
    static protected $config;

    protected $dbname;

    const DATE_FORMAT = "Y-m-d";


    function __construct() 
    {
        self::$config = parse_ini_file('../config.ini');
        $this->dbname = self::$config['dbname'];
    }





    /**
     *  Connect to database via PDO.
     *
     *  @return pdo
     */
    public function connect() 
    {
        
        if (!self::$connection) {
            try {
                self::$connection = new PDO(
                    "mysql:host=" . self::$config['host'] 
                    . ";dbname=" . $this->dbname
                    . ";charset=" . self::$config['charset'], 
                    self::$config['user'],
                    self::$config['pass'],
                        array(
                            // PDO::ATTR_EMULATE_PREPARES => false,
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_PERSISTENT => false
                        )
                );
            } catch (PDOException $e) {
                $code = $e->getCode();
                die("Could not connect to database! [ERROR CODE: $code]");
            }
        } 

        return self::$connection;
    }





    /**
     * Fetch result of a select query. The result type is determined
     * by the fetch mode provided to the PDO method fetchAll.
     *
     * @param string $selectQuery SQL select query.
     * @param int(3) $fetchmode Optional fetch mode provided to
     * fetchAll. Defaults to PDO::ATTR_DEFAULT_FETCH_MODE.
     *
     * @return mixed
     **/
    public function fetch(
        $selectQuery, 
        $fetchmode=PDO::ATTR_DEFAULT_FETCH_MODE) 
    {
        $stmt = $this -> connect() -> query($selectQuery);

        if ( !$stmt ) {
            throw new Exception(
                "PDO::query in " . __METHOD__ . " failed!");
        } 

        $ret = $stmt->fetchAll($fetchmode);

        if ($ret === false) {
            throw new Exception(
                "PDOStatement::fetchAll in " . __METHOD__ . " failed!");
        }

        return $ret;
    }





    /**
     * Select a row with the given id from a table. The result is an
     * array of matches, but since the id is a primary key, it will
     * consist of only one element, which is the desired row. Returns
     * null if the result from fetch is empty.
     *
     * @param string $table String repsenting name of the table.
     * @param integer $id Id of the row.
     * @param int(3) $fetchmode Optional fetch mode provided to
     * fetchAll. Defaults to PDO::ATTR_DEFAULT_FETCH_MODE.
     *
     * @return mixed | null
     **/
    public function selectById(
        $table, 
        $id, 
        $fetchmode=PDO::ATTR_DEFAULT_FETCH_MODE) 
    {
        $result = $this -> fetch(
            "SELECT * 
             FROM `{$this->dbname}`.$table 
             WHERE id = $id",
             $fetchmode
        );

        return empty($result) ? null : $result[0];
    }





    /**
     * Inserts field into table.
     * @throws Exception
     *
     * @param string $table Database table.
     * @param string[] $dataToBeInserted Array with data to be inserted.
     *
     * @return integer Id returned from the insert operation. 
     */
    public function insert($table, $dataToBeInserted) 
    {

        $conn = $this -> connect();

        if ( empty($table) ) throw new Exception("No table name provided for insert!");
        if ( empty($dataToBeInserted) ) throw new Exception("inserting into '$table': No data to insert!");
        
        $insertedKeyArray = array_keys($dataToBeInserted);
        $insertedValueArray = array_values($dataToBeInserted);

        foreach ($insertedValueArray as &$value) {
            $value = $conn -> quote($value);
        }
        $insertedKeyString = implode(",", $insertedKeyArray);
        $insertedValueString = implode(",", $insertedValueArray);
        
        $insertQuery = "INSERT INTO $table($insertedKeyString) VALUES ($insertedValueString)";

        try {

            $stmt = $conn -> prepare($insertQuery);
            if (!$stmt) throw new Exception("inserting into '$table': failed!");
            
            $stmt->execute();
            $id = $conn -> lastInsertId();

        } catch (PDOException $e) {

            throw new Exception("Inserting into table '$table' failed with error code ". $e->getCode() . ".");

        }

        return $id;
    }





    /**
     * Delete row from table.
     *
     * @param string $table Name of the table. 
     * @param string $rowId Id of the row to be deleted.
     *
     * @return boolean True if the row has been successfully deleted.
     */
    public function delete($table, $rowId) 
    {

        if ( empty($table) or (!is_int($rowId)) ) return false;

        $deleteQuery = "DELETE FROM `{$this->dbname}`.$table WHERE id = :rowId";

        $stmt = $this -> connect() -> prepare($deleteQuery);
        if (!$stmt) return false;

        $stmt->bindValue(':rowId', $rowId, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }


}




