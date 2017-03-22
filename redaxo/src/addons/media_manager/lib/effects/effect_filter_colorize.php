<?php

class rex_effect_filter_colorize extends rex_effect_abstract
{
    public function execute()
    {
        $this->params['filter_r'] = (int) $this->params['filter_r'];
        if ($this->params['filter_r'] < 0) {
            return;
        }
        $this->params['filter_g'] = (int) $this->params['filter_g'];
        if ($this->params['filter_g'] < 0) {
            return;
        }
        $this->params['filter_b'] = (int) $this->params['filter_b'];
        if ($this->params['filter_b'] < 0) {
            return;
        }

        $this->media->asImage();
        $img = $this->media->getImage();

        imagefilter($img, IMG_FILTER_COLORIZE, $this->params['filter_r'], $this->params['filter_g'], $this->params['filter_b']);
        $this->media->setImage($img);
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_colorize_r'),
                'name' => 'filter_r',
                'type' => 'int',
                'default' => '',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_colorize_g'),
                'name' => 'filter_g',
                'type' => 'int',
                'default' => '',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_colorize_b'),
                'name' => 'filter_b',
                'type' => 'int',
                'default' => '',
            ],
        ];
    }
}
