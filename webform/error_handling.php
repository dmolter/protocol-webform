<?php

function errorHandler($e) 
{

    error_log(
        get_class($e) 
        . ($e->getCode() ? '[code: ' . $e->getCode() . ']' : '') 
        . ': '  
        . $e->getMessage() 
        . ' (line ' . $e->getLine() . ', ' . $e->getFile() . ')' 
        . "\n===>\n" 
        . $e->getTraceAsString() 
    );

}



