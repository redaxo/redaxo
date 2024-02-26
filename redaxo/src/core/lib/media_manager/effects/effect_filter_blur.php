<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_filter_blur extends rex_effect_abstract
{
    private const OPTIONS = ['', 'gaussian', 'selective'];
    private const OPTIONS_SMOOTHIT = [-10, -9, -8, -7, -6, -5, -4, -3, -2, -1, '', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    public function execute()
    {
        $options = [];
        $options['gaussian'] = IMG_FILTER_GAUSSIAN_BLUR;
        $options['selective'] = IMG_FILTER_SELECTIVE_BLUR;

        $this->media->asImage();
        $gdimage = $this->media->getImage();

        $this->params['repeats'] = (int) $this->params['repeats'];
        if ($this->params['repeats'] < 0) {
            return;
        }

        if (!in_array($this->params['type'], self::OPTIONS)) {
            $this->params['type'] = '';
        }

        if (!in_array($this->params['smoothit'], self::OPTIONS_SMOOTHIT)) {
            $this->params['smoothit'] = '';
        }

        for ($i = 0; $i < $this->params['repeats']; ++$i) {
            if ('' != $this->params['smoothit']) {
                imagefilter($gdimage, IMG_FILTER_SMOOTH, (int) $this->params['smoothit']);
            }

            if ('' != $this->params['type']) {
                imagefilter($gdimage, $options[$this->params['type']]);
            }
        }
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_blur');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_blur_repeats'),
                'name' => 'repeats',
                'type' => 'int',
                'default' => '10',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_blur_type'),
                'name' => 'type',
                'type' => 'select',
                'options' => self::OPTIONS,
                'default' => 'gaussian',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_blur_smoothit'),
                'name' => 'smoothit',
                'type' => 'select',
                'options' => self::OPTIONS_SMOOTHIT,
                'default' => '',
            ],
        ];
    }
}
