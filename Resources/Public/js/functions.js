$(document).ready(function() {

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