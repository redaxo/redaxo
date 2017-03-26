<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_header extends rex_effect_abstract
{
    public function execute()
    {
        if ($this->params['cache'] == 'no_cache') {
            $this->media->setHeader('Cache-Control', 'must-revalidate, proxy-revalidate, private, no-cache, max-age=0');
            $this->media->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT'); // in the past
        }

        if ($this->params['download'] == 'download') {
            $this->media->setHeader('Content-Disposition', 'attachment; filename="' . basename($this->media->getMediaFilename()) . '";');
        }

        /*
         header("Pragma: public"); // required
         header("Expires: 0");
         header("Content-Transfer-Encoding: binary");
         header("Content-Length: ".$fsize);
         */
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_header_download'),
                'name' => 'download',
                'type' => 'select',
                'options' => ['open_media', 'download'],
                'default' => 'open_media',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_header_cache'),
                'name' => 'cache',
                'type' => 'select',
                'options' => ['no_cache', 'cache'],
                'default' => 'no_cache',
            ],
        ];
    }
}
