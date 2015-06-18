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
    
    //Function to animate slider captions 
    function doAnimations( elems ) {
        //Cache the animationend event in a variable
        var animEndEv = 'webkitAnimationEnd animationend';
        
        elems.each(function () {
            var $this = $(this),
                $animationType = $this.data('animation');
            $this.addClass($animationType).one(animEndEv, function () {
                $this.removeClass($animationType);
            });
        });
    }
    
    //Variables on page load 
    var $myCarousel = $('.carousel'),
        $firstAnimatingElems = $myCarousel.find('.item:first').find("[data-animation ^= 'animated']");
        
    //Initialize carousel 
    $myCarousel.carousel();
    
    //Animate captions in first slide on page load 
    doAnimations($firstAnimatingElems);
    
    //Pause carousel  
    $myCarousel.carousel('pause');
    
    
    //Other slides to be animated on carousel slide event 
    $myCarousel.on('slide.bs.carousel', function (e) {
        var $animatingElems = $(e.relatedTarget).find("[data-animation ^= 'animated']");
        doAnimations($animatingElems);
    });
})