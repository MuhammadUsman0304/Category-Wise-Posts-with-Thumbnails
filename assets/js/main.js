jQuery(document).ready(function ($) {
    // alert('okabo');
    $('#myus_category').on('change', function () {
        alert('okabo');
        var category_name = $(this).find('option:selected').text();
        $('#my-widget-title').val(category_name);
    });
});