$(document).ready(function() {
    $('input#usernameinput').change(function(){
        $("#usernamereader").val($(this).val());
    });

    $('#login').appendTo('ul.nav.navbar-nav.navbar-right');
    $('#createnewnote').appendTo('ul.nav.navbar-nav.navbar-right');
    $('#yournotes').appendTo('ul.nav.navbar-nav.navbar-right');
    $('#logout').appendTo('ul.nav.navbar-nav.navbar-right');

    $('#sendEmail').change(function() {
        if($(this).is(":checked")) {
            $('#email').parent().parent().slideDown();
        } else {
            $('#email').parent().parent().slideUp();
            $('#email').val('');
        }
    });

    $('.findErrors').find('.hasError').parent().parent().addClass('has-error');
});