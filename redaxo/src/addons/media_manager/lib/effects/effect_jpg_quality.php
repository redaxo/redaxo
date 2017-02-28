<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_jpg_quality extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->setImageAttribute('quality', $this->params['quality']);
    }

    public function getParams()
    {
        return array(
            array(
                'label' => rex_i18n::msg('media_manager_effect_jpg_quality'),
                'name' => 'quality',
                'type' => 'int',
                'default' => rex_config::get('media_manager', 'jpg_quality', 85),
            ),
        );
    }
}
