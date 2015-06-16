"use strict";

// PRE: ../webform/autocomplete.js
function autoCustomer(field) {
    var customerFields = [
        'c_name',
        'postcode',
        'city',
        'street',
        'street_num',
        'email',
        'contact_last',
        'contact_first',
        'contact_form_of_address',
        'contact_title'
    ]; 

    $("#" + field).autocomplete({
        source: "auto_" + field + ".php",
        focus: function(event, ui) {
            storeExistingFieldInputs(customerFields);
            fillFields(ui, customerFields);
            return false;
        },
        select: function(event, ui) {
            deleteExistingFieldInputs(customerFields);
            $("#cid").val(ui.item.id);
            return false;
        },
        change: function(event, ui) {
            loadExistingFieldInputs(customerFields);
            return false;
        }
    });
}



$(function() {
    
    autoCustomer("contact_last");
    autoCustomer("c_name");
    autoCustomer("email");

});


