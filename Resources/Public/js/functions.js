$(document).ready(function() {
    $('input#usernameinput').change(function(){
        $("#usernamereader").val($(this).val());
    });

    $('#register').appendTo('ul.nav.navbar-nav.navbar-right');
    $('#login').appendTo('ul.nav.navbar-nav.navbar-right');
    $('#createnewnote').appendTo('ul.nav.navbar-nav.navbar-right');
    $('#yournotes').appendTo('ul.nav.navbar-nav.navbar-right');
    $('#logout').appendTo('ul.nav.navbar-nav.navbar-right');


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
        'sitekey':  '6Le0Sf8SAAAAAMXFrjit-ATcHLtJaLA1sku-5BdG',
        'callback': verifyCallback
    });
};
