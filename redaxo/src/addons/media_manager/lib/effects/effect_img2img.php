<?php

/**
 * @package redaxo\media-manager
 */

class rex_effect_img2img extends rex_effect_abstract
{
    private static $convert_types = [
        'jpg',
        'png',
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

    public function execute()
    {
        $media = $this->media;

        $media->asImage();
        $imageObject = $media->getImage();

        $this->keepTransparent($imageObject);

        if (null === $imageObject) {
            return;
        }

        $ext = $media->getFormat();
        // skip if extension is not in list
        if (!in_array(strtolower($ext), self::$convert_types)) {
            return;
        }

        if (!isset(self::$convert_to[$this->params['convert_to']])) {
            $convert_to = self::$convert_to[self::$convert_to_default];
        } else {
            $convert_to = self::$convert_to[$this->params['convert_to']];
        }

        // convert image
        $addon = rex_addon::get('media_manager');

        imagepalettetotruecolor($imageObject);

        $interlace = $media->getImageProperty(rex_managed_media::PROP_INTERLACE, $addon->getConfig('interlace'));
        imageinterlace($imageObject, in_array($convert_to['ext'], $interlace) ? 1 : 0);

        switch ($convert_to['ext']) {
            case 'jpg':
                $quality = $media->getImageProperty(rex_managed_media::PROP_JPG_QUALITY, $addon->getConfig('jpg_quality'));
                imagejpeg($imageObject, null, $quality);
                break;

            case 'webp':
                $quality = $media->getImageProperty(rex_managed_media::PROP_WEBP_QUALITY, $addon->getConfig('webp_quality'));
                imagewebp($imageObject, null, $quality);
                break;

            case 'png':
                $compression = $media->getImageProperty(rex_managed_media::PROP_PNG_COMPRESSION, $addon->getConfig('png_compression'));
                imagepng($imageObject, null, $compression);
                break;

             case 'gif':
                imagegif($imageObject);
                break;
        }

        $filename = $media->getMediaFilename();
        $filename_wo_ext = substr($filename, 0, (strlen($filename) - strlen($ext)));
        $targetFilename = $filename_wo_ext . $convert_to['ext'];

        $media->setImage($imageObject);
        $media->setFormat($convert_to['ext']);
        $media->setMediaFilename($targetFilename);
        $media->setHeader('Content-Type', $convert_to['content-type']);
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
        ];
    }
}
