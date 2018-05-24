$(document).on('rex:ready', function (event, container) {
    Customizer.init(container);
});

var Customizer = function () {};

Customizer.init = function (container)
{
    var cm_editor = {};
    var cm = 0;

    container.find("#rex-rex_cronjob_phpcode textarea, #rex-page-modules-actions textarea.form-control, textarea.rex-code, textarea.codemirror, textarea#previewaction , textarea#presaveaction, textarea#postsaveaction").each(function() {
        var t = $(this);
        var id = t.attr("id");

        if(typeof id === "undefined") {
            cm++;
            id = 'codemirror-id-'+cm;
            t.attr("id",id);
        }

        var mode = "application/x-httpd-php";
        var theme = rex.customizer_codemirror_defaulttheme;

        var new_mode = t.attr("data-codemirror-mode");
        var new_theme = t.attr("data-codemirror-theme");

        if(typeof new_mode !== "undefined") {
            mode = new_mode;
        }

        if(typeof new_theme !== "undefined") {
            theme = new_theme;
        }

        cm_editor[cm] = CodeMirror.fromTextArea(document.getElementById(id), {
            mode: mode,
            theme: theme,
            lineNumbers: true,
            lineWrapping: true,
            styleActiveLine: true,
            matchBrackets: true,
            autoCloseBrackets: true,
            matchTags: {bothTags: true},
            indentUnit: 4,
            indentWithTabs: false,
            enterMode: "keep",
            tabMode: "shift",
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
            foldGutter: true,
            extraKeys: {
                "F11": function(cm) {
                    cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                },
                "Esc": function(cm) {
                    if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                }
            }
        });
    });

    if (typeof rex.customizer_labelcolor !== "undefined" && rex.customizer_labelcolor != '') {
        $('.rex-nav-top').css('border-bottom','10px solid '+rex.customizer_labelcolor)
    }

    if (typeof rex.customizer_showlink !== "undefined" && rex.customizer_showlink != '' && !$('.be-style-customizer-title').length) {
        $('.rex-nav-top .navbar-header').append(rex.customizer_showlink);
    }
};
