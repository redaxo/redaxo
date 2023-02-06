<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_image_properties extends rex_effect_abstract
{
    private const NO_INTERLACING = '- off -';

    public function execute()
    {
        $media = $this->media;

        $media->asImage();

        if (!empty($this->params['jpg_quality'])) {
            $media->setImageProperty(rex_managed_media::PROP_JPG_QUALITY, $this->params['jpg_quality']);
        }

        if (!empty($this->params['png_compression'])) {
            $media->setImageProperty(rex_managed_media::PROP_PNG_COMPRESSION, $this->params['png_compression']);
        }

        if (!empty($this->params['webp_quality'])) {
            $media->setImageProperty(rex_managed_media::PROP_WEBP_QUALITY, $this->params['webp_quality']);
        }

        if (!empty($this->params['avif_quality'])) {
            $media->setImageProperty(rex_managed_media::PROP_AVIF_QUALITY, $this->params['avif_quality']);
        }
        if (!empty($this->params['avif_speed'])) {
            $media->setImageProperty(rex_managed_media::PROP_AVIF_SPEED, $this->params['avif_speed']);
        }

        if ($this->params['interlace']) {
            $interlace = explode('|', trim($this->params['interlace'], '|'));
            $interlace = in_array(self::NO_INTERLACING, $interlace) ? [] : $interlace;
            $media->setImageProperty(rex_managed_media::PROP_INTERLACE, $interlace);
        }
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_image_properties');
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
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_png_compression_notice').' '.rex_i18n::msg('media_manager_png_compression_note'),
                'name' => 'png_compression',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_webp_quality'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_webp_quality_notice'),
                'name' => 'webp_quality',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_avif_quality'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_avif_quality_notice'),
                'name' => 'avif_quality',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_avif_speed'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_avif_speed_notice'),
                'name' => 'avif_speed',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_interlace'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_interlace_notice'),
                'name' => 'interlace',
                'type' => 'select',
                'options' => [self::NO_INTERLACING, 'jpg', 'png', 'gif'],
                'attributes' => ['multiple' => true, 'class' => 'selectpicker form-control'],
                'suffix' => '
<script type="text/javascript" nonce="' . rex_response::getNonce() . '">
    $(function() {
        var $field = $("#media-manager-rex-effect-image-properties-interlace-select");

        $field.on("changed.bs.select", function (event, clickedIndex, newValue, oldValue) {
            var off = "'. self::NO_INTERLACING .'";
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
