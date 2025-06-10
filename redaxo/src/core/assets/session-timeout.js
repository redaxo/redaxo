/*
rex.session_keep_alive // refresh rate
rex.session_duration // Maximale Sessiondauer wenn nicht aktualisiert wurde
rex.session_max_overall_duration // (maximale komplette dauer, danach wird IMMER geschlossen
rex.session_start
rex.time
rex.session_warning_time // Zeit in Sekunden vor dem Ablauf einer Session, an der eine Warnung ausgegeben wird, dass die Session bald abläuft
rex.session_logout_url
rex.session_keep_alive_url

rex.i18n.session_timeout_title
rex.i18n.session_timeout_message
rex.i18n.session_timeout_logout_label
rex.i18n.session_timeout_refresh_label
*/

if (1 === rex.session_logged_id && rex.session_keep_alive) {

    (function ($) {

            'use strict';

            $.rex_sessionTimeout = function (options) {
                rex.currentBrowserTime = new Date(); // 02:00h
                rex.serverTime = new Date(rex.time * 1000); // 05:00h
                rex.offsetTime = rex.currentBrowserTime.getTime() - rex.serverTime.getTime(); // -3h
                rex.lastSessionUpdate = new Date(rex.time * 1000);
                rex.maxKeepAlive = new Date((rex.time + rex.session_keep_alive) * 1000); // 2 Minuten
                rex.keepAliveExpandtime = 15 * 60; // 15 Minuten in Sekunden

                if (rex.session_warning_time > rex.session_duration) {
                    rex.session_warning_time = parseInt(rex.session_duration / 2); // Session duration halbiert
                }

                $.rex_sessionInterval(); // Session halten

            }

            $.rex_sessionInterval = function (options) {

                var rex_sessionCheckInterval = setInterval(function () {

                    rex.currentBrowserTime = new Date();
                    rex.currentServerTime = new Date(rex.currentBrowserTime.getTime() - rex.offsetTime);

                    // Overalltime is about to expire
                    rex.overAllEndServerTimeWarningMoment = new Date((rex.session_start + rex.session_max_overall_duration - rex.session_warning_time) * 1000);
                    if (rex.overAllEndServerTimeWarningMoment < rex.currentServerTime) {
                        clearInterval(rex_sessionCheckInterval);
                        $.rex_viewSessionOverallTimeoutDialog();
                    }

                    // Current Session Check, if it is about to expire
                    rex.nextWarningSessionDurationTime = new Date(rex.lastSessionUpdate.getTime() + (rex.session_warning_time * 1000));
                    if (rex.nextWarningSessionDurationTime < rex.currentServerTime) {
                        clearInterval(rex_sessionCheckInterval);
                        $.rex_viewSessionExpandDialog();
                    }

                    // KeepAlive, if not timed out
                    if (rex.maxKeepAlive > rex.currentServerTime) {
                        clearInterval(rex_sessionCheckInterval);
                        $.ajax(rex.session_keep_alive_url, {
                            cache: false
                        }).done(function (data) {
                            rex.lastSessionUpdate = new Date();
                            rex.lastSessionUpdate.setTime(rex.lastSessionUpdate.getTime() - rex.offsetTime); // Adjust to server time
                            $.rex_sessionInterval(); // Restart the session interval
                        }).fail(function (data) {
                            $.rex_viewSessionFailedDialog();
                        });
                    }

                }, 15 * 1000 /* check all 5 seconds */);

            }

            $.rex_viewModalBox = function (options) {

                // options.buttons auslesen und html erstellen
                var buttonHTML = '';
                if (options.buttons && options.buttons.length) {
                    options.buttons.forEach(function (button) {
                        buttonHTML += '<button id="' + button.id + '" ' + button.attr + ' type="button" >' + button.label + '</button>';
                        if (button.click) {
                            $(document).on('click', '#' + button.id, function () {
                                button.click();
                            });
                        }
                    });
                }

                $('body')
                    .find('.rex-session-timeout-dialog')
                    .remove();
                $('body')
                    .append(
                        '<div class="modal rex-session-timeout-dialog"> \
                            <div class="modal-dialog"> \
                                <div class="modal-content"> \
                                    <div class="modal-header"> \
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> \
                                        <h4 class="modal-title">' + rex.i18n.session_timeout_title + '</h4> \
                                    </div> \
                                    <div class="modal-body"> \
                                        <p>' + options.message + '</p> \
                                    </div> \
                                    <div class="modal-footer"> \
                                        ' + buttonHTML + ' \
                                    </div> \
                                </div> \
                            </div> \
                        </div>'
                    );

                $('.rex-session-timeout-dialog').on('hidden.bs.modal', function (e) {
                    $('.rex-session-timeout-dialog').remove();
                })
                $('.rex-session-timeout-dialog').modal({
                    backdrop: 'static',
                });
            }

            $.rex_viewSessionFailedDialog = function (options) {

                options = options || {};
                options.title = rex.i18n.session_timeout_title;
                options.message = rex.i18n.session_timeout_message_failed;
                options.buttons = [
                    {
                        id: 'rex-session-timeout-dialog-logout',
                        label: '<i class="rex-icon rex-icon-sign-out"></i> ' + rex.i18n.session_timeout_login_label,
                        attr: ' class="btn btn-default rex-session-timeout-dialog-logout"',
                        click: function () {
                            window.location.href = rex.session_logout_url;
                        }
                    }
                ];

                $.rex_viewModalBox(options);
            }

            $.rex_viewSessionExpandDialog = function (options) {

                options = options || {};
                var values = [parseInt(rex.session_warning_time/60), parseInt(rex.keepAliveExpandtime/60)];
                options.message = rex.i18n.session_timeout_message_expand.replace(/{(\d+)}/g, (match, index) => values[Number(index)]);;

                options.buttons = [
                    {
                        id: 'rex-session-timeout-dialog-logout',
                        label: '<i class="rex-icon rex-icon-sign-out"></i> ' + rex.i18n.session_timeout_logout_label,
                        attr: ' class="btn btn-default rex-session-timeout-dialog-logout"',
                        click: function () {
                            window.location.href = rex.session_logout_url;
                        }
                    },
                    {
                        id: 'rex-session-timeout-dialog-refresh',
                        label: '<i class="rex-icon rex-icon-refresh"></i> ' + rex.i18n.session_timeout_refresh_label,
                        attr: ' class="btn btn-primary rex-session-timeout-dialog-refresh" data-dismiss="modal"',
                        click: function () {
                            rex.maxKeepAlive = new Date(rex.maxKeepAlive.getTime() + (rex.keepAliveExpandtime * 1000)); // 15 Minuten
                            $.rex_sessionInterval(); // Restart the session interval
                            $('.rex-session-timeout-dialog').remove();
                        }
                    }
                ];

                $.rex_viewModalBox(options);

            }

            $.rex_viewSessionOverallTimeoutDialog = function (options) {

                options = options || {};
                var values = [parseInt(rex.session_warning_time/60)];
                options.message = rex.i18n.session_timeout_message_expired.replace(/{(\d+)}/g, (match, index) => values[Number(index)]);;

                options.buttons = [
                    {
                        id: 'rex-session-timeout-dialog-logout',
                        attr: ' class="btn btn-default rex-session-timeout-dialog-logout"',
                        label: '<i class="rex-icon rex-icon-sign-out"></i> ' + rex.i18n.session_timeout_logout_label,
                        click: function () {
                            window.location.href = rex.session_logout_url;
                        }
                    }
                ];
                $.rex_viewModalBox(options);

            }

        }
    )(jQuery);

    $(document).on('rex:ready', function () {
        $.rex_sessionTimeout();
    });
}

// This code is executed when the document is ready
