$(function () {
    
    var opertationUrl;
    $('[need-confirm]').click(function (e) {
        opertationUrl = $(this).attr('href');
        $('#confirm-modal').modal('show');
        e.preventDefault();
        return false;
    });

    $('#yes-button').click(function () {
        if (opertationUrl != null) {
            window.location.href = opertationUrl;
            opertationUrl = null;
        }
    });
})