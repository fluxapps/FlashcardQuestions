$(document).ready(function() {
    $('select#report_lvl_1').change(function() {
        if ($(this).val() === 0 || $('select#report_lvl_2').val() !== $(this).val()) {
            return;
        }
        $('select#report_lvl_2').val(0);
    });

    $('select#report_lvl_2').change(function() {
        if ($(this).val() === 0 || $('select#report_lvl_1').val() !== $(this).val()) {
            return;
        }
        $('select#report_lvl_1').val(0);
    });
});