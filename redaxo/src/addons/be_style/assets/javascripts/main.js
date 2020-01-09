$(document).on('rex:ready', function (event, container) {
    container.find('.selectpicker').selectpicker().on('loaded.bs.select', function () {
        // remove legacy class `.bs3-has-addon` since it brings in broken styles
        $(this).parent().removeClass('bs3-has-addon');
    });
});
