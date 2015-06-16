<?php

/**
 * Script for outputting to PDF via FPDF.
 *
 * @author Dennis Molter
 */


require_once "../pdf/tcpdf.php";
require_once "../pdf/fpdi.php";


define('A4_LEFT', 16);
define('A4_RIGHT', 156);
define('A4_RIGHT_2', 169);
define('A4_RIGHT_3', 183);
define('A4_PART_LEFT', 24);
define('A4_DESC', 40);
define('A4_SERIAL', 105);
define('A4_WORK_TOTAL', 69);
define('A4_BREAKS', 125);
define('A4_WORK_NET', 182);




/**
 * Format number to two decimal places.
 *
 * @author Dennis Molter
 *
 * @param float $number Number to format.
 *
 * @return string Formatted number.
 */

function formatDecimal($number) {
    return number_format($number, 2, ',', '.');
}


function fillWithZeroes($n, $number) {
    $s = "%0" . $n . "s";
    return sprintf($s, $number );
}

function format2($number) {
    return fillWithZeroes('2', $number);
}

function format3($number) {
    return fillWithZeroes('3', $number);
}

function format4($number) {
    return fillWithZeroes('4', $number);
}

function format5($number) {
    return fillWithZeroes('5', $number);
}


function formatDate($dateString) {
    return date( 'H:i', strtotime($dateString) );
}



/**
 * Format output of action fields using constants for position on 
 * x-axis.
 *
 * @author Dennis Molter
 *
 * @param fpdf $pdf FPDF object.
 * @param string[] $data Form data.
 * @param integer $index Numerica suffix for field name. 
 * @param integer $y Position on y-axis.
 */

function writeActionRow($pdf, $data, $index, $y) {
    $y -= 1;
    if(!empty($data['action_' . $index])) {
        $pdf -> SetXY(A4_LEFT, $y);
        $pdf -> Write(0, $data['action_' . $index]);
    } else
        return;
    if(!empty($data['begin_time_' . $index])) {
        $pdf -> SetXY(A4_RIGHT, $y);
        $pdf -> Write(0, formatDate($data['begin_time_' . $index]));
    }
    if(!empty($data['end_time_' . $index])) {
        $pdf -> SetXY(A4_RIGHT_2, $y);
        $pdf -> Write(0, formatDate($data['end_time_' . $index]));
    }
    if(!empty($data['hours_' . $index])) {
        $pdf -> SetXY(A4_RIGHT_3, $y);
        $pdf -> Write(0, formatDecimal($data['hours_' . $index]));
    }
}


/**
 * Format output of part fields using constants for position on 
 * x-axis.
 *
 * @author Dennis Molter
 *
 * @param fpdf $pdf FPDF object.
 * @param string[] $data Form data.
 * @param integer $index Numerica suffix for field name. 
 * @param integer $y Position on y-axis.
 */

function writePartRow($pdf, $data, $index, $y) {
    $y -= 2;
    if( !empty($data['num_' . $index]) ){
        $pdf -> SetXY(A4_PART_LEFT, $y);
        $pdf -> Write(0, format5($data['num_' . $index]));
    }
    if(!empty($data['descr_' . $index])) {
        $pdf -> SetXY(A4_DESC, $y);
        $pdf -> Write(0, $data['descr_' . $index]);
    }
    if (!empty($data['serial_' . $index])) {
        $pdf -> SetXY(A4_SERIAL, $y);
        $pdf -> Write(0, $data['serial_' . $index]);
    }
    if(!empty($data['p_name_' . $index])) {
        $pdf -> SetXY(A4_RIGHT, $y);
        $pdf -> Write(0, $data['p_name_' . $index]);
    }
}





/**
 * Process output to pdf.
 *
 * @author Dennis Molter
 *
 * @todo catching errors and returning false
 *
 * @param string[] $data Form data.
 *
 * @return boolean True if successfully output to PDF. 
 */

