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

        if ('default' !== $this->params['interlace']) {
            $media->setImageProperty('interlace', 'yes' === $this->params['interlace']);
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
                'name' => 'interlace',
                'type' => 'select',
                'options' => ['default', 'no', 'yes'],
            ],
        ];
    }
}
