<?php

/**
 * language mappings for the protocol database table columns
 *
 * @author Dennis Molter
 */




$fieldsGerman = array(

    'protocol' => array(
        'id' => 'ID',
        'tech' => 'Techniker',
        'date' => 'Datum',
        'remote' => 'Remote',
        'work_total' => 'Brutto (in Std.)',
        'breaks' => 'Pausen (in Min.)',
        'work_net' => 'Netto (in Std.)',
        'addendum_1' => 'Addendum1',
        'addendum_2' => 'Addendum2',
        'addendum_3' => 'Addendum3'
    ),

    'protocol_customer' => array(
        'id' => 'Knd.-ID',
        'c_name' => 'Firma',
        'postcode' => 'PLZ',
        'city' => 'Stadt',
        'street' => 'Straße',
        'street_num' => 'Haus-Nr.',
        'email' => 'Email',
        'contact_form_of_address' => 'Anrede',
        'contact_title' => 'Titel',
        'contact_first' => 'Vorname',
        'contact_last' => 'Nachname'
    ),

    'protocol_action' => array(
        'id' => 'Akt.-ID',
        'action' => 'Name',
        'begin_time' => 'Zeit(Start)',
        'end_time' => 'Zeit(Ende)',
        'hours' => 'Std.'
    ),

    'protocol_part' => array(
        'id' => 'Art.-ID',
        'p_name' => 'Art.-Nr.',
        'descr' => 'Beschreibung',
        'serial' => 'Serien-Nr.',
        'num' => 'Anzahl'
    )

);   






