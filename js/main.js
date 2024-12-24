$(function(){
    'use strict';
    // Confirmation message on button clicked

    $(".confirm").click(function () {
        return confirm("هل أنت متأكد؟");
    });

    // Convert password field to text field on hover

    let passField = $(".password");

    $(".show-pass").hover(function () {

        passField.attr("type", "text");
        
    }, function () {
        
        passField.attr("type", "password");

    });

    // Add asterisk on required fields

    $("input").each(function () {

        if ($(this).attr("required") === "required") {
            
            $(this).after("<span class='asterisk'>*</span>");

        };
        
    });

    // Hide placeholder on hover

    $("[placeholder]").focus(function () {
        
        $(this).attr("data-text", $(this).attr("placeholder"));

        $(this).attr("placeholder", "");

    }).blur(function () {
        
        $(this).attr("placeholder", $(this).attr("data-text"));

    });
});
