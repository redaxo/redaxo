<?php

use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\Message;
use Redaxo\Core\View\View;

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

    $buttons = '<a class="btn btn-setup" href="' . $context->getUrl(['step' => 3]) . '">' . I18n::msg('setup_212') . '</a>';
} else {
    $class = 'success';
    $buttons = '<a class="btn btn-setup" href="' . $context->getUrl(['step' => 3]) . '">' . I18n::msg('setup_210') . '</a>';
}

$security = '<div class="rex-js-setup-security-message" style="display:none">' . Message::error(I18n::msg('setup_security_msg') . '<br />' . I18n::msg('setup_no_js_security_msg')) . '</div>';
$security .= '<noscript>' . Message::error(I18n::msg('setup_no_js_security_msg')) . '</noscript>';

$security .= '<script nonce="' . rex_response::getNonce() . '">

    jQuery(function($){
        var allowedUrl = "' . Url::backend('index.php') . '";

        // test url, which is not expected to be accessible
        // after each expected error, run a request which is expected to succeed.
        // that way we try to make sure tools like fail2ban dont block the client
        var urls = [
            "' . Url::backend('bin/console') . '",
            allowedUrl,
            "' . Url::backend('data/.redaxo') . '",
            allowedUrl,
            "' . Url::backend('src/core/boot.php') . '",
            allowedUrl,
            "' . Url::backend('cache/.redaxo') . '"
        ];

        // NOTE: we have essentially a copy of this code in checkHtaccess() - see standard.js
        $.each(urls, function (i, url) {
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
    $security .= Message::warning($warning);
}

echo View::title(I18n::msg('setup_200') . $cancelSetupBtn);

$fragment = new Fragment();
$fragment->setVar('class', $class, false);
$fragment->setVar('title', I18n::msg('setup_207'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
echo '<div class="rex-js-setup-section">' . $fragment->parse('core/page/section.php') . '</div>';
echo $security;
