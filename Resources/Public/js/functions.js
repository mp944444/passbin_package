$(document).ready(function() {
    $('input#usernameinput').change(function(){
        $("#usernamereader").val($(this).val());
    });

    $('#register, #login, #createnewnote, #yournotes, #logout').appendTo('ul.nav.navbar-nav.navbar-right');

    if($('#email').val() != "") {
        $('#email').parent().parent().slideDown();
        $('#sendEmail').prop('checked', true);
    }

    $('#sendEmail').change(function() {
        if($(this).is(":checked")) {
            $('#email').parent().parent().slideDown();
        } else {
            $('#email').parent().parent().slideUp();
            $('#email').val('');
        }
    });

    $('.findErrors').find('.hasError').parent().parent().addClass('has-error');

    $(window).keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
});

var verifyCallback = function() {
    $("#loginbtn, #registerbtn, #resetpw").removeClass('hide');
};
var onloadCallback = function() {
    grecaptcha.render("captcha", {
        'sitekey':  '6Lewmv8SAAAAAK_rDgRZgZOZyaOaxHHXh0Jp3mAy',
        'callback': verifyCallback
    });
};
