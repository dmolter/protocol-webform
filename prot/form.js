"use strict";




// TODO bad idea...namespace it

/********************************************************************

    GLOBALS
 
********************************************************************/


var currentFilePath = "";


/********************************************************************

    CONSTANTS
 
********************************************************************/


var MAX_ACTIONS = 14;
var MAX_PARTS = 5;

var DELIMITER = " # ";

var DEFAULT_ACTION = '';
var DEFAULT_BEGIN_TIME = '09:00';
var DEFAULT_END_TIME = '14:00';
var DEFAULT_HOURS = 5;
var ACTION_FIELD_INITS = [
    DEFAULT_ACTION, 
    DEFAULT_BEGIN_TIME, 
    DEFAULT_END_TIME, 
    DEFAULT_HOURS
];

var DEFAULT_WORK_TOTAL = DEFAULT_HOURS;
var DEFAULT_BREAKS = 0;
var DEFAULT_WORK_NET = DEFAULT_WORK_TOTAL;

var DEFAULT_PART_NAME = '';
var DEFAULT_PART_DESCR = '';
var DEFAULT_PART_SERIAL = '';
var DEFAULT_PART_AMOUNT = '';
var PART_FIELD_INITS = [
    DEFAULT_PART_NAME, 
    DEFAULT_PART_DESCR,
    DEFAULT_PART_SERIAL,
    DEFAULT_PART_AMOUNT
];





/********************************************************************

    INITIALIZATION

********************************************************************/


function initFormFields() {
    $("#work_total").prop('value', DEFAULT_WORK_TOTAL);
    $("#breaks").prop('value', DEFAULT_BREAKS);
    $("#work_net").prop('value', DEFAULT_WORK_NET);
    
    setCurrentDate("date");

    setFields(
        $("#input_action_1").children().find('input'),
        ACTION_FIELD_INITS
    );
}





/********************************************************************

    CONFIRM DIALOG
 
********************************************************************/


/* 
 * confirming pdf validity ==> writing form data into database 
 * TODO throw event instead of calling db write, remove parameter
 */
function callConfirmDialog(formData) {
    $( "#dialog-confirm" ).dialog({
        closeOnEscape: false,
        position: {my:"right center", at:"right center"}, 
        resizable: true,
        height: 340,
        width: 400,
        modal: true,
        buttons: {
            'OK': function() {
                $( this ).dialog( "close" );
                sendFormDataToDbWrite(formData);
            },
            'Abbrechen': function() {
                $( this ).dialog( "close" );
            }
        },
        open: function(event, ui) { 
            $(".ui-dialog-titlebar-close").hide(); 
        },
    });
}





/********************************************************************

    AJAX

    currently all of these are using the promise returned by a 
     generalized AJAX function for sending form data

    TODO utilizing error function instead of just writing to console
 
********************************************************************/


/**
 * 
 * transform json response into js object, extract formok flag
 * if form is ok => send serialized formdata to pdf maker
 * else => extract errors, send them along together with the
 *  formdata (index 0 in js object array) 
 *
 */
function sendFormDataToValidate(formData) {
    console.log("protocol: validating form on server...");

    sendFormDataViaAjax('../prot/validation.php', formData)
        .done(function( data, textStatus, jQxhr ) {
            if (data === null) {
                console.error("validation: ERROR -- response was null!");
            } else {
                var dataObject = JSON.parse(data);
                var dataArray = Object.keys(dataObject).map(
                    function(k) {
                        return dataObject[k]; 
                    });
                var formIsOk = dataArray[0];

                if (formIsOk) {
                    console.log("validation: DONE");

                    sendFormDataToPdfMaker(formData);
                } else {
                    console.log("validation: FAIL");

                    var validateErrors = dataArray[1];

                    setErrors(
                        validateErrors.join(DELIMITER), 
                        DELIMITER
                    );
                }

            }
        });
}


function sendFormDataToPdfMaker(formData) {
    console.log("protocol: generating pdf...");
    var timestamp = getUniqueTime();
    currentFilePath = '../storage/' + timestamp + '.pdf';
    formData += '&file_path=' + currentFilePath;

    sendFormDataViaAjax('../prot/pdf_make.php', formData)
        .done(function( data, textStatus, jQxhr ) {
            if (data == 1) {
                window.open(currentFilePath, '_blank');
                callConfirmDialog(formData);
                console.log("pdf generation: DONE");
            } else {
                console.error("pdf generation: FAIL");
            }
        });
}


/**
 *
 * TODO confirm dialog?
 * TODO add row via datatables instead of updating from database?
 *
 */
function sendFormDataToDbWrite(formData) {
    console.log("protocol: writing into database...");
    formData += '&file_path=' + currentFilePath;

    sendFormDataViaAjax('../prot/db_write.php', formData)
        .done(function( data, textStatus, jQxhr ) {
            if (data == 1) {
                console.log("db writing and mailing: DONE");
                updateTable(
                    'protocol', 
                    '#result_table', 
                    '#result_view'
                );
            } else {
                console.error("db writing or mailing: FAIL");
            }
        });
}





/********************************************************************

    INPUT GROUPS

    taking a dependency on dynamic-input-elements.js, a script for
     dynamically generating a group of input fields 
 
********************************************************************/


function addInputGroup(elementString, maxElements, fieldInits) {
    var group = addElement(elementString, maxElements);
    var inputs = $(group).children().find('input');
    addInteractionListeners(inputs);
    setFields(inputs, fieldInits);

    return group;
}


