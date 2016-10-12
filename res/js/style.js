    if ($(window).width() >= 1200){
        $('ul.nav li.dropdown').hover(function() {
            $(this).find('.dropdown-menu').stop(true, true).delay(50).fadeIn(50);
        }, function() {
            $(this).find('.dropdown-menu').stop(true, true).delay(50).fadeOut(50);
        });
    }
