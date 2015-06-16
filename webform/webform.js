"use strict";




function setFields(inputs, fieldValues) {
    $(inputs).each( function(index, input) {
        $(input).val(fieldValues[index]);
    });
}




function getUniqueTime() {
    var time = new Date().getTime();
    while (time === new Date().getTime());
    return new Date().getTime();
}





/**
 *
 * adding up a selection of input values
 * if there is an invalid one, the total is undefined
 *
 */
function calcTotal(selectedInputs) {
    if (selectedInputs.length === 0) return undefined;

    var total = 0;
    var calcValid = true;

    selectedInputs.each(function (index) {
        if (!calcValid) return;
        var parsedVal = parseFloat(this.value);

        if (parsedVal !== parsedVal) {
            calcValid = false;
            return;
        } else {
            total += parsedVal;
        }
    });

    return calcValid ? total : undefined;
}




/* promise for sending form data via POST */
function sendFormDataViaAjax(url, data) {
    var deferred = $.ajax({ 
        url: url,
        dataType: 'text',
        type: 'POST',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
    });
    return deferred.promise();
}




/********************************************************************

    INTERACTION LISTENERS

    placing colored borders around inputs after they have been
     interacted with
 
********************************************************************/


function addInteractionListener(input) {
    input.addEventListener(
        'blur', 
        function(event) {
            event.target.classList.add('interacted');
        }, 
        false
    ); 
}

function addInteractionListeners(inputs) {
    $(inputs).each( function(index, input) {
        addInteractionListener(input);
    });
}





/********************************************************************

    ERROR DISPLAY
 
********************************************************************/


function getErrorList() {
    return $("div.form_container").find(".error_container");
}


function displayErrorList(errors) {
    var errorList = getErrorList();
    $(errorList).addClass('visible');
    $(errors).each( function (index, value) {
            $(errorList).append( $("<li>" + value + "</li>") );
    });
}


function removeErrorList() {
    var errorList = getErrorList();
    $(errorList).removeClass('visible');
    $(errorList).children("li").remove();
}


function setErrors(errorsString, delimiter) {
    errorsString = errorsString || '';
    var errors = 
        (errorsString != '') ? errorsString.split(delimiter) : null;
    if(errors !== null) {
            displayErrorList(errors);
    } 
}




    
/********************************************************************

    TABLE MANIPULATION
 
********************************************************************/


/**
 *
 * creating a table from json data, then appending a delete button
 *  to each row 
 * TODO ajax post without a form 
 * -- DataTables plugin offers a way to add elements wired into ajax
 * -- if changed, the doc-ready submit handler is no longer
 *    necessary!
 *
 */
function createTable(tableName, jsonTable, deleteBtnText) {
    jsonTable = jsonTable || [];

    var header = jsonTable[0];
    if(!header) return;

    var table = document.createElement('table');
    var thead = document.createElement('thead');
    var tbody = document.createElement('tbody');
    var headerRow = document.createElement('tr');
    
    // HEADER
    header.forEach( function(field, index) {
        var th = document.createElement('th');
        th.innerHTML = field;
        headerRow.appendChild(th);
    });
    var emptyHeaderCell = document.createElement('th');
    emptyHeaderCell.innerHTML = "";
    headerRow.appendChild(emptyHeaderCell);

    thead.appendChild(headerRow);

   
    // BODY
    jsonTable.forEach( function(row, index) {
        var bodyRow;
        var formCell;
        var form;
        var deleteBtnCell;
        var tableNameCell;
        var idCell;
        if (index > 0) {
            bodyRow = document.createElement('tr');
            row.forEach( function(cell, index) {
                var td = document.createElement('td');
                td.innerHTML = cell;
                bodyRow.appendChild(td);
            });

            formCell = document.createElement('td');

            form = document.createElement('form');
            form.setAttribute("action","../webform/delete.php");
            form.setAttribute("method","POST");

            deleteBtnCell = document.createElement('input');
            deleteBtnCell.setAttribute("type","submit");
            deleteBtnCell.setAttribute("name","delete_action");
            deleteBtnCell.setAttribute("class","delete_row");
            deleteBtnCell.setAttribute("value", deleteBtnText);

            tableNameCell = document.createElement('input');
            tableNameCell.setAttribute("type","hidden");
            tableNameCell.setAttribute("name","table");
            tableNameCell.setAttribute("value",tableName);

            idCell = document.createElement('input');
            idCell.setAttribute("type","hidden");
            idCell.setAttribute("name","delete_row_id");
            idCell.setAttribute("value", row[0]);

            form.appendChild(deleteBtnCell);
            form.appendChild(tableNameCell);
            form.appendChild(idCell);

            formCell.innerHTML = form.outerHTML;
            bodyRow.appendChild(formCell);

            tbody.appendChild(bodyRow);
        }
    });

    // TABLE 
    table.setAttribute("id", "result_table");
    table.setAttribute("cellpadding", "10");
    table.setAttribute("border", "1");
    table.appendChild(thead);
    table.appendChild(tbody);
    return table;
}


