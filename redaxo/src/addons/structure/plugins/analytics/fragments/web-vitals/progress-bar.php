<?php

/** @var rex_fragment $this */

/** @var rex_analytics_metric $lcp */
$lcp = $this->getVar('lcp');
if (!$lcp) {
    throw new rex_functional_exception(rex_i18n::msg('structure_analytics_fragment_exception', 'lcp'));
}

/** @var rex_analytics_metric $fid */
$fid = $this->getVar('fid');
if (!$fid) {
    throw new rex_functional_exception(rex_i18n::msg('structure_analytics_fragment_exception', 'fid'));
}

/** @var rex_analytics_metric $cls */
$cls = $this->getVar('cls');
if (!$cls) {
    throw new rex_functional_exception(rex_i18n::msg('structure_analytics_fragment_exception', 'cls'));
}

$progress = function($title, $abbr, rex_analytics_metric $metric) {
    $value = $metric->getValue();
    $title = rex_escape($title);
    $abbr = rex_escape($abbr);

    $red = $metric->isRed() ? ' rex-analytics-progress-bar-active' : '';
    $yellow = $metric->isYellow() ? ' rex-analytics-progress-bar-active' : '';
    $green = $metric->isGreen() ? ' rex-analytics-progress-bar-active' : '';

    $redValue = $metric->isRed() ? $value .$metric->getUnit() : '';
    $yellowValue = $metric->isYellow() ? $value .$metric->getUnit() : '';
    $greenValue = $metric->isGreen() ? $value .$metric->getUnit() : '';

    $good = rex_i18n::msg('structure_analytics_good');
    $needsImprovement = rex_i18n::msg('structure_analytics_needs_improvement');
    $poor = rex_i18n::msg('structure_analytics_poor');

    return <<<EOF
                <dl class="rex-analytics-progress-list">
                    <dt><abbr title="${title}">${abbr}</abbr></dt>
                    <dd>
                        <div class="rex-analytics-progress">
                            <div class="rex-analytics-progress-bar rex-analytics-progress-bar-success${green}" title="${good}">${greenValue}</div>
                            <div class="rex-analytics-progress-bar rex-analytics-progress-bar-warning${yellow}" title="${needsImprovement}">${yellowValue}</div>
                            <div class="rex-analytics-progress-bar rex-analytics-progress-bar-danger${red}" title="${poor}">${redValue}</div>
                        </div>
                    </dd>
                </dl>
EOF;
};

$lcpClass = $lcp->isRed() ? ' rex-analytics-progress-bar-danger' : ($lcp->isYellow() ? ' rex-analytics-progress-bar-warning' : ($lcp->isGreen() ? ' rex-analytics-progress-bar-success' : ''));
$fidClass = $fid->isRed() ? ' rex-analytics-progress-bar-danger' : ($fid->isYellow() ? ' rex-analytics-progress-bar-warning' : ($fid->isGreen() ? ' rex-analytics-progress-bar-success' : ''));
$clsClass = $cls->isRed() ? ' rex-analytics-progress-bar-danger' : ($cls->isYellow() ? ' rex-analytics-progress-bar-warning' : ($cls->isGreen() ? ' rex-analytics-progress-bar-success' : ''));
echo sprintf(
    '<td class="rex-table-analytics">
        <div class="rex-analytics">
            <div class="rex-analytics-progress">
                <div class="rex-analytics-progress-bar'.$lcpClass.'"></div>
                <div class="rex-analytics-progress-bar'.$fidClass.'"></div>
                <div class="rex-analytics-progress-bar'.$clsClass.'"></div>
                <div class="rex-analytics-total">94</div>
            </div>
            <div class="rex-analytics-panel">
                '. $progress(rex_i18n::msg('structure_analytics_lcp_long'), rex_i18n::msg('structure_analytics_lcp_abbr'), $lcp) .'
                '. $progress(rex_i18n::msg('structure_analytics_fid_long'), rex_i18n::msg('structure_analytics_fid_abbr'), $fid) .'
                '. $progress(rex_i18n::msg('structure_analytics_cls_long'), rex_i18n::msg('structure_analytics_cls_abbr'), $cls) .'
                per <a href="https://web.dev/vitals/" rel="noopener noreferrer" target="_blank">https://web.dev/vitals/</a>
            </div>
        </div>
    </td>');
