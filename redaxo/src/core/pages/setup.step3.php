<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($successArray) && is_array($successArray));
assert(isset($errorArray) && is_array($errorArray));
assert(isset($cancelSetupBtn));

$content = '';

if (count($successArray) > 0) {
    $content .= '<ul><li>' . implode('</li><li>', $successArray) . '</li></ul>';
}

$buttons = '';
$class = '';
if (count($errorArray) > 0) {
    $class = 'error';
    $content .= implode('', $errorArray);

    $buttons = '<a class="btn btn-setup" href="' . $context->getUrl(['step' => 4]) . '">' . rex_i18n::msg('setup_312') . '</a>';
} else {
    $class = 'success';
    $buttons = '<a class="btn btn-setup" href="' . $context->getUrl(['step' => 4]) . '">' . rex_i18n::msg('setup_310') . '</a>';
}

$security = '<div class="rex-js-setup-security-message" style="display:none">' . rex_view::error(rex_i18n::msg('setup_security_msg') . '<br />' . rex_i18n::msg('setup_no_js_security_msg')) . '</div>';
$security .= '<noscript>' . rex_view::error(rex_i18n::msg('setup_no_js_security_msg')) . '</noscript>';

$security .= '<script>

    jQuery(function($){
        var whiteUrl = "' . rex_url::backend('index.php') . '";

        // test url, which is not expected to be accessible
        // after each expected error, run a request which is expected to succeed.
        // that way we try to make sure tools like fail2ban dont block the client
        var blacklistedUrls = [
            "' . rex_url::backend('bin/console') . '",
            whiteUrl,
            "' . rex_url::backend('data/.redaxo') . '",
            whiteUrl,
            "' . rex_url::backend('src/core/boot.php') . '",
            whiteUrl,
            "' . rex_url::backend('cache/.redaxo') . '"
        ];

        // NOTE: we have essentially a copy of this code in checkHtaccess() - see standard.js
        $.each(blacklistedUrls, function (i, url) {
            $.ajax({
                url: url,
                cache: false,
                success: function(data) {
                    if (i % 2 == 0) {
                        $(".rex-js-setup-security-message").show();
                        $(".rex-js-setup-section").hide();
                    }
                }
            });
        });

    })

</script>';

foreach (rex_setup::checkPhpSecurity() as $warning) {
    $security .= rex_view::warning($warning);
}

echo rex_view::title(rex_i18n::msg('setup_300').$cancelSetupBtn);

$fragment = new rex_fragment();
$fragment->setVar('class', $class, false);
$fragment->setVar('title', rex_i18n::msg('setup_307'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
echo '<div class="rex-js-setup-section">' . $fragment->parse('core/page/section.php') . '</div>';
echo $security;