function pdfMake($data){

    $config = parse_ini_file('../config.ini');

    $pdf = new FPDI("p", "mm", "a4");
    $pdf -> addPage();
    $pdf -> setSourceFile($config['template_path']);
    $tplIdx = $pdf -> importPage(1);
    $pdf -> useTemplate($tplIdx);

    $pdf -> AddFont('Consolas');
    $pdf -> SetFont('Consolas', '', 10);
    $pdf -> SetTextColor(0, 0, 0);

    $offset = 2;
    

    # customer and contact, box in upper left
    $y = 20 - $offset;
    if(isset($data['c_name'])){
        $pdf -> SetXY(A4_LEFT, $y);
        $pdf -> Write(0, $data['c_name']);
    }   
    $y += 5;
    if(isset($data['street']) and isset($data['street_num'])){
        $fullStreet = $data['street'] . " " . $data['street_num'];
        $pdf -> SetXY(A4_LEFT, $y);
        $pdf -> Write(0, $fullStreet);
    }
    $y += 5;
    if(isset($data['city']) and isset($data['postcode'])) {
        $fullPlace = $data['postcode'] . " " . $data['city'];
        $pdf -> SetXY(A4_LEFT, $y);
        $pdf -> Write(0, $fullPlace);
    } 
    $y += 10;
    if( isset($data['contact_form_of_address']) 
        and isset($data['contact_title']) 
        and isset($data['contact_first']) 
        and isset($data['contact_last'])) {
        
            $firstLine = $data['contact_form_of_address'];
            
            $title = $data['contact_title'];
            if ( !empty($title) ) {
                $firstLine .= ' ' . $title;
            } 
            
            $firstname = $data['contact_first'];
            $lastname = $data['contact_last'];

            $firstnameProvided = !empty($firstname);
            
            if (strlen($lastname) > 15) {
                $secondLine = $lastname;
                if ( $firstnameProvided ) {
                    // if (strlen($firstname) > 18) {
                    $secondLine = $firstname[0] . '. ' . $secondLine;
                    // } else {
                    //     $firstLine .= ' ' . $firstname;
                    // }
                }
                $pdf -> SetXY(A4_LEFT, $y);
                $pdf -> Write(0, $firstLine);
                $y += 5 - $offset;
                $pdf -> SetXY(A4_LEFT, $y);
                $pdf -> Write(0, $secondLine);
            } else {
                if ( $firstnameProvided ) {
                    $firstLine .= ' ' . $firstname[0] . '.';
                }
                $firstLine .= ' ' . $lastname;
                $pdf -> SetXY(A4_LEFT, $y);
                $pdf -> Write(0, $firstLine);
            }
        
    }
    
    
    # date
    if(isset($data['date'])){
        $pdf -> SetXY(84, 59 - $offset);
        $pdf -> Write(0, $data['date']);
    }
    # tech
    if(isset($data['tech'])){
        $pdf -> SetXY(130, 59 - $offset);
        $pdf -> Write(0, $data['tech']);
    }
    # remote maint checkboxes
    if(isset($data['remote'])){
        $pdf -> SetXY(113, 68 - $offset);
        $pdf -> Write(0, 'X');
    } else{
        $pdf -> SetXY(149, 68 - $offset);
        $pdf -> Write(0, 'X');
    }
      
    # actions
    writeActionRow($pdf, $data, 1, 84);
    writeActionRow($pdf, $data, 2, 91);
    writeActionRow($pdf, $data, 3, 98);
    writeActionRow($pdf, $data, 4, 105);
    writeActionRow($pdf, $data, 5, 111.5);
    writeActionRow($pdf, $data, 6, 118.5);
    writeActionRow($pdf, $data, 7, 125.5);
    writeActionRow($pdf, $data, 8, 132);
    writeActionRow($pdf, $data, 9, 139);
    writeActionRow($pdf, $data, 10, 146);
    writeActionRow($pdf, $data, 11, 153);
    writeActionRow($pdf, $data, 12, 160);
    writeActionRow($pdf, $data, 13, 166.5);
    writeActionRow($pdf, $data, 14, 173.5);
    
    # work hours
    $y = 181.5 - $offset;
    if(isset($data['work_total'])) {
        $pdf -> SetXY(A4_WORK_TOTAL, $y);
        $pdf -> Write(0, formatDecimal($data['work_total']));
    } 
    if(isset($data['breaks'])) {
        $pdf -> SetXY(A4_BREAKS, $y);
        $pdf -> Write(0, format3($data['breaks']));
    } 
    if(isset($data['work_net'])) {
        $pdf -> SetXY(A4_WORK_NET, $y);
        $pdf -> Write(0, formatDecimal($data['work_net']));
    } 

    # material
    writePartRow($pdf, $data, 1, 200);
    writePartRow($pdf, $data, 2, 206);
    writePartRow($pdf, $data, 3, 212);
    writePartRow($pdf, $data, 4, 218);
    writePartRow($pdf, $data, 5, 224.5);
   
    #comment
    $y = 238 - $offset;
    if(isset($data['addendum_1'])){
        $pdf -> SetXY(A4_LEFT, $y);
        $pdf -> Write(0, $data['addendum_1']);
    }
    $y += 5.5;
    if(isset($data['addendum_2'])){
        $pdf -> SetXY(A4_LEFT, $y);
        $pdf -> Write(0, $data['addendum_2']);
    }
    $y += 5.5;
    if(isset($data['addendum_3'])){
        $pdf -> SetXY(A4_LEFT, $y);
        $pdf -> Write(0, $data['addendum_3']);
    }

    // ob_end_clean;

    $pdf -> Output($data['file_path'], 'F');
    return true; 
    // $pdf -> closeParsers();
}


echo pdfMake($_POST);
