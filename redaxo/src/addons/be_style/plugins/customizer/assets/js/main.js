$(document).on('rex:ready', function (event, container) {
    if (container.find(rex.customizer_codemirror_selectors).size() > 0) {
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
                        if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
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
        $('.rex-nav-top').css('border-bottom', '7px solid ' + rex.customizer_labelcolor)
    }

    // inspired by https://blog.roomanna.com/09-24-2011/dynamically-coloring-a-favicon
    if (typeof rex.customizer_inkfavicon !== "undefined" && rex.customizer_inkfavicon != '') {
        var link = document.querySelector("link[rel~='icon']");
        if (!link) {
            link = document.createElement("link");
            link.setAttribute("rel", "shortcut icon");
            document.head.appendChild(link);
        }
        var faviconUrl = link.href || window.location.origin + "/favicon.ico";

        var img = document.createElement("img");
        img.addEventListener("load", function () {
            var canvas = document.createElement("canvas");
            canvas.width = img.width;
            canvas.height = img.height;
            var context = canvas.getContext("2d");
            context.fillStyle = rex.customizer_labelcolor;
            context.fillRect(0, 0, img.width, img.height);
            context.drawImage(img, 0, 0);
            context.fill();
            link.type = "image/x-icon";
            link.href = canvas.toDataURL();
        });
        img.src = faviconUrl;
    }

    if (typeof rex.customizer_showlink !== "undefined" && rex.customizer_showlink != '' && !$('.be-style-customizer-title').length) {
        $('.rex-nav-top .navbar-header').append(rex.customizer_showlink);
    }
};
