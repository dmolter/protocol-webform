"use strict";

function attachDatePicker(dateField, dateFormat) {
    $("#" + dateField).datepicker({
        dateFormat: dateFormat,
        yearRange: '1970:',
        showOtherMonths: true,
        selectOtherMonths: true,
        regional: "de"      
    });
}

function setDate(dateField, date) {
    $("#" + dateField).datepicker('setDate', date);
}

function setCurrentDate(dateField) {
    setDate(dateField, new Date());
}