function createGermanTable(tableName, jsonTable) {
    return createTable(tableName, jsonTable, "Löschen");
}


/*-------------------------------------------------------------------

    DATATABLES REQUIRED

    all of the following functions take a dependency on the
     datatables plugin
 
-------------------------------------------------------------------*/

/**
 *
 * PRE: the table with id "result_table" is assumed to exist
 * it is created in createTable
 *
 */
function loadDatatables(table, url, deleteButtonText) {
    $(table).dataTable( {
        "language": {
            "url": url
        },
        "destroy": true,
        "initComplete": function( settings, json ) {
            console.log("DataTable: applied to table with id #" 
                + $(table).attr('id'));

            var tbody = $("#result_table tbody");

            $(table)
              .closest("#result_table_wrapper")
              .children(":first")
              .before("<button id='delete_row_button'>" 
                + deleteButtonText + "</button>");

            $(tbody).on( 'click', 'tr', function () {
                if ( $(this).hasClass('selected') ) {
                    $(this).removeClass('selected');
                }
                else {
                    $(tbody)
                        .find('tr.selected')
                        .removeClass('selected');
                    $(this).addClass('selected');
                }
            } );

            $("#delete_row_button").on('click', function () {
                deleteRow($(tbody).find('.selected'));
            });

            console.log("DataTable: handlers attached");
        }
    } );
}

function loadGermanDatatables(table) {
    loadDatatables(
        table, 
        "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json",
        "selektierte Zeile löschen"
    );
}


/**
 * 
 * PRE: datatables plugin
 * TODO PERFORMANCE table is updated by removing it and then 
 * creating it from scratch with current db data 
 *
 */
function updateTable(tableName, tableId, containerId) {
    console.log("update: table '" + tableName + "' with id " 
        + tableId + " in container with the id " + containerId);

    $(containerId)
        .find(tableId)
        .closest("#result_table_wrapper")
        .remove();
    
    showTable(tableName, containerId);
}


/** 
 * requesting a table and displaying it
 * PRE: datatables plugin, due to display function
 *
 */
function showTable(tableName, containerId, language) {
    $.ajax({ 
        url: 'select_overview.php',
        dataType: 'json',
        type: 'GET',

        success: function(data) {
            switch (language) {
                case "German":
                    return displayGermanTable(
                        tableName, containerId, data);
                default:
                    return displayGermanTable(
                        tableName, containerId, data);
            }
        },

        error: function( jqXhr, textStatus, errorThrown ){
            console.error( errorThrown );
        }
    });
}


/** 
 * displaying a datatable
 * PRE: datatables plugin
 *
 */
function displayGermanTable(tableName, containerId, tableContent) {
    var table = createGermanTable(tableName, tableContent);
    if (table === undefined) {
        console.error("Table could not be created!");
        return;
    }
    $(table).appendTo(containerId);
    loadGermanDatatables(table);
    console.log("show: table '" + tableName     
      + "' with id #" + $(table).attr('id') 
      + " in container with the id " + containerId);
}


/**
 *
 * deleting the row from the database and the view
 * PRE: datatables plugin
 * TODO currently deleting the row from view instead of fetching db 
 * content
 * TODO ask for confirmation
 *
 */
function deleteRow(rowToDelete) {
    var formData = rowToDelete.find('form').serialize();
    sendFormDataViaAjax('../webform/delete_row.php', formData)
        .done(function(data) {
            if (data == 1) {
                rowToDelete
                    .closest('table')
                    .DataTable()
                    .row(rowToDelete)
                    .remove()
                    .draw( false );
            }
        });
}





/********************************************************************

    DOCUMENT READY FUNCTION
 
********************************************************************/

$(function () {

    /* listening for submit events on result view container */
    $("#result_view").delegate('tr', 'submit', function (event) {
        event.preventDefault();
        deleteRow($(this));
    });

});

