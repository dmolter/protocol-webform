<?php

/**
 * Script calling the autocomplete function for the customer input fields.
 *
 * @author Dennis Molter
 */
 
require_once "autocomplete.php";

echo json_encode(autoCustomer('contact_last'));

