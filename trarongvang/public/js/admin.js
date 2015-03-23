$(function () {
    $(window).resize(function () {
        $('#page-wrapper').height($(window).height() - 70 + 'px');
    });
    $(window).resize();
    
    
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
    
    $('select[multiple] option').on('mousedown', function (e) {
        this.selected = !this.selected;
        e.preventDefault();
    });
})