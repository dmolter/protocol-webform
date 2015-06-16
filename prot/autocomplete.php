<?php

/**
 * Functions for handling autocomplete.
 *
 * @author Dennis Molter
 */

require_once "../webform/error_handling.php";
require_once "ProtocolDatabase.php";


/**
 * Set the dropdown label for the given field upon triggering 
 * autocomplete. This requires adding the property "label" to 
 * the provided customer object.
 *
 * @param object $c The customer object.
 *
 * @return void
 **/
function setCustomerAutoDropdownLabel(&$c) {
    $c->label = '['. $c->id . ']' . ' ' 
        . $c->contact_form_of_address . ' ' 
        . $c->contact_title . ' ' 
        . $c->contact_last . ', ' 
        . $c->contact_first . ' - ' . $c->c_name 
                            . ' (' . $c->email . ')';
}


/**
 * Autocomplete for customer form fields.
 *
 * @param string $field Name of the form field.
 *
 * @return object[] List of corresponding fields from the same 
 * customer that contains $field.
 */
function autoCustomer($field) 
{

    $config = parse_ini_file('../config.ini');
    
    $db = new ProtocolDatabase();

    $term = $_GET['term'];
    $query = 
        "SELECT 
            id, 
            c_name,
            postcode,
            city,
            street,
            street_num,
            email,
            contact_form_of_address,
            contact_title,
            contact_first,
            contact_last 
        FROM `" . $config['dbname'] . "`.protocol_customer 
        WHERE $field LIKE '%$term%' ORDER BY id ASC LIMIT 0, 10";

    
    $customers = array();

    try {
        
        $customers = $db -> fetch($query, PDO::FETCH_OBJ);

    } catch (Exception $e) {
        
        errorHandler($e);

    }
    

    foreach ( $customers as $customer ) {
        setCustomerAutoDropdownLabel($customer);
        $customer->value = $customer->$field;
    }
 
    return $customers;
}





