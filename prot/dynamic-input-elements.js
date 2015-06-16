"use strict";

function addElement(elemString, maxElems) {
    var num        = $('.input_' + elemString).length;
    var newNum     = num + 1;
    var latestElem = $('#input_' + elemString + '_' + num);
    var newElem    = latestElem
                        .clone(true)
                        .prop('id', 'input_' + elemString + '_' 
                            + newNum);

    var inputs = $(newElem).find('input');
    $(inputs).each( function(index, input) {
        var currentInput = $(input); 
        var idSplit = currentInput.prop('id').split('_');
        var suffixIndex = idSplit.length - 1;
        idSplit[suffixIndex] = newNum;
        var s = idSplit.join('_');
        currentInput.prop('id', s).prop('name', s);
    });
    
    latestElem.after(newElem);

    $('#btn_del_' + elemString).removeAttr('disabled');

    if (newNum === maxElems)
        $('#btn_add_' + elemString).prop('disabled','disabled');

    return newElem;
}



function delElement(elemString) {
    var num = $('.input_' + elemString).length;
    $('#input_' + elemString + '_' + num).remove();
    $('#btn_add_'  + elemString).removeAttr('disabled');

    if (num-1 === 1)
        $('#btn_del_' + elemString).prop('disabled','disabled');
}


