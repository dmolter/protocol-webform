<?php

/**
 * Script for selecting a table in which every row represents a 
 * protocol, including all the data gathered from foreign tables.
 * This provides a comprehensive overview.
 *
 * @todo move functions into other files
 * @return A table in JSON. 
 * @author Dennis Molter
 **/


require_once "../webform/error_handling.php";
require_once "../webform/Row.php";
require_once "ProtocolDatabase.php";
require_once "Protocol.php";
require_once "db_schema.php";
require_once "i18n.php";

define('PROT_AUTHOR_INDEX', 0);
define('PROT_HOURS_INDEX', 4);
define('PROT_ADDENDUM_INDEX', 7);


/********************************************************************

    HELPER FUNCTIONS

********************************************************************/


/**
 * Splits an array into disjoint parts the starting point of
 * which is determined by a list of indices. There must be at
 * least two elements inside the array. Otherwise the unmodified
 * array is returned.
 *
 * @param array $arr Array.
 * @param integer[] $indices List of integers representing the 
 * index of an element at which to slice the array. 
 *
 * @return array of arrays
 * @author Dennis Molter
 **/
function chopUp($arr, $indices) 
{
    if ($arr===null) return null;

    $iLength = count($indices);
    if ($iLength <= 1) {
        return [$arr];
    }
    
    $choppedUpArr = array();
    $nextIndices = $indices;
    $nextIndices[] = count($arr);
    
    for ($i=1; $i<=$iLength; $i++) {
        $index = $indices[$i-1];

        $step = $nextIndices[$i]-$index;
        if ($step < 1) return $arr;
        
        $choppedUpArr[$i-1] = array_slice(
            $arr,
            $index,
            $step
        );
    } 

    return $choppedUpArr;
}



/*-------------------------------------------------------------------

    MAP FUNCTIONS

-------------------------------------------------------------------*/


/**
 * Maps database identifiers of fields to labels of a given
 * language.  
 * NOTE: The arrays are assumed to have matching formats.
 *
 * @param array $dbFields Array containing field identifiers.
 * @param array $i18nOfFields Array with the mapping. 
 *
 * @return array
 * @author Dennis Molter 
 **/
function mapLabels($dbFields, $i18nOfFields) 
{
    return mapNestedValues($dbFields, $i18nOfFields);
}



/**
 * Performs "mapValues" on the next-inner level of nesting in
 * respect to the input arrays.
 *
 * @param array $original Array with one level of nesting.
 * @param array $mapped Array with one level of nesting. 
 *
 * @return array
 * @author Dennis Molter 
 **/
function mapNestedValues($original, $mapped) 
{
    foreach (array_keys($original) as $sectionKey) {
        
        $original[$sectionKey] = mapValues(
                $original[$sectionKey], 
                $mapped[$sectionKey]
        );
    }
    return $original;
}

/**
 * Each value in array A serves as a key in array B and the 
 * result is returned as a value to A. 
 *
 * @param array $original Array.
 * @param array $mapped Array. 
 *
 * @return array 
 * @author Dennis Molter 
 **/
function mapValues($original, $mapped) 
{
    foreach ($original as $key => $value) {
        $original[$key] = $mapped[$value];
    }
    return $original;
}








/********************************************************************

    BUILDING THE TABLE

********************************************************************/



/**
 * Builds the header for the result table.
 *
 * @param integer[] $protSectionIndices A list of integers
 * representing the indices used to divide the fields of the
 * protocol table into sections. 
 * @param string[][] Array containing i18n mapping.
 *
 * @return Row
 * @author Dennis Molter 
 **/
function buildHeader($protSectionIndices, $i18n) 
{

    $dbSchema  = ProtocolDatabase::load();
    
    $labels = mapLabels($dbSchema['protDbFields'], $i18n);
    
    $protSections = chopUp(
        $labels['protocol'], 
        $protSectionIndices
    );

    $maxRecords = $dbSchema['maxRecords'];
    return ( new Row() )
        -> merge( $protSections[0] )
        -> merge( $labels['protocol_customer'] )
        -> mergeCopies(
            $labels['protocol_action'], 
            $maxRecords['action'], 
            true )
        -> merge( $protSections[1] )
        -> mergeCopies( 
            $labels['protocol_part'], 
            $maxRecords['part'], 
            true )
        -> merge( $protSections[2] );
}


/**
 * Builds the body for the result table.
 *
 * NOTE: The array_map of array_values is a bit of a hack, 
 * turning the associative array into one with numerical
 * indices, as required elsewhere. A cleaner solution should 
 * be found.
 *
 * @param integer[] $protSectionIndices A list of integers
 * representing the indices used to divide the fields of the
 * protocol table into sections. 
 *
 * @return string[][]
 * @author Dennis Molter
 **/
function buildBody($protSectionIndices) 
{

    $body = array();

    foreach ( (new ProtocolDatabase()) -> getProtocols() as $prot) {

        $protSections = array_map('array_values', chopUp(
                    $prot -> getProtocolFields(), 
                    $protSectionIndices
        ));

        $body[] = buildRow($prot, $protSections)->get();

    }

    return $body;
}


/**
 * Builds a row for the body.
 *
 * @param Protocol $protocol Protocol object.
 * @param string[][] $protSections Array containing
 * thematic sections of the protocol field array. 
 *
 * @return Row
 * @author Dennis Molter 
 **/
function buildRow($protocol, $protSections) 
{
    
    return ( new Row() )
        -> merge  ( $protSections[0]             )
        -> merge  ( $protocol -> getCustomer()   )
        -> inline ( $protocol -> getAllActions() )
        -> merge  ( $protSections[1]             )
        -> inline ( $protocol -> getAllParts()   )
        -> merge  ( $protSections[2]             );

}



/********************************************************************

    SCRIPT

********************************************************************/



$PROT_SECTION_INDICES = array(
    PROT_AUTHOR_INDEX,
    PROT_HOURS_INDEX,
    PROT_ADDENDUM_INDEX
);


$table = array();

try {

    $table = array_merge(
        [buildHeader($PROT_SECTION_INDICES, $fieldsGerman)->get()], 
          buildBody($PROT_SECTION_INDICES)
    );
    
} catch (Exception $e) {

    errorHandler($e);
    
}


echo json_encode( $table );




