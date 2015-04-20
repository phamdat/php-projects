$(function () {
    $('header .nav.navbar-nav a, .right-sidebar a').each(function(){
        if($(this).attr('href') == window.location.pathname){
            $(this).parent().addClass('active');
            $(this).parents('li').addClass('active');
        }
    });
    
    $('.header-menu a.dropdown-toggle').click(function(){
        window.location.href = $(this).attr('href');
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('[html-include]').each(function (i, elem) {
        var el = $(elem);
        var params = {};

        $.each(this.attributes, function (i, attrib) {
            params[attrib.name] = attrib.value;
        });

        $.ajax({
            url: el.attr('html-include'),
            method: 'GET',
            data: params,
            success: function (html) {
                el.html(html);
            }
        });
    });    
    
    $('#search-button').click(function () {
        window.location.href = '/post/search/keyword/' + $('#search-text').val();
    });
    
    $('#search-text').keydown(function (e) {
        if(e.which == 13){
            $('#search-button').click();
        }
    });
})