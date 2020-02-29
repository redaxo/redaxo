<?php

$report = rex_system_report::factory()->asMarkdown();

echo '
    <p><clipboard-copy for="rex-system-report-markdown" class="btn btn-copy btn-primary">'. rex_i18n::msg('copy_to_clipboard') .'</clipboard-copy></p>

    <div id="rex-system-report-markdown" contenteditable="true" spellcheck="false">
        <pre>'.rex_escape($report).'</pre>
    </div>


    <script>
        $("#rex-system-report-markdown")
            .on("cut paste", function (event) {
                event.preventDefault();
            })
            .on("keydown", function (event) {
                if (!event.metaKey) {
                    event.preventDefault();
                }
            });
    </script>
';