function addAction() {
    var action = addInputGroup(
        'action',
        MAX_ACTIONS,
        ACTION_FIELD_INITS
    );

    $(action).find('input')
        .removeClass('interacted')
        .prop('required', 'required');

    $(action).find('.hours_alert').html('');

    return action;
}


/*
 * parts are optional, but if a part should be written into the
 * database then name and amount must be set
 *
 * if a previous part is left empty, it might be considered 
 * for db writing, therefore once a new part is added the
 * name and amount of the previous part are set to 'required' 
 *
 */
function addPart() {
    var part = addInputGroup(
        'part',
        MAX_PARTS,
        PART_FIELD_INITS
    );

    var prevPart = $(part).prev();

    $(part)
        .find('input')
        .removeClass('interacted')
        .prop('required', '');

    $(prevPart)
        .find('input.p_name, input.num')
        .prop('required', 'required');

    return part;
}


function delAction() {
    delElement('action');
}

function delPart() {
    delElement('part');
}





/********************************************************************

    AUTO-FILLING

    calculating values to automatically fill form fields with
 
********************************************************************/

/**
 *
 * work net value is equal to work total minus breaks
 * breaks are specified in minutes (15-minute interval, currently)
 *
 */
function calcWorkNet(total, workBreaks) {
    var breaks = $(workBreaks).val() / 60;
    var net = total - breaks;
    if (net < 0) {
        console.log("Es wurde mehr Pause als Arbeitszeit eingegeben!");
        $(workBreaks).val(0);
        return total;
    } else {
        return net;
    }
}



/*-------------------------------------------------------------------

    THESE ARE EVENT HANDLERS
 
-------------------------------------------------------------------*/

/**
 *
 * an hours field is empty 
 *  ==> total is undefined 
 *  ==> total field and net field are both empty
 *
 */
function updateTotalAndNet() {
    var inputs = $("input[class=hours]");
    var total = calcTotal(inputs);
    var workTotal = $("#work_total");
    var workNet = $("#work_net");
    var workBreaks = $("#breaks");

    if (total === undefined) {
        $(workTotal).val('');
        $(workNet).val('');
    } else {
        $(workTotal).val(total);
        $(workNet).val(calcWorkNet(total, workBreaks));
    }
}



function updateNet() {
    var workNet = $("#work_net");
    var workBreaks = $("#breaks");
    var workTotal = $("#work_total");
    var total = $(workTotal).val();

    $(workNet).val(calcWorkNet(total, workBreaks));
}



function updateHours() {

    var $inputAction = $(this).closest(".input_action");
    var $beginTimeField = $inputAction.find(".begin_time");
    var $endTimeField = $inputAction.find(".end_time");
    var $hoursField = $inputAction.find(".hours");
    var $hoursAlert = $inputAction.find(".hours_alert");

    var beginTime = 
        Date.parse('1970-01-01T' + $beginTimeField.val() + ':00');
    
    var endTime = 
        Date.parse('1970-01-01T' + $endTimeField.val() + ':00');

    var message = "";
    var hours = '';
    var tempHours;

    if (beginTime !== beginTime) {
        message ="Startzeit nicht erkannt!";
    } else if (endTime !== endTime) {
        message ="Endzeit nicht erkannt!";
    } else {
        tempHours = (endTime - beginTime) / (1000 * 60 * 60);
        if (tempHours <= 0) {
            message ="Startzeit muss vor Endzeit liegen!";
        } else if (tempHours % 0.25 !== 0) {
            message ="Zeiten bitte in 15-Minuten-Intervallen!";
        } else {
            hours = tempHours;
        }
    }

    $hoursAlert.html(message);

    $hoursField.val(hours).trigger('change');

}














/********************************************************************

    HANDLER
 
********************************************************************/


/* 
 * reset customer id to 0 (new customer) if autocompleted field
 *  is edited 
 */
function customerEditHandler(e) {
    $('#cid').val(0);
}



function submitHandler(e) {
    e.preventDefault();
    var form = e.target;
    var formData = $(form).serialize();
    removeErrorList();
    sendFormDataToValidate(formData);
}





/********************************************************************

    DOCUMENT READY FUNCTION

********************************************************************/

$(function() {

    var $protForm = $("#protForm");


    $protForm
        .find(".max_part_count")
        .html(" (max. " + MAX_PARTS + ")");
    $protForm
        .find(".max_action_count")
        .html(" (max. " + MAX_ACTIONS + ")");


    /*
     *  mark all inputs after interaction in order to display a
     *   colored border indicating validity 
     */
    addInteractionListeners($protForm.find(":input"));



    /* submit events */    
    
    $protForm.on('submit', submitHandler);
    

    /* change events */

    $protForm
        .find(".begin_time, .end_time").on('change', updateHours);

    $protForm.find(".hours").on('change', updateTotalAndNet);

    $protForm.find("#breaks").on('change', updateNet);


    /* input events */
    
    $('#customer, #contact').on('input', customerEditHandler);



    /* adding and deleting dynamic elements */

    $('#btn_add_action').on('click', addAction);
    $('#btn_del_action').on('click', delAction);
    $('#btn_add_action, #btn_del_action')
        .on('click', updateTotalAndNet);
    
    $('#btn_add_part').on('click', addPart);
    $('#btn_del_part').on('click', delPart);
    
    if ( $('.input_action').length === 1 ) {
        $('#btn_del_action').prop('disabled','disabled');
    }
    if ( $('.input_part').length === 1 ) {
        $('#btn_del_part').prop('disabled','disabled');
    }



    /*** including datepicker ***/
    attachDatePicker("date", "dd.mm.yy");

    
    initFormFields();

});