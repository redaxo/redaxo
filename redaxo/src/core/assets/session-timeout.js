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

if (1 === rex.session_logged_in && rex.session_keep_alive) {

    (function () {
        'use strict';

        // Session Timeout Manager als Closure
        const createSessionTimeoutManager = function () {
            // Private Variablen
            let currentBrowserTime;
            let serverTime;
            let offsetTime;
            let lastSessionUpdate;
            let maxKeepAlive;
            let keepAliveExpandtime = 5 * 60;
            let sessionCheckInterval;

            // Private Methoden
            const init = function () {

                currentBrowserTime = new Date();
                serverTime = new Date(rex.time * 1000);
                offsetTime = currentBrowserTime.getTime() - serverTime.getTime();
                lastSessionUpdate = new Date(rex.time * 1000);
                maxKeepAlive = new Date((rex.time + rex.session_keep_alive) * 1000);

                // keepaliveExpandtime auf die Hälfte der keepalive Zeit setzen
                if (rex.session_warning_time > rex.session_keep_alive) {
                    keepAliveExpandtime = parseInt(rex.session_keep_alive / 2);
                }

                if (rex.session_warning_time > rex.session_duration) {
                    rex.session_warning_time = parseInt(rex.session_duration / 2);
                }

                startSessionInterval();
            };

            const startSessionInterval = function () {

                sessionCheckInterval = setInterval(function () {

                    const currentBrowserTimeNow = new Date();
                    const currentServerTimeNow = new Date(currentBrowserTimeNow.getTime() - offsetTime);

                    // Überprüfung der Gesamtzeit
                    const overAllEndServerTimeWarningMoment = new Date((rex.session_start + rex.session_max_overall_duration - rex.session_warning_time) * 1000);
                    if (overAllEndServerTimeWarningMoment < currentServerTimeNow) {
                        clearInterval(sessionCheckInterval);
                        viewSessionOverallTimeoutDialog();
                        return;
                    }

                    // Aktuelle Session überprüfen
                    const nextWarningSessionDurationTime = new Date(lastSessionUpdate.getTime() + (rex.session_warning_time * 1000));
                    if (nextWarningSessionDurationTime < currentServerTimeNow) {
                        clearInterval(sessionCheckInterval);
                        viewSessionExpandDialog();
                        return;
                    }

                    // KeepAlive wenn nicht abgelaufen
                    if (maxKeepAlive > currentServerTimeNow) {
                        clearInterval(sessionCheckInterval);
                        performKeepAlive();
                    }

                }, 10 * 1000);
            };

            const performKeepAlive = function () {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', rex.session_keep_alive_url + '&' + new Date().getTime(), true);

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        lastSessionUpdate = new Date();
                        lastSessionUpdate.setTime(lastSessionUpdate.getTime() - offsetTime);
                        startSessionInterval();
                    } else {
                        viewSessionFailedDialog();
                    }
                };

                xhr.onerror = function () {
                    viewSessionFailedDialog();
                };

                xhr.send();
            };

            const createModalBox = function (options) {
                // Entferne existierende Dialoge
                const existingDialog = document.querySelector('.rex-session-timeout-dialog');
                if (existingDialog) {
                    existingDialog.remove();
                }

                // Erstelle Modal HTML
                const modal = document.createElement('div');
                modal.className = 'modal rex-session-timeout-dialog';

                let buttonHTML = '';
                if (options.buttons && options.buttons.length) {
                    options.buttons.forEach(function (button) {
                        buttonHTML += '<button id="' + button.id + '" ' + button.attr + ' type="button">' + button.label + '</button>';
                    });
                }

                modal.innerHTML = `
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">${rex.i18n.session_timeout_title}</h4>
                            </div>
                            <div class="modal-body">
                                <p>${options.message}</p>
                            </div>
                            <div class="modal-footer">
                                ${buttonHTML}
                            </div>
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);

                // Event Listener für Buttons hinzufügen
                if (options.buttons && options.buttons.length) {
                    options.buttons.forEach(function (button) {
                        if (button.click) {
                            const btnElement = document.getElementById(button.id);
                            if (btnElement) {
                                btnElement.addEventListener('click', button.click);
                            }
                        }
                    });
                }

                // Close Button Funktionalität
                const closeButton = modal.querySelector('.close');
                if (closeButton) {
                    closeButton.addEventListener('click', function () {
                        modal.remove();
                    });
                }

                // Bootstrap Modal anzeigen (falls Bootstrap vorhanden)
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    $(modal).modal({
                        backdrop: 'static'
                    });
                    $(modal).on('hidden.bs.modal', function () {
                        modal.remove();
                    });
                } else {
                    // Fallback ohne Bootstrap
                    modal.style.display = 'block';
                    modal.classList.add('in');

                    // Backdrop hinzufügen
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade in';
                    document.body.appendChild(backdrop);
                }
            };

            const viewSessionFailedDialog = function () {
                const options = {
                    title: rex.i18n.session_timeout_title,
                    message: rex.i18n.session_timeout_message_failed,
                    buttons: [
                        {
                            id: 'rex-session-timeout-dialog-logout',
                            label: '<i class="rex-icon rex-icon-sign-out"></i> ' + rex.i18n.session_timeout_login_label,
                            attr: 'class="btn btn-default rex-session-timeout-dialog-logout"',
                            click: function () {
                                window.location.href = rex.session_logout_url;
                            }
                        }
                    ]
                };

                createModalBox(options);
            };

            const viewSessionExpandDialog = function () {
                const values = [parseInt(rex.session_warning_time / 60), parseInt(keepAliveExpandtime / 60)];
                const message = rex.i18n.session_timeout_message_expand.replace(/{(\d+)}/g, function (match, index) {
                    return values[Number(index)];
                });

                const options = {
                    message: message,
                    buttons: [
                        {
                            id: 'rex-session-timeout-dialog-logout',
                            label: '<i class="rex-icon rex-icon-sign-out"></i> ' + rex.i18n.session_timeout_logout_label,
                            attr: 'class="btn btn-default rex-session-timeout-dialog-logout"',
                            click: function () {
                                window.location.href = rex.session_logout_url;
                            }
                        },
                        {
                            id: 'rex-session-timeout-dialog-refresh',
                            label: '<i class="rex-icon rex-icon-refresh"></i> ' + rex.i18n.session_timeout_refresh_label,
                            attr: 'class="btn btn-primary rex-session-timeout-dialog-refresh" data-dismiss="modal"',
                            click: function () {
                                maxKeepAlive = new Date(maxKeepAlive.getTime() + (keepAliveExpandtime * 1000));
                                performKeepAlive(); // direkt keep alive aufrufen
                                startSessionInterval(); // neues Intervall für keepalive
                                const dialog = document.querySelector('.rex-session-timeout-dialog');
                                if (dialog) {
                                    dialog.remove();
                                }
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) {
                                    backdrop.remove();
                                }
                            }
                        }
                    ]
                };

                createModalBox(options);
            };

            const viewSessionOverallTimeoutDialog = function () {
                const values = [parseInt(rex.session_warning_time / 60)];
                const message = rex.i18n.session_timeout_message_expired.replace(/{(\d+)}/g, function (match, index) {
                    return values[Number(index)];
                });

                const options = {
                    message: message,
                    buttons: [
                        {
                            id: 'rex-session-timeout-dialog-logout',
                            attr: 'class="btn btn-default rex-session-timeout-dialog-logout"',
                            label: '<i class="rex-icon rex-icon-sign-out"></i> ' + rex.i18n.session_timeout_logout_label,
                            click: function () {
                                window.location.href = rex.session_logout_url;
                            }
                        }
                    ]
                };

                createModalBox(options);
            };

            // Öffentliche API
            return {
                init: init,
                stopInterval: function () {
                    if (sessionCheckInterval) {
                        clearInterval(sessionCheckInterval);
                    }
                }
            };
        };

        // Session Manager erstellen und initialisieren
        const sessionManager = createSessionTimeoutManager();

        // Warte bis DOM geladen ist
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                sessionManager.init();
            });
        } else {
            sessionManager.init();
        }

        // Optional: Manager global verfügbar machen für Debugging
        window.rexSessionManager = sessionManager;

    })();

}
