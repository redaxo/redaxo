<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_jpg_quality extends rex_effect_abstract
{
    public function execute()
    {
        global $REX;
        $this->image->img['quality'] = $this->params['quality'];
    }

    public function getParams()
    {
        global $REX,$I18N;

        return array(
            array(
                'label' => 'JPG quality',
                'name' => 'quality',
                'type' => 'int',
                'default' => 85,
            ),
        );
    }
}
