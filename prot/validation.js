"use strict";

$( function() {

    /* setting fields as "required" */
    
    $("#c_name").attr('required', true);
    $("#postcode").attr('required', true);
    $("#city").attr('required', true);
    $("#street").attr('required', true);
    $("#street_num").attr('required', true);
    $("#email").attr('required', true);
    $("#contact_form_of_address").attr('required', true);
    $("#contact_last").attr('required', true);
    
    $("#tech").attr('required', true);
    
    $("#date").attr('required', true);

    
    /** 
     *
     * PRE: dynamic input element script
     * this block is cloned for following actions via the script
     *
     */
    $("#action_1").attr('required', true);
    $("#begin_time_1").attr('required', true);
    $("#end_time_1").attr('required', true);
    $("#hours_1").attr('required', true);
   
    $("#work_total").attr('required', true);
    $("#breaks").attr('required', true);
    $("#work_net").attr('required', true);

    
    /* setting pattern attribute */
    
    $("#postcode").attr("pattern", "^[0-9]{5}$");
    $("#city").attr("pattern", "\\D*");
    $("#street").attr("pattern", "\\D*");
    $("#contact_form_of_address").attr('pattern', "Herr|Frau");
    $("#contact_first").attr('pattern', "\\D*");
    $("#contact_last").attr('pattern', "\\D*");
    

   /* PRE: dynamic input element script - handler is copied */
   $("#protForm .input_part")
    .on('change', ".p_name, .descr, .serial, .num", function(){
        var inputPart = $(this).closest(".input_part");
        var partName = inputPart.find(".p_name");
        var partDescr = inputPart.find(".descr");
        var partSerial = inputPart.find(".serial");
        var partAmount = inputPart.find(".num");

        if (partDescr.val() === '' 
            && partSerial.val() === '' 
            && partName.val() === '' 
            && partAmount.val() === '') {
                partName.removeAttr('required');
                partAmount.removeAttr('required');
        } 
        if ($(this).val() !== '') {
                partName.prop('required', true);
                partAmount.prop('required', true);
        } 
   });
   

});
