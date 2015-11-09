$(document).on('pjax:end',   function()
{
    var cm_editor = {};
    var cm = 0;

    // $("#rex-rex_cronjob_phpcode textarea, #rex-page-module #rex-wrapper textarea, #rex-page-template #rex-wrapper textarea, textarea.codemirror").each(function(){
	$("#rex-rex_cronjob_phpcode textarea, #rex-page-modules-actions textarea.form-control, textarea.rex-code, textarea.codemirror").each(function(){

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
            lineNumbers: true,
            lineWrapping: true,
            styleActiveLine: true,
            matchBrackets: true,
            mode: mode,
            indentUnit: 4,
            indentWithTabs: false,
            enterMode: "keep",
            tabMode: "shift",
            theme: theme,
            extraKeys: {
                "F11": function(cm) {
                    cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                },
                "Esc": function(cm) {
                    if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                }
            }
        });

        // $(cm_editor[cm].getWrapperElement()).css({
            // "margin-top": t.css("margin-top"),
            // "margin-left": t.css("margin-left"),
            // "margin-bottom": t.css("margin-bottom"),
            // "margin-right": t.css("margin-right")
        // });

        // var height = parseInt(t.height());
        // var width = parseInt(t.width());

        // if(height < 200) {
            // height = 200;
        // }
        // if(width < 300) {
            // width = 300;
        // }

        // cm_editor[cm].setSize(width, height);
        // cm_editor[cm].refresh();

    });

});
