<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_form_restrictons_element extends rex_form_select_element
{
    private $chkbox_element;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_metainfo_table_expander $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);

        $this->chkbox_element = new rex_form_checkbox_element('');
        $this->chkbox_element->setAttribute('name', 'enable_restrictions');
        $this->chkbox_element->setAttribute('id', 'enable-restrictions-checkbox');
        $this->chkbox_element->addOption(rex_i18n::msg('minfo_field_label_no_restrictions'), '');

        if ($table->getPrefix() == rex_metainfo_article_handler::PREFIX || $table->getPrefix() == rex_metainfo_category_handler::PREFIX) {
            $restrictionsSelect = new rex_category_select(false, false, true, false);
        } elseif ($table->getPrefix() == rex_metainfo_media_handler::PREFIX) {
            $restrictionsSelect = new rex_media_category_select();
        } else {
            throw new rex_exception('Unexpected TablePrefix "' . $table->getPrefix() . '"!');
        }

        $restrictionsSelect->setMultiple(true);
        $this->setSelect($restrictionsSelect);
        $this->setNotice(rex_i18n::msg('ctrl'));
    }

    public function get()
    {
        $slctDivId = $this->getAttribute('id') . '-div';

        // Wert aus dem select in die checkbox übernehmen
        $this->chkbox_element->setValue($this->getValue());

        $html = '';

        $html .= '
        <script type="text/javascript">
        <!--

        jQuery(function($) {

            $("#enable-restrictions-checkbox").click(function() {
                $("#' . $slctDivId . '").slideToggle("slow");
                if($(this).is(":checked"))
                {
                    $("option:selected", "#' . $slctDivId . '").each(function () {
                        $(this).removeAttr("selected");
                    });
                }
            });

            if($("#enable-restrictions-checkbox").is(":checked")) {
                $("#' . $slctDivId . '").hide();
            }
        });

        //-->
        </script>';

        $html .= $this->chkbox_element->get();

        $html .= '<div id="' . $slctDivId . '">' . parent :: get() . '</div>';

        return $html;
    }
}
