$("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
});

function month_replacer(month) {
    var day = ''
    switch (month) {
        case '01':
        case 1:
            day = "Januari";
            break;
        case '02':
        case 2:
            day = "Februari";
            break;
        case '03':
        case 3:
            day = "Maret";
            break;
        case '04':
        case 4:
            day = "April";
            break;
        case '05':
        case 5:
            day = "Mei";
            break;
        case '06':
        case 6:
            day = "Juni";
            break;
        case '07':
        case 7:
            day = "Juli";
            break;
        case '08':
        case 8:
            day = "Agustus";
            break;
        case '09':
        case 9:
            day = "September";
            break;
        case '10':
        case 10:
            day = "Oktober";
            break;
        case '11':
        case 11:
            day = "November";
            break;
        case '12':
        case 12:
            day = "Desember";
    }
    return day;
}
