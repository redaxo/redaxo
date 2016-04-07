<?php

/**
 * @package redaxo\metainfo
 *
 * @internal
 */
class rex_form_templates_element extends rex_form_select_element
{
    private $chkbox_element;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_metainfo_table_expander $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);

        $this->chkbox_element = new rex_form_checkbox_element('');
        $this->chkbox_element->setAttribute('name', 'enable_templates');
        $this->chkbox_element->setAttribute('id', 'enable-templates-checkbox');
        $this->chkbox_element->addOption(rex_i18n::msg('minfo_field_label_all_templates'), '');

        $sql = rex_sql::factory();
        $options = $sql->getArray("SELECT name, id FROM rex_template WHERE active = 1");

        $rex_select = new rex_select();

        foreach ($options as $option)
            $rex_select->addOption($option["name"], $option["id"]);

        $rex_select->setMultiple(true);
        $this->setSelect($rex_select);
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

            $("#enable-templates-checkbox").click(function() {
                $("#' . $slctDivId . '").slideToggle("slow");
                if($(this).is(":checked"))
                {
                    $("option:selected", "#' . $slctDivId . '").each(function () {
                        $(this).removeAttr("selected");
                    });
                }
            });

            if($("#enable-templates-checkbox").is(":checked")) {
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
