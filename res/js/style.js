if ($(window).width() >= 980) {
    $('ul.nav li.dropdown').hover(function () {
        $(this).find('.dropdown-menu').stop(true, true).delay(50).fadeIn(50);
    }, function () {
        $(this).find('.dropdown-menu').stop(true, true).delay(50).fadeOut(50);
    });
}

var deadline = '2016-12-31';

function getTimeRemaining(endtime) {
    var t = Date.parse(endtime) - Date.parse(new Date());
    var seconds = Math.floor((t / 1000) % 60);
    var minutes = Math.floor((t / 1000 / 60) % 60);
    var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
    var days = Math.floor(t / (1000 * 60 * 60 * 24));
    return {
        'total': t,
        'days': days,
        'hours': hours,
        'minutes': minutes,
        'seconds': seconds
    };
}
var timeinterval = setInterval(function () {
    var t = getTimeRemaining(deadline);
    $('#detik').html(t.seconds + '<br><small>detik</small>');
    $('#menit').html(t.minutes + '<br><small>menit</small>');
    $('#jam').html(t.hours + '<br><small>jam</small>');
    $('#hari').html(t.days + '<br><small>Hari</small>');
    if (t.total <= 0) {
        clearInterval(timeinterval);
    }
}, 1000);

$(window).scroll(function (event) {
    var scroll = $(window).scrollTop();
    if (scroll >= 180) {
        $('.navbar').addClass("navbar-fixed-top");
        $('body').addClass("body-padding");
    } else {
        $('.navbar').removeClass("navbar-fixed-top");
        $('body').removeClass("body-padding");
    }
});
