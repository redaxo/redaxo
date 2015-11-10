(function($){

    $(function(){
        $('.rex-nav-main').perfectScrollbar();
    });

    $(function(){
        $('.rex-nav-main').perfectScrollbar();
    });


    var $structureContentNav = null,
        $structureContentNavTopPosition = null,
        $structureContentNavTopPositionSubtract = 70 + 20 + 78,
        $structureBreadcrumb = null,
        $structureBreadcrumbTopPosition = null,
        $structureBreadcrumbTopPositionSubtract = 70 + 20,
        $structureLanguage = null,
        $structureLanguageTopPosition = null,
        $structureLanguageTopPositionSubtract = 70 + 20;

    function structureContentScroll() {
        if($structureContentNavTopPosition !== null)
            if ($(this).scrollTop() >= $structureContentNavTopPosition) {
                $structureContentNav.addClass('rex-is-fixed');
            } else {
                $structureContentNav.removeClass('rex-is-fixed');
            }
    }
    function structureContentResize() {
        $structureContentNav.width($structureContentNav.parent().width());
        if ($structureContentNav.length > 0 && $structureContentNav.hasClass('rex-is-fixed')) {
            $structureContentNav.css('width', $structureContentNav.parent().width());
        } else {
            $structureContentNav.css('width', '');
        }
    }

    function structureBreadcrumbScroll() {
        if($structureBreadcrumbTopPosition !== null)
            if ($(this).scrollTop() >= $structureBreadcrumbTopPosition) {
                $structureBreadcrumb.addClass('rex-is-fixed');
            } else {
                $structureBreadcrumb.removeClass('rex-is-fixed');
            }
            structureBreadcrumbResize();
    }
    function structureBreadcrumbResize() {
        if ($structureBreadcrumb.length > 0 && $structureBreadcrumb.hasClass('rex-is-fixed') && $structureLanguage.length > 0) {
            $structureBreadcrumb.css('width', $structureBreadcrumb.parent().width() - $structureLanguage.outerWidth(true));
        } else {
            $structureBreadcrumb.css('width', '');
        }
    }

    function structureLanguageScroll() {
        if($structureLanguageTopPosition !== null)
            if ($(this).scrollTop() >= $structureLanguageTopPosition) {
                $structureLanguage.addClass('rex-is-fixed');
            } else {
                $structureLanguage.removeClass('rex-is-fixed');
            }
    }

    $(function() {
        $structureContentNav = $('#rex-js-structure-content-nav');
        $structureBreadcrumb = $('#rex-js-structure-breadcrumb');
        $structureLanguage = $('#rex-js-page-main > .rex-nav-language').first();

        $(window).on({
            scroll: function() {
                structureContentScroll();
                structureBreadcrumbScroll();
                structureLanguageScroll();
            },
            resize: function () {
                structureContentResize();
                structureBreadcrumbResize();
            }
        });

        if($structureContentNav.length > 0) {
            $structureContentNavTopPosition = $structureContentNav.offset().top - $structureContentNavTopPositionSubtract;
            structureContentResize();
        }
        if($structureBreadcrumb.length > 0) {
            $structureBreadcrumbTopPosition = $structureBreadcrumb.offset().top - $structureBreadcrumbTopPositionSubtract;
            structureBreadcrumbResize();
        }
        if($structureLanguage.length > 0) {
            $structureLanguageTopPosition = $structureLanguage.offset().top - $structureLanguageTopPositionSubtract;
        }
    });

})(jQuery);
