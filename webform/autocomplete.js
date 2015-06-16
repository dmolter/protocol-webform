"use strict";

function fillField(ui, s) {
    $("#"+s).val(ui.item[s]);

}

function fillFields(ui, fieldStringArray) {
    $(fieldStringArray).each( function(id, fieldString) {
        fillField(ui, fieldString);
    });
}


function loadExistingFieldInputs(fieldStringArray) {
    $(fieldStringArray).each( function(id, fieldString) {
        var field = $("#"+fieldString);
        var existingValue = field.attr('data-old');
        if (existingValue !== undefined) {
            field
              .val(existingValue)
              .removeAttr('data-old');
        }
    });
}

function storeExistingFieldInputs(fieldStringArray) {
    $(fieldStringArray).each( function(id, fieldString) {
        var field = $("#"+fieldString);
        if ( field.attr('data-old') === undefined ) {
            field.attr('data-old', field.val());
        }
    });
}

function deleteExistingFieldInputs(fieldStringArray) {
    $(fieldStringArray).each( function(id, fieldString) {
        var field = $("#"+fieldString);
        if (field.attr('data-old') !== undefined) {
            field.removeAttr('data-old');
        }
    });
}



