<?php
/**
 * @var $element rex_minibar_element
 */
$element = $this->element;

$class = 'rex-minibar-element ';
$class .= rex_string::normalize(get_class($element), '-');
$class .= ($element->getOrientation() == rex_minibar_element::RIGHT ? ' rex-minibar-element-right' : '');
$class .= ($element->isDanger() ? ' rex-minibar-status-danger' : '');
$class .= ($element->isWarning() ? ' rex-minibar-status-warning' : '');
$class .= ($element->isPrimary() ? ' rex-minibar-status-primary' : '');

$onmouseover = '';
if ($element instanceof rex_minibar_lazy_element && rex_minibar_lazy_element::isFirstView()) {
    $elementId = get_class($element);
    $context = rex_context::restore();
    $url = $context->getUrl(['lazy_element' => $elementId] + rex_api_minibar::getUrlParams());
    $onmouseover = <<<EOD
    var that = this;
    window._rex_minibar_req = window._rex_minibar_req || {};
    if (window._rex_minibar_req.$elementId) return;
    
    window._rex_minibar_req.$elementId = true;
    window.fetch('$url')
    .then(function(response) {
        if (!response.ok) {
            throw new Error('HTTP error, status = ' + response.status);
        }
        return response.text();
    })
    .then(function(text) {
        that.outerHTML = text;
        window._rex_minibar_req.$elementId = false;
    })
    .catch(function(error) {
        console.error(error)
        window._rex_minibar_req.$elementId = false;
    });
EOD;
}

?>
<div class="<?= $class ?>" onmouseover="<?= $onmouseover ?>"><?= $element->render(); ?></div>
