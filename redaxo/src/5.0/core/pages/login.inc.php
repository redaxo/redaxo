<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

rex_title('Login');



$fragment = new rex_fragment();
$fragment->setVar('rex_user_login', $rex_user_login);
$fragment->setVar('rex_user_loginmessage', $rex_user_loginmessage);
echo $fragment->parse('core_login');
unset($fragment);