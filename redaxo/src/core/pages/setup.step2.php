<?php

assert(isset($context) && $context instanceof rex_context);

$license_file = rex_path::base('LICENSE.md');
$license = '<p>' . nl2br(rex_file::get($license_file)) . '</p>';

$content = rex_i18n::rawMsg('setup_202');
$content .= $license;

$buttons = '<a class="btn btn-setup" href="' . $context->getUrl(['step' => 3]) . '">' . rex_i18n::msg('setup_203') . '</a>';

echo rex_view::title(rex_i18n::msg('setup_200'));

$fragment = new rex_fragment();
$fragment->setVar('heading', rex_i18n::msg('setup_201'), false);
$fragment->setVar('body', '<div class="rex-scrollable">' . $content . '</div>', false);
$fragment->setVar('buttons', $buttons, false);
echo $fragment->parse('core/page/section.php');
