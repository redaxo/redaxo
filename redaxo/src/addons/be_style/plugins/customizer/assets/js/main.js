$(document).on('rex:ready', function (event, container) {
    if (container.find(rex.customizer_codemirror_selectors).length > 0) {
        // Zusätzliche Themes?
        themes = '';
        container.find(rex.customizer_codemirror_selectors).each(function () {
            $.each(this.attributes, function () {
                if (this.specified) {
                    if (this.name == 'data-codemirror-theme') {
                        themes = themes + this.value + ',';
                    }
                }
            });
        });
        if (themes != '') {
            themes = themes.substring(0, themes.length - 1);
        }
        if (typeof CodeMirror !== 'function') {
            // this could better use javascript Promises, but browser support..
            var cssLoaded = false,
                scriptLoaded = false;
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = '?codemirror_output=css&buster=' + rex.customizer_codemirror_cssbuster + '&themes=' + themes;
            css.onload = function () {
                cssLoaded = true;

                if (cssLoaded && scriptLoaded) {
                    Customizer.init(container);
                }
            }
            document.head.appendChild(css);

            var script = document.createElement('script');
            script.src = '?codemirror_output=javascript&buster=' + rex.customizer_codemirror_jsbuster;
            document.head.appendChild(script);
            script.onload = function () {
                scriptLoaded = true;

                if (cssLoaded && scriptLoaded) {
                    Customizer.init(container);
                }
            };
        } else {
            Customizer.init(container);
        }
    } else {
        Customizer.init(container);
    }
});

var Customizer = function () {};

Customizer.init = function (container) {
    var cm_editor = {};
    var cm = 0;

    container.find(rex.customizer_codemirror_selectors).each(function () {
        var t = $(this);
        var id = t.attr("id");

        if (typeof id === "undefined") {
            cm++;
            id = 'codemirror-id-' + cm;
            t.attr("id", id);
        }

        var mode = "application/x-httpd-php";
        var theme = rex.customizer_codemirror_defaulttheme;

        var new_mode = t.attr("data-codemirror-mode");
        var new_theme = t.attr("data-codemirror-theme");

        if (typeof new_mode !== "undefined") {
            mode = new_mode;
        }

        if (typeof new_theme !== "undefined") {
            theme = new_theme;
        }

        if (typeof CodeMirror === "function") {
            cm_editor[cm] = CodeMirror.fromTextArea(document.getElementById(id), {
                mode: mode,
                theme: theme,
                autoRefresh: true,
                lineNumbers: true,
                lineWrapping: true,
                styleActiveLine: true,
                matchBrackets: true,
                autoCloseBrackets: true,
                matchTags: {
                    bothTags: true
                },
                tabSize: 4,
                indentUnit: 4,
                indentWithTabs: false,
                enterMode: "keep",
                tabMode: "shift",
                gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                foldGutter: true,
                extraKeys: {
                    "F11": function (cm) {
                        cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                    },
                    "Esc": function (cm) {
                        cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                    },
                    "Tab": function (cm) {
                        if (cm.doc.somethingSelected()) {
                            return CodeMirror.Pass;
                        }
                        var spacesPerTab = cm.getOption("indentUnit");
                        var spacesToInsert = spacesPerTab - (cm.doc.getCursor("start").ch % spacesPerTab);
                        var spaces = Array(spacesToInsert + 1).join(" ");
                        cm.replaceSelection(spaces, "end", "+input");
                    }
                }
            });
        }
    });

    if (typeof rex.customizer_labelcolor !== "undefined" && rex.customizer_labelcolor != '') {
        $('.rex-nav-top').css('border-bottom-color', rex.customizer_labelcolor);
    }

    if (typeof rex.customizer_showlink !== "undefined" && rex.customizer_showlink != '' && !$('.be-style-customizer-title').length) {
        $('.rex-nav-top .navbar-header').append(rex.customizer_showlink);
    }
};

