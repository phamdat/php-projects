$(function () {
    
    var opertationUrl;
    $('[need-confirm]').click(function () {
        opertationUrl = $(this).attr('href');
        $('#confirm-modal').modal('show');
        return false;
    });

    $('#yes-button').click(function () {
        if (opertationUrl != null) {
            window.location.href = opertationUrl;
            opertationUrl = null;
        }
    });
})