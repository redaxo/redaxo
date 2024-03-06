<?php

use Redaxo\Core\Translation\I18n;

class rex_effect_rotate extends rex_effect_abstract
{
    private const OPTIONS = ['0', '90', '180', '270'];

    public function execute()
    {
        $this->media->asImage();
        $gdimage = $this->media->getImage();
        $gdimage = imagerotate($gdimage, $this->params['rotate'], 0);
        $this->keepTransparent($gdimage);
        $this->media->setImage($gdimage);
    }

    public function getName()
    {
        return I18n::msg('media_manager_effect_rotate');
    }

    public function getParams()
    {
        return [
            [
                'label' => I18n::msg('media_manager_effect_rotate_degree'),
                'name' => 'rotate',
                'type' => 'select',
                'options' => self::OPTIONS,
                'default' => '0',
            ],
        ];
    }
}
