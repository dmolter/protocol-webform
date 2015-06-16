 <?php

require_once "ProtocolDatabase.php";



function flatten($arr) {
    foreach ($arr as $key => &$value) {
        $value = $value[0];
    }
    unset($value);

    return $arr;
}

function fetchFlattened($db, $query) {
    return flatten( $db -> fetch($query, PDO::FETCH_NUM) );
}


function isNotForeignKey($compare) {
    return strpos($compare, '_id') === false;
}

function isActionId($compare) {
    return strpos($compare, 'action_id_') !== false;
}

function isPartId($compare) {
    return strpos($compare, 'part_id_') !== false;
}



$config = parse_ini_file('../config.ini');

$db = new ProtocolDatabase();

$protDbFields = array();

$dbName = $config['dbname'];

$tableQuery =
    "SELECT TABLE_NAME 
     FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = '$dbName'";

foreach ( fetchFlattened($db, $tableQuery) as $table ) {
    $columnQuery = 
        "SELECT COLUMN_NAME 
         FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE 
            TABLE_SCHEMA = '$dbName' 
            AND TABLE_NAME = '$table'";

    $protDbFields[$table] = fetchFlattened($db, $columnQuery);
}


$trueProtFields = 
    array_filter($protDbFields['protocol'],  'isNotForeignKey');

$diff = array_diff($protDbFields['protocol'], $trueProtFields);

$maxRecords['action'] = count( array_filter($diff,  'isActionId') );
$maxRecords['part'] = count( array_filter($diff,  'isPartId') );


$protDbFields['protocol'] = $trueProtFields;

ProtocolDatabase::store($protDbFields, $maxRecords);


unset($config);
unset($db);
unset($protDbFields);
unset($dbName);
unset($tableQuery);
unset($columnQuery);
unset($trueProtFields);
unset($diff);
unset($maxRecords);

