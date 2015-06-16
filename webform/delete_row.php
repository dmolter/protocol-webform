<?php

/**
 * Script which returns the result of a delete query.
 *
 * @author Dennis Molter
 *
 */

require_once "../webform/error_handling.php";
require_once "../webform/Database.php";

if($_POST === null){
    exit("There is no POST.");
}





$db = new Database();

$deleted = false;

if(isset($_POST['table']) and isset($_POST['delete_row_id'])){
    
    $table = $_POST['table'];
    $deleteRowId = $_POST['delete_row_id'];

    #TODO empty check for above variables

    try {

        $deleted = $db -> delete($table, $deleteRowId + 0);
    
    } catch (Exception $e) {
        
        errorHandler($e);    
    }
}


echo $deleted ? 1 : 0; 


