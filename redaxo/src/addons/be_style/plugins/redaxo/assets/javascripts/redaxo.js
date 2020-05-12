(function($){

    $(document).ready(function() {
        var $breakpoint = {};
        $breakpoint.refreshValue = function () {
            this.value = window.getComputedStyle(document.querySelector('body'), ':before').getPropertyValue('content').replace(/"/g, '');
            this.float = window.getComputedStyle(document.querySelector('body'), ':after').getPropertyValue('content').replace(/"/g, '');
        };

        $(window).resize(function () {
            $breakpoint.refreshValue();
        }).resize();


        moveMainNavigation();
        $(window).on('resize', function(){
            (!window.requestAnimationFrame) ? setTimeout(moveMainNavigation, 300) : window.requestAnimationFrame(moveMainNavigation);
        });

        function moveMainNavigation() {
            var $mainNavigation = $('#rex-js-nav-main');
            $('.rex-js-nav-main-toggle').attr('data-target', '');
            if ($breakpoint.float == 'min') {
                //desktop screen - insert inside header element
                $mainNavigation.detach();
                $mainNavigation.prependTo('#rex-js-page-container');
            } else {
                //mobile screen - insert after .main-content element
                $mainNavigation.detach();
                $mainNavigation.insertAfter('#rex-start-of-page');
            }
        }

        //mobile version - open/close navigation
        $('.rex-js-nav-main-toggle').on('click', function(event) {
            event.preventDefault();
            $('#rex-start-of-page').toggleClass('rex-nav-main-is-visible');
            $('#rex-js-nav-main').toggleClass('rex-nav-main-is-visible');
        });

    });

})(jQuery);
