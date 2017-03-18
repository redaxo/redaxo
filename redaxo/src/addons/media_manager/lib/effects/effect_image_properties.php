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

        if ('default' !== $this->params['jpg_progressive']) {
            $media->setImageProperty('jpg_progressive', 'yes' === $this->params['jpg_progressive']);
        }

        if (!empty($this->params['png_compression'])) {
            $media->setImageProperty('png_compression', $this->params['png_compression']);
        }
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_image_properties_jpg_quality'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_jpg_quality_notice'),
                'name' => 'jpg_quality',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_image_properties_jpg_progressive'),
                'name' => 'jpg_progressive',
                'type' => 'select',
                'options' => ['default', 'no', 'yes'],
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_image_properties_png_compression'),
                'notice' => rex_i18n::msg('media_manager_effect_image_properties_png_compression_notice'),
                'name' => 'png_compression',
                'type' => 'int',
            ],
        ];
    }
}
