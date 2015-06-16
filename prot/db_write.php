<?php

/**
 * Script for writing into the database.
 *
 * @author Dennis Molter
 */

require_once "../webform/error_handling.php";
require_once "ProtocolDatabase.php";
require_once "db_schema.php";
require_once "mailer.php";



/**
 * Prepare form fields for database writing. 
 * Fields that do not exist or have empty values are ignored.
 * 
 * @author Dennis Molter
 *
 * @param string[] $formData Form data.
 *
 * @return string[][] Arrays with data to insert for each table. 
 */
function prepareFields($formData) 
{

    if ( empty($formData) ) return null;

    $protDbFields  = ProtocolDatabase::load()['protDbFields'];

    $protocolArray = $customerArray = $actions = $parts = array();

    foreach( array_keys($formData) as $field ){

        if( !isset($formData[$field]) ){
            continue;
        }
        $value = $formData[$field];
        if( empty($value) ) {
            continue;  
        } 



        if ( in_array($field, $protDbFields['protocol_customer']) ) {
            $customerArray[$field] = $value;
            continue;
        }



        if ( in_array($field, $protDbFields['protocol']) ) {
            if(strpos($field, 'date') !== false){
                $value = date(
                    Database::DATE_FORMAT, 
                    strtotime($value)
                );
            }
            elseif(strpos($field, 'remote') !== false){
                $value = true;
            }
            $protocolArray[$field] = $value;
            continue;
        }



        $fieldExploded = explode("_", $field);
        $fieldPrefix = $fieldExploded[0];
        $num = $fieldExploded[count($fieldExploded)-1];

            
        if($fieldPrefix === 'action'){
            $field = 'action';
        }
        elseif($fieldPrefix === 'begin'){
            $field = 'begin_time';
        }
        elseif($fieldPrefix === 'end'){
            $field = 'end_time';
        }
        elseif($fieldPrefix === 'hours'){
            $field = 'hours';
        }

        if ( in_array($field, $protDbFields['protocol_action']) ) {
            $actions[$num-1][$field] = $value;
            continue;
        } 



        if($fieldPrefix === 'p'){
            $field = 'p_name';
        }
        elseif($fieldPrefix === 'descr'){
            $field = 'descr';
        }
        elseif($fieldPrefix === 'serial'){
            $field = 'serial';
        }
        elseif($fieldPrefix === 'num'){
            $field = 'num';
        }

        if ( in_array($field, $protDbFields['protocol_part']) ) {
            $parts[$num-1][$field] = $value;
            continue;
        }
    }



    return array($customerArray, $actions, $parts, $protocolArray);
}






/********************************************************************

    EARLY EXIT FROM SCRIPT

********************************************************************/



if($_POST === null){
    exit("There is no POST to process.");
}


if ( !(isset($_POST['email'])) ) {
    exit("No recipient email address specified!");
}

if ( !(isset($_POST['file_path'])) ) {
    exit("No file path provided!");
}


$preparedFields = prepareFields($_POST);
if ( empty($preparedFields) ) {
    exit("No fields to process!");
}



/********************************************************************

    VARIABLES

********************************************************************/


$customerId = 0;

list($customer, $actions, $parts, $protocol) = $preparedFields;

$commit = false;


/********************************************************************

    SETTING UP DATABASE CONNECTION

********************************************************************/
 

$db = new ProtocolDatabase(); 

$conn = $db -> connect();

$conn -> beginTransaction();

try {


/********************************************************************

    WRITING INTO DATABASE

********************************************************************/


/*-------------------------------------------------------------------

    CUSTOMER TABLE

-------------------------------------------------------------------*/

    if(isset($_POST['cid'])){
    	$customerId = $_POST['cid'];
    }

    $protocol['customer_id'] 
    = $customerId 
        ? $customerId 
        : $db -> insert('protocol_customer', $customer);
    

/*-------------------------------------------------------------------

    ACTIONS TABLE

-------------------------------------------------------------------*/

    $actionsIndex = 1;
    foreach($actions as $action) {

        $protocol['action_id_' . $actionsIndex] 
            = $db -> insert('protocol_action', $action);
    
        $actionsIndex++;
    
    }

/*-------------------------------------------------------------------

    PARTS TABLE

-------------------------------------------------------------------*/

    $partsIndex = 1;
    foreach($parts as $part) {

        $protocol['part_id_' . $partsIndex] 
            = $db -> insert('protocol_part', $part);
    
        $partsIndex++;
    
    }

/*-------------------------------------------------------------------

    PROTOCOL TABLE

-------------------------------------------------------------------*/

    $db -> insert('protocol', $protocol);



/********************************************************************

    SENDING MAIL WITH ATTACHMENT

********************************************************************/


    $email = $_POST['email'];
    $filePath = $_POST['file_path'];

    $mail = new Mail($email, $filePath);

    if (!$mail->sendMail()) {
        throw new Exception("There was an error in sending email!");
    }





    $commit = true;

} catch(Exception $e) {

    errorHandler($e);

}




/********************************************************************

    EVALUATE TRANSACTION

********************************************************************/

if ($commit) {

    $conn -> commit();

} else {

    $conn -> rollBack();
    
}




echo $commit ? 1 : 0;



