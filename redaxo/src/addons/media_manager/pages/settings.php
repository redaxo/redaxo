<?php

/**
 * media_manager Addon
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]yakmara[dot]de Jan Kristinus
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

// rex_request();

$content = '';

$func = rex_request('func', 'string');
$jpg_quality = rex_request('jpg_quality', 'int');

if ($func == 'update') {
    if ($jpg_quality > 100) {
        $jpg_quality = 100;
    } elseif ($jpg_quality < 0) {
        $jpg_quality = 0;
    }

    $this->setConfig('jpg_quality', $jpg_quality);
    echo rex_view::info($this->i18n('config_saved'));

}

$content .= '
    <fieldset>
        <input type="hidden" name="func" value="update" />
';


        $formElements = [];
        $n = [];
        $n['class'] = 'rex-range-input-group';
        $n['label'] = '<label for="rex-js-rating-text-jpg-quality">' . $this->i18n('jpg_quality') . ' [0-100]</label>';
        //$n['field'] = '<input class="form-control" type="text" id="rex-jpg-quality" name="jpg_quality" value="' . htmlspecialchars($this->getConfig('jpg_quality')) . '" />';
        $n['left'] = '<input id="rex-js-rating-source-jpg-quality" type="range" min="0" max="100" step="1" value="' . htmlspecialchars($this->getConfig('jpg_quality')) . '" />';
        $n['field'] = '<input class="form-control" id="rex-js-rating-text-jpg-quality" type="text" id="rex-jpg-quality" name="jpg_quality" value="' . htmlspecialchars($this->getConfig('jpg_quality')) . '" />';
        $n['right'] = '%';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/input_group.php');



        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-primary" href="' . rex_url::currentBackendPage() . '"><i class="rex-icon rex-icon-back"></i> ' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-primary" type="submit" name="sendit" value="1"' . rex::getAccesskey(rex_i18n::msg('update'), 'apply') . '>' . rex_i18n::msg('update') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');


$content .= '
    </fieldset>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('subpage_config'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>

    <script type="text/javascript">
    <!--

    jQuery(function($) {

        $("#rex-js-rating-text-jpg-quality").change(function(){
            $("#rex-js-rating-source-jpg-quality").val(this.value);
        });
        $("#rex-js-rating-source-jpg-quality").change(function(){
            $("#rex-js-rating-text-jpg-quality").val(this.value);
            $("#rex-js-rating-text-jpg-quality").trigger("change");
        });

    });

    //-->
    </script>

    ';

echo $content;
