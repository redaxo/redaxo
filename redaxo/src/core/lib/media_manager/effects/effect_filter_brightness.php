<?php

class rex_effect_filter_brightness extends rex_effect_abstract
{
    public function execute()
    {
        $this->params['brightness'] = (int) $this->params['brightness'];
        if (!$this->params['brightness']) {
            $this->params['brightness'] = 0;
        }
        if ($this->params['brightness'] < -255) {
            $this->params['brightness'] = -255;
        }
        if ($this->params['brightness'] > 255) {
            $this->params['brightness'] = 255;
        }
        $this->media->asImage();
        $img = $this->media->getImage();

        imagefilter($img, IMG_FILTER_BRIGHTNESS, $this->params['brightness']);
        $this->media->setImage($img);
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_brightness');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_brightness_value'),
                'notice' => rex_i18n::msg('media_manager_effect_brightness_notice'),
                'name' => 'brightness',
                'type' => 'int',
                'default' => '',
            ],
        ];
    }
}
