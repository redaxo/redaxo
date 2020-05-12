<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($success_array) && is_array($success_array));
assert(isset($error_array) && is_array($error_array));

$content = '';

if (count($success_array) > 0) {
    $content .= '<ul><li>' . implode('</li><li>', $success_array) . '</li></ul>';
}

$buttons = '';
$class = '';
if (count($error_array) > 0) {
    $class = 'error';
    $content .= implode('', $error_array);

    $buttons = '<a class="btn btn-setup" href="' . $context->getUrl(['step' => 4]) . '">' . rex_i18n::msg('setup_312') . '</a>';
} else {
    $class = 'success';
    $buttons = '<a class="btn btn-setup" href="' . $context->getUrl(['step' => 4]) . '">' . rex_i18n::msg('setup_310') . '</a>';
}

$security = '<div class="rex-js-setup-security-message" style="display:none">' . rex_view::error(rex_i18n::msg('setup_security_msg') . '<br />' . rex_i18n::msg('setup_no_js_security_msg')) . '</div>';
$security .= '<noscript>' . rex_view::error(rex_i18n::msg('setup_no_js_security_msg')) . '</noscript>';
$security .= '<script>

    jQuery(function($){
        var urls = [
            "' . rex_url::backend('bin/console') . '",
            "' . rex_url::backend('data/.redaxo') . '",
            "' . rex_url::backend('src/core/boot.php') . '",
            "' . rex_url::backend('cache/.redaxo') . '"
        ];

        $.each(urls, function (i, url) {
            $.ajax({
                url: url,
                cache: false,
                success: function(data) {
                    $(".rex-js-setup-security-message").show();
                    $(".rex-js-setup-section").hide();
                }
            });
        });

    })

</script>';

foreach (rex_setup::checkPhpSecurity() as $warning) {
    $security .= rex_view::warning($warning);
}

echo rex_view::title(rex_i18n::msg('setup_300'));

$fragment = new rex_fragment();
$fragment->setVar('class', $class, false);
$fragment->setVar('title', rex_i18n::msg('setup_307'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
echo '<div class="rex-js-setup-section">' . $fragment->parse('core/page/section.php') . '</div>';
echo $security;
