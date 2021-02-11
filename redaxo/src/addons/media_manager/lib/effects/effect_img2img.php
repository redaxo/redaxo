<?php

/**
 * @package redaxo\media-manager
 */

class rex_effect_img2img extends rex_effect_abstract
{
    private static $convert_types = [
        'png',
        'jpg',
        'gif',
        'webp',
    ];

    private static $convert_to = [
        'jpg' => [
            'ext' => 'jpg',
            'content-type' => 'image/jpeg',
        ],
        'png' => [
            'ext' => 'png',
            'content-type' => 'image/png',
        ],
        'gif' => [
            'ext' => 'gif',
            'content-type' => 'image/gif',
        ],
        'webp' => [
            'ext' => 'webp',
            'content-type' => 'image/webp',
        ],
    ];

    private static $convert_tos = ['jpg', 'png', 'gif', 'webp'];
    private static $convert_to_default = 'jpg';
    private static $convert_to_default_quality = 80;

    public function execute()
    {
        $this->media->asImage();
        $imageObject = $this->media->getImage();
        if (null === $imageObject) {
            return;
        }

        if (!isset(self::$convert_to[$this->params['convert_to']])) {
            $convert_to = self::$convert_to[self::$convert_to_default];
        } else {
            $convert_to = self::$convert_to[$this->params['convert_to']];
        }

        $quality = 0;
        switch ($convert_to['ext']) {
            case 'jpg':
                $quality = rex_addon::get('media_manager')->getConfig('jpg_quality');
                break;

            case 'webp':
                $quality = rex_addon::get('media_manager')->getConfig('webp_quality');
                break;

            case 'png':
                $quality = rex_addon::get('media_manager')->getConfig('png_compression');
                break;
        }

        if (!$quality) {
            $quality = self::$convert_to_default_quality;
        }
        if ($this->params['quality'] && (int) $this->params['quality'] > 0) {
            $quality = $this->params['quality'];
        }
        if (100 < (int) $quality) {
            $quality = 100;
        }
        if ('png' == $convert_to['ext']) {
            if (9 < (int) $quality) {
                $quality = 9;
            }
        }

        $from_path = realpath($this->media->getMediaPath());
        $ext = rex_file::extension($from_path);

        // skip if extension is not in list
        if (!in_array(strtolower($ext), self::$convert_types)) {
            return;
        }

        $filename = $this->media->getMediaFilename();
        $filename_wo_ext = substr($filename, 0, (strlen($filename) - strlen($ext)));
        $targetFilename = $filename_wo_ext . $convert_to['ext'];
        $to_path = rex_path::addonCache('media_manager', 'media_manager__convert_img2img_'.md5($this->media->getMediaPath()).'_'.$targetFilename);

        // save
        $funcOut = 'image' . strtolower('jpg' == $this->params['convert_to'] ? 'jpeg' : $this->params['convert_to']);

        // no support for the output function
        if (!function_exists($funcOut)) {
            rex_file::delete($to_path);
            return;
        }

        imagepalettetotruecolor($imageObject);
        $saved = @call_user_func($funcOut, $imageObject, $to_path, $quality);

        if (!$saved) {
            return;
        }

        $funcIn = 'imagecreatefrom' . strtolower('jpg' == $this->params['convert_to'] ? 'jpeg' : $this->params['convert_to']);

        // no support for the input function
        if (!function_exists($funcIn)) {
            rex_file::delete($to_path);
            return;
        }

        $desObject = @call_user_func($funcIn, $to_path);

        $this->media->setImage($desObject);
        $this->media->setSourcePath($to_path);
        $this->media->setFormat($convert_to['ext']);
        $this->media->setMediaFilename($targetFilename);
        $this->media->setHeader('Content-Type', $convert_to['content-type']);

        register_shutdown_function(static function () use ($to_path) {
            rex_file::delete($to_path);
        });
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_img2img');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_img2img_convertto'),
                'name' => 'convert_to',
                'type' => 'select',
                'options' => self::$convert_tos,
                'default' => self::$convert_to_default,
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_img2img_quality'),
                'notice' => rex_i18n::msg('media_manager_effect_img2img_quality_notice'),
                'name' => 'quality',
                'type' => 'int',
                'default' => self::$convert_to_default_quality,
            ],
        ];
    }
}
