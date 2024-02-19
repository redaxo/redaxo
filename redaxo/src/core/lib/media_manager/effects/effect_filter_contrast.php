<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_filter_contrast extends rex_effect_abstract
{
    public function execute()
    {
        $this->params['contrast'] = (int) $this->params['contrast'];
        if (!$this->params['contrast']) {
            $this->params['contrast'] = 0;
        }
        if ($this->params['contrast'] < -100) {
            $this->params['contrast'] = -100;
        }
        if ($this->params['contrast'] > 100) {
            $this->params['contrast'] = 100;
        }
        $this->media->asImage();
        $img = $this->media->getImage();

        imagefilter($img, IMG_FILTER_CONTRAST, $this->params['contrast']);
        $this->media->setImage($img);
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_contrast');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_contrast_value'),
                'notice' => rex_i18n::msg('media_manager_effect_contrast_notice'),
                'name' => 'contrast',
                'type' => 'int',
                'default' => '',
            ],
        ];
    }
}
