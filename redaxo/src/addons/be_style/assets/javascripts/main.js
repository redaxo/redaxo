(function () {
    $(document).on('pjax:end', function(event, xhr, options) {
        options.container.find('.selectpicker').selectpicker();
    });
})();
