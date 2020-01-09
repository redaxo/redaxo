$(document).on('rex:ready', function (event, container) {
    var selects = container.find('.selectpicker');
    selects.selectpicker().on('rendered.bs.select', function () {
        // remove legacy class `.bs3-has-addon` since it brings in broken styles
        $(this).parent().removeClass('bs3-has-addon');
    });
    // refresh selects to force repaints fixing rendering issues in safari
    selects.selectpicker('refresh');
});
