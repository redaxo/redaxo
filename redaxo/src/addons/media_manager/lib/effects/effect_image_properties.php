<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_image_properties extends rex_effect_abstract
{
    public function execute()
    {
        $media = $this->media;

        if (!empty($this->params['jpg_quality'])) {
            $media->setImageProperty('jpg_quality', $this->params['jpg_quality']);
        }

        if (!empty($this->params['png_compression'])) {
            $media->setImageProperty('png_compression', $this->params['png_compression']);
        }

        if ($this->params['interlace']) {
            $interlace = in_array('- off -', $this->params['interlace']) ? [] : $this->params['interlace'];
            $media->setImageProperty('interlace', $interlace);
        }
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_jpg_quality'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_jpg_quality_notice'),
                'name' => 'jpg_quality',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_png_compression'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_png_compression_notice'),
                'name' => 'png_compression',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_interlace'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_interlace_notice'),
                'name' => 'interlace',
                'type' => 'select',
                'options' => ['- off -', 'jpg', 'png', 'gif'],
                'attributes' => ['multiple' => true, 'class' => 'selectpicker form-control'],
                'suffix' => '
<script type="text/javascript">
    $(function() {
        var $field = $("#media-manager-rex-effect-image-properties-interlace-select");
        
        $field.on("changed.bs.select", function (event, clickedIndex, newValue, oldValue) {
            var off = "- off -";
            if (0 == clickedIndex && newValue) {
                $field.selectpicker("val", [off]);
            }
            if (0 != clickedIndex && newValue) {
                var value = $field.selectpicker("val");
                var index = value.indexOf(off);
                if (index > -1) {
                    value.splice(index, 1);
                    $field.selectpicker("val", value);
                }
            }
        });
    });
</script>',
            ],
        ];
    }
}
