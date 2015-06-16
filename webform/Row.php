<?php

/**
* Encapsulating a row into an object. Exposing methods
* for chaining in order to improve readability.
*
* @todo expand the role of this class in a more ambitious 
* OOP design scheme...link it to the Protocol class 
* @author Dennis Molter
*/


class Row
{
    
    private $row;



    public function __construct($row = array())
    {
        $this->row = $row;
    }




    /**
     * Getter for the encapsulated array.
     *
     * @return string[]
     **/
    public function get()
    {
        return $this->row;
    }





    /**
     * Merging the encapsulated array with an array representing
     * another row. 
     *
     * @param string[] $otherRow Array representing another row.
     *
     * @return this
     **/
    public function merge($otherRow)
    {
        if ($otherRow === null) {
            throw new InvalidArgumentException(
                "Argument #1 in " . __METHOD__ . " was null!");
        }

        $this->row = array_merge($this->row, $otherRow);

        return $this;
    }

    /**
     * Merging the encapsulated array by inlining and array 
     * of arrays representing another set of rows.
     *
     * @param string[][] $rows Array of arrays representing
     * a set of rows.
     *
     * @return this
     **/
    public function inline($rows)
    {
        if ($rows === null) {
            throw new InvalidArgumentException(
                "Argument #1 in " . __METHOD__ . " was null!");
        }

        foreach ($rows as $row) {
            $this -> merge($row);
        }
        return $this;
    }




    /**
     * Merging the encapsulated array with a number of copies of 
     * an array representing another row. Optionally, the first
     * element of the row can be suffixed with a unique number by
     * setting the third (default) argument to "true".
     *
     * @param string[] $row Array representing another row.
     * @param integer $count Amount of times to copy $row.
     * @param boolean $numbered If true, the first element will
     * be suffixed with a unique number. Default is false.
     *
     * @return this
     **/
    public function mergeCopies($row, $count, $numbered=false) 
    {
        if ($row === null) {
            throw new InvalidArgumentException(
                "Argument #1 in " . __METHOD__ . " was null!");
        }
        if ($row === []) {
            return $this;
        }

        reset($row);
        $firstkey = key($row);
        $prefix = $row[$firstkey];
        for ($i = 1; $i <= $count; $i++) {
            $row[$firstkey] = $prefix . ($numbered ? '_' . $i : '');
            $this -> merge($row);
        }
        return $this;
    }



}