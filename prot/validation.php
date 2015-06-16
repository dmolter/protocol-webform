<?php

/**
 * Script for form validation.
 *
 * @author Dennis Molter
 *
 * @todo check existence of form fields in post array
 */

require_once "ProtocolDatabase.php";
require_once "db_schema.php";


$maxRecords  = ProtocolDatabase::load()['maxRecords'];


/********************************************************************

    FORM VALIDATION VARIABLES

********************************************************************/ 

$formok = true;
$errors = array();

/********************************************************************

    FORM DATA

********************************************************************/
 
$name = $_POST['c_name'];
$postcode = $_POST['postcode'];
$city = $_POST['city'];
$street = $_POST['street'];
$street_num = $_POST['street_num'];
$email = $_POST['email'];
$contact_form_of_address = $_POST['contact_form_of_address'];
$contact_title = $_POST['contact_title'];
$contact_first = $_POST['contact_first'];
$contact_last = $_POST['contact_last'];
$tech = $_POST['tech'];
$date = $_POST['date'];

$work_total = $_POST['work_total'];
$work_net = $_POST['work_net'];
$breaks = $_POST['breaks'];

$addendum_1 = $_POST['addendum_1'];
$addendum_2 = $_POST['addendum_2'];
$addendum_3 = $_POST['addendum_3'];






/********************************************************************

    VALIDATION: CUSTOMER

********************************************************************/


 
if(empty($name)) {
    $formok = false;
    $errors[] = "Der Firmenname fehlt.";
}

if(empty($postcode)) {
    $formok = false;
    $errors[] = "Die Postleitzahl fehlt.";
} else {
    if (!filter_var($postcode, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"^[0-9]{5}$^")))) {
        $formok = false;
        $errors[] = "Die Postleitzahl besteht nicht aus genau 5 Ziffern.";
    }
}
 
# TODO no digits 
if(empty($city)) {
    $formok = false;
    $errors[] = "Der Ort fehlt.";
}

# TODO no digits 
if(empty($street)) {
    $formok = false;
    $errors[] = "Die Strasse fehlt.";
}

if(empty($street_num)) {
    $formok = false;
    $errors[] = "Die Hausnummer fehlt.";
}

if(empty($email)){
    $formok = false;
    $errors[] = "Die Email-Adresse fehlt.";
} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $formok = false;
    $errors[] = "Die Email-Adresse wurde nicht erkannt.";
}
 
 # TODO no digits
if(empty($contact_form_of_address)) {
    $formok = false;
    $errors[] = "Die Anrede der Kontaktperson fehlt.";
} 
if ($contact_form_of_address !== "Herr" && $contact_form_of_address !== "Frau") {
    $formok = false;
    $errors[] = "Als Anrede stehen nur die gebotenen Alternativen zur Auswahl.";
}
 
 # TODO no digits
if(empty($contact_last)) {
    $formok = false;
    $errors[] = "Der Nachname der Kontaktperson fehlt.";
} 
 
 # TODO no digits
if(empty($tech)) {
    $formok = false;
    $errors[] = "Der Techniker fehlt.";
} 

# TODO no future date ? 
$regs;
if(empty($date)) {
    $formok = false;
    $errors[] = "Das Datum fehlt.";
} else {
    $matched = preg_match("@(\d{1,2}).(\d{1,2}).(\d{4})@", $date, $regs);
    if (!$matched) {
        $formok = false;
        $errors[] = "Das Datum wurde nicht erkannt.";
    } else {
        list($tmp, $day, $month, $year) = $regs;
        if ( !checkdate($month, $day, $year) ) {
            // echo "\nDate was invalid.";
            $formok = false;
            $errors[] = "Das Datum existiert nicht!";
        }
    }
}



/********************************************************************

    VALIDATION: ACTIONS

********************************************************************/


 
$inputActionArray = array();
         
for($i = 1; $i <= $maxRecords['action']; $i++){
    if(!isset($_POST['action_' . $i])){
		break;
	}
    
	$action = $inputActionArray[$i - 1]['action'] = $_POST['action_' . $i];
	$begin_time = $inputActionArray[$i - 1]['begin_time'] = $_POST['begin_time_' . $i];
	$end_time = $inputActionArray[$i - 1]['end_time'] = $_POST['end_time_' . $i];
	$hours = $inputActionArray[$i - 1]['hours'] = $_POST['hours_' . $i];
	
	if(empty($action)){
        $formok = false;
		if($i === 1){
			$errors[] = "Die erste Aktion ist immer erforderlich und muss benannt sein.";
		} else{
			$errors[] = "Aktion $i muss benannt werden.";
	    }
	}
	
	$timeDiffCalcPossible = true;
	if(empty($begin_time)){
		$formok = false;
		$timeDiffCalcPossible = false;
		$errors[] = "Die Startzeit der Aktion $i fehlt.";
	}
	# TODO valid time
	
	if(empty($end_time)){
		$formok = false;
		$timeDiffCalcPossible = false;
		$errors[] = "Die Endzeit der Aktion $i fehlt.";
	}
	# TODO valid time
	
	if(empty($hours)){
		$formok = false;
		$timeDiffCalcPossible = false;
		$errors[] = "Die Stundenanzahl der Aktion $i fehlt.";
	}
	elseif(!filter_var($hours, FILTER_VALIDATE_FLOAT)){
		$formok = false;
		$timeDiffCalcPossible = false;
		$errors[] = "Die Stundenanzahl der Aktion $i ist ungültig.";
	}
	
	if($timeDiffCalcPossible){
		# TODO negative values of the time diff cannot be checked !!
		# the absolute-flag of diff does not seem to work, all results are absolute
		$endDate = new DateTime(date("Y-m-d H:i:s", strtotime($begin_time)));
		$beginDate = new DateTime(date("Y-m-d H:i:s", strtotime($end_time)));
		$diff = $endDate->diff($beginDate);
		$diffInHours = $diff->h + $diff->i / 60;
		$epsilon = 0.00000000001;
		
		if(abs($diffInHours - $hours) >= $epsilon){
			$formok = false;
			$errors[] = "Die Differenz der eingegebenen Zeiten für die Aktion $i ist negativ.";
		}
		elseif($diffInHours <= 0 or $diffInHours >= 24){
			$formok = false;
			$errors[] = "Die Stundenanzahl der Aktion $i muss größer als 0 und kleiner als 24 sein.";
		}
		elseif(fmod($diffInHours, 0.25) != 0){
			$formok = false;
			$errors[] = "Die Stundenanzahl der Aktion $i muss ein Vielfaches von 0,25 sein. Bitte geben Sie Zeiten in Intervallen von 15 Minuten an.";
		}
	}
}



/********************************************************************

    VALIDATION: TOTAL, BREAKS, NET

********************************************************************/



# TODO 



/********************************************************************

    VALIDATION: PARTS

********************************************************************/



$inputPartArray = array();
for($i = 1; $i <= $maxRecords['part']; $i++){

    if(!isset($_POST['p_name_' . $i])){
		break;
	}
	
	$p_name = $inputPartArray[$i - 1]['p_name'] = $_POST['p_name_' . $i];
	$descr = $inputPartArray[$i - 1]['descr'] = $_POST['descr_' . $i];
	$serial = $inputPartArray[$i - 1]['serial'] = $_POST['serial_' . $i];
	$num = $inputPartArray[$i - 1]['num'] = $_POST['num_' . $i];
	
	# TODO number range
	if(!empty($num) and !filter_var($num, FILTER_VALIDATE_INT)){
		$formok = false;
		$errors[] = "Die Stückzahl des Materials $i ist keine Zahl.";
	}
	
	# not empty num, descr, or serial ==> not empty p_name
	if(!empty($num) or !empty($descr) or !empty($serial)){
		if(empty($p_name)){
			$formok = false;
			$errors[] = "Die Art.-Nr. des Materials $i fehlt.";
		}
	}
	# not empty p_name, descr, or serial ==> not empty num
	if(!empty($p_name) or !empty($descr) or !empty($serial)){
		if(empty($num)){
			$formok = false;
			$errors[] = "Die Stückzahl des Materials $i fehlt.";
		}
	}
}



/********************************************************************

    RESPONSE

********************************************************************/



$returndata = array(
    'form_ok' => $formok,
    'errors' => $errors
);


 
echo json_encode($returndata);


