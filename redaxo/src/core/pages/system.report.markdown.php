<?php

$report = rex_system_report::factory()->asMarkdown();

echo '<p><clipboard-copy for="rex-system-report-markdown" class="btn btn-copy btn-primary">'. rex_i18n::msg('copy_to_clipboard') .'</clipboard-copy></p>';

// there must be no whitespace between div and pre and within pre
// otherwise the copied markdown via clipboard-copy can be invalid
echo '<div id="rex-system-report-markdown" contenteditable="true" spellcheck="false">';
echo '<pre><code>'.rex_escape($report).'</code></pre>';
echo '</div>';

echo '
    <script nonce="'.rex_response::getNonce().'">
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
