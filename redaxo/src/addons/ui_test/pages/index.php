<?php

/**
 * @package redaxo5
 */

$content = '';

/* panels ----------------------------------------------------------------------------- */

$content.= '<br><h2>Panels</h2><br>';

// buttons
$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="" value="">Speichern</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

// body
$body = 'Hello I’ve waited here for you <a href="#">Everlong</a> Tonight I throw myself into and out of the red <code>Out of her head</code>, she sang Come down and waste away with me Down with me Slow, how you wanted it to be I’m over my head Out of her head, she sang And I wonder';



$fragment = new rex_fragment();
$fragment->setVar('title', 'default', false);
$fragment->setVar('body', $body, false);
$fragment->setVar('buttons', $buttons, false);
$content.= $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'edit', false);
$fragment->setVar('body', $body, false);
$fragment->setVar('buttons', $buttons, false);
$content.= $fragment->parse('core/page/section.php');



$fragment = new rex_fragment();
$fragment->setVar('class', 'primary', false);
$fragment->setVar('title', 'primary', false);
$fragment->setVar('body', $body, false);
$content.= $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'success', false);
$fragment->setVar('title', 'success', false);
$fragment->setVar('body', $body, false);
$content.= $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', 'info', false);
$fragment->setVar('body', $body, false);
$content.= $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'warning', false);
$fragment->setVar('title', 'warning', false);
$fragment->setVar('body', $body, false);
$content.= $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'danger', false);
$fragment->setVar('title', 'danger', false);
$fragment->setVar('body', $body, false);
$content.= $fragment->parse('core/page/section.php');



/* alerts ----------------------------------------------------------------------------- */

$content.= '<br><h2>Alerts</h2><br>';

$alert = 'Hello I’ve waited here for you <a href="#">Everlong</a> Tonight I throw myself into and out of the red <code>Out of her head</code>, she sang';

$content.= '<div class="alert alert-success">' . $alert . '</div>';
$content.= '<div class="alert alert-info">' . $alert . '</div>';
$content.= '<div class="alert alert-warning">' . $alert . '</div>';
$content.= '<div class="alert alert-danger">' . $alert . '</div>';







echo '<div>'. $content . '</div>';
