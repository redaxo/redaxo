(function () {
    'use strict';

    // scroll to current sidebar navigation item
    function scrollToCurrentNavigationItem() {
        var currentItem = document.querySelector('#rex-js-nav-main li.active');
        if (currentItem) {
            var scrollItem = document.querySelector('#rex-js-nav-main nav');
            var isInViewport = currentItem.getBoundingClientRect().bottom < window.innerHeight;
            scrollItem.scrollTop = !isInViewport ? currentItem.getBoundingClientRect().top - (window.innerHeight - currentItem.offsetHeight) / 2 : 0;
        }
    }


    // ----------------------------------------------------------------------------


    // handle scroll events
    // use ticking to debounce rAF (https://developer.mozilla.org/en-US/docs/Web/API/Document/scroll_event#Examples)
    var ticking = false;
    var handleScrollEvents = function (event) {
        if (!ticking) {
            requestAnimationFrame(function () {
                navigationBar.update(window.scrollY);
                ticking = false;
            });
            ticking = true;
        }
    };


    // handle resize events
    // use timeout to debounce (https://css-tricks.com/snippets/jquery/done-resizing-event/)
    var timeout = false;
    var handleResizeEvents = function (event) {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            navigationBar.onViewportResize();
        }, 100);
    };


    // handle click events
    var handleClickEvents = function (event) {

        // handle menu button
        if (event.target.matches('#rex-js-nav-main-toggle, #rex-js-nav-main-toggle *')) {
            event.preventDefault();
            viewport.navigationToggle();
            navigationBar.reset();
        }

        // handle backdrop
        if (event.target.matches('#rex-js-nav-main-backdrop')) {
            viewport.navigationToggle(false);
        }
    };


    // ----------------------------------------------------------------------------


    // viewport
    // knows about viewport size and toggles navigation state
    var viewport = function () {
        var mode;
        var navigationActive;
        var navigationActiveClass = 'rex-nav-main-is-visible';

        // check viewport size
        function checkViewportSize() {
            var size = window.getComputedStyle(document.querySelector('body'), ':after').getPropertyValue('content').replace(/"/g, '');
            if (size === 'min' || size === 'max') {
                mode = size;
            }
        }

        // is viewport small?
        function isSmall() {
            checkViewportSize();
            // hint: it is small when it is max
            return mode === 'max';
        }

        // check navigation status
        function checkNavigationStatus() {
            navigationActive = document.querySelector('body').classList.contains(navigationActiveClass);
            return navigationActive;
        }

        // is navigation active?
        function isNavigationActive() {
            return checkNavigationStatus();
        }

        // toggle navigation
        function navigationToggle(mode) {
            if (typeof mode === 'undefined') {
                mode = !navigationActive;
            }
            if (mode) {
                document.querySelector('body').classList.add(navigationActiveClass);
                navigationActive = true;
            }
            else {
                document.querySelector('body').classList.remove(navigationActiveClass);
                navigationActive = false;
            }
            return navigationActive;
        }

        // reveal
        return {
            isSmall: isSmall,
            isNavigationActive: isNavigationActive,
            navigationToggle: navigationToggle
        };
    }();


    // ----------------------------------------------------------------------------


    // navigationBar
    // handles navigation bar position on scroll and on viewport resize
    var navigationBar = function () {
        var currentScrollPosition = 0;
        var previousScrollPosition;
        var scrollUntilSnap = 80;
        var snapScrollPosition = 0;
        var releaseScrollUpPosition;
        var releaseScrollDownPosition;
        var isSnapped = false;
        var navigationBarElm;
        var navigationBarSelector = '#rex-js-nav-top';
        var navigationBarVisiblePosition = 0;
        var navigationBarHiddenPosition;
        var currentNavbarPosition = 0;

        // init
        function init() {
            navigationBarElm = document.querySelector(navigationBarSelector);
            navigationBarHiddenPosition = -(navigationBarElm.querySelector('.navbar').offsetHeight);
        }

        // update position and mode
        function update(scrollPosition) {
            if (typeof scrollPosition === 'undefined') {
                scrollPosition = window.scrollY;
            }

            if (!navigationBarElm) {
                return false;
            }

            // save current scroll position
            currentScrollPosition = scrollPosition;

            // scrolling down
            if (viewport.isSmall() && !viewport.isNavigationActive() && currentScrollPosition > previousScrollPosition) {
                // snap
                if (currentNavbarPosition !== navigationBarHiddenPosition && currentScrollPosition >= snapScrollPosition + scrollUntilSnap) {
                    if (!isSnapped) {
                        // save once
                        releaseScrollDownPosition = currentScrollPosition - navigationBarHiddenPosition;
                        releaseScrollUpPosition = currentScrollPosition;
                        currentNavbarPosition = currentScrollPosition;
                    }
                    isSnapped = true;
                }
                // release
                if (releaseScrollDownPosition && currentScrollPosition >= releaseScrollDownPosition) {
                    if (isSnapped) {
                        // save once
                        currentNavbarPosition = navigationBarHiddenPosition;
                    }
                    isSnapped = false;
                }
                // update snap position
                if (currentNavbarPosition === navigationBarHiddenPosition && currentScrollPosition >= snapScrollPosition) {
                    snapScrollPosition = currentScrollPosition;
                }
            }

            // scrolling up
            if (viewport.isSmall() && !viewport.isNavigationActive() && currentScrollPosition < previousScrollPosition) {
                // snap
                if (currentNavbarPosition !== navigationBarVisiblePosition && currentScrollPosition <= snapScrollPosition - scrollUntilSnap) {
                    if (!isSnapped) {
                        // save once
                        var limiter = 0;
                        if (currentScrollPosition <= scrollUntilSnap) {
                            // adjust if current scoll position is beyond limit
                            // common use case: new page is shorter than previous page
                            limiter = scrollUntilSnap - currentScrollPosition;
                        }
                        releaseScrollUpPosition = (currentScrollPosition + limiter) + navigationBarHiddenPosition;
                        releaseScrollDownPosition = (currentScrollPosition + limiter);
                        currentNavbarPosition = (currentScrollPosition + limiter) + navigationBarHiddenPosition;
                    }
                    isSnapped = true;
                }
                // release
                if (releaseScrollUpPosition && currentScrollPosition <= releaseScrollUpPosition) {
                    if (isSnapped) {
                        // save once
                        currentNavbarPosition = navigationBarVisiblePosition;
                    }
                    isSnapped = false;
                }
                // update snap position
                if (currentNavbarPosition === navigationBarVisiblePosition && currentScrollPosition <= snapScrollPosition) {
                    snapScrollPosition = currentScrollPosition;
                }
            }

            // toggle elevated style on scroll position
            if (currentScrollPosition > 10) {
                navigationBarElm.classList.add('rex-nav-top-is-elevated');
            } else {
                navigationBarElm.classList.remove('rex-nav-top-is-elevated');
            }

            // toggle elevated style when in hidden position
            if (currentNavbarPosition === navigationBarHiddenPosition) {
                navigationBarElm.classList.remove('rex-nav-top-is-elevated');
            }

            // update position
            if (isSnapped) {
                navigationBarElm.style.position = 'absolute';
                navigationBarElm.style.top = currentNavbarPosition + 'px';
            } else {
                navigationBarElm.style.position = 'fixed';
                navigationBarElm.style.top = currentNavbarPosition + 'px';
            }

            // save current scroll position
            previousScrollPosition = currentScrollPosition;
        }

        // reset
        function reset() {
            currentNavbarPosition = navigationBarVisiblePosition;
            isSnapped = false;
            navigationBarElm.style.position = 'fixed';
            navigationBarElm.style.top = currentNavbarPosition + 'px';
        }

        // on viewport resize
        function onViewportResize() {
            if (!navigationBarElm) {
                return false;
            }
            if (!viewport.isSmall()) {
                navigationBar.reset();
                navigationBar.init();
            }
        }

        // reveal
        return {
            init: init,
            update: update,
            reset: reset,
            onViewportResize: onViewportResize
        };
    }();


    // ----------------------------------------------------------------------------


    // on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        // re-init navigation bar
        navigationBar.init();
    });

    // on PJAX success
    document.addEventListener('pjax:success', function () {
        // re-init and update navigation bar
        navigationBar.init();
        navigationBar.update(window.scrollY);
        // close navigation
        viewport.navigationToggle(false);
    });

    // on load
    window.addEventListener('load', function () {
        // scroll to current navigation item
        scrollToCurrentNavigationItem();
    });

    // on scroll
    window.addEventListener('scroll', function (event) {
        handleScrollEvents(event);
    });

    // on resize
    window.addEventListener('resize', function (event) {
        handleResizeEvents(event);
    });

    // on click
    window.addEventListener('click', function (event) {
        handleClickEvents(event);
    });


    // ----------------------------------------------------------------------------


    // reveal
    return {
        viewport: viewport,
        navigationBar: navigationBar
    };

})();
