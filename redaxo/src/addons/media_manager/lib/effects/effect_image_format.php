<?php

/**
 * @package redaxo\media-manager
 */

class rex_effect_image_format extends rex_effect_abstract
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
    private static $convert_to_default = 'webp';

    public function execute()
    {
        $media = $this->media;

        $ext = strtolower($media->getFormat());
        $ext = 'jpeg' === $ext ? 'jpg' : $ext;
        // skip if extension is not in list
        if (!in_array($ext, self::$convert_types)) {
            return;
        }

        if (!isset(self::$convert_to[$this->params['convert_to']])) {
            $convert_to = self::$convert_to[self::$convert_to_default];
        } else {
            $convert_to = self::$convert_to[$this->params['convert_to']];
        }
        if ($convert_to['ext'] == $ext) {
            return;
        }

        $media->asImage();
        $imageObject = $media->getImage();
        if (null === $imageObject) {
            return;
        }

        switch ($convert_to['ext']) {
            case 'webp':
                imagepalettetotruecolor($imageObject); // Prevent error 'Paletter image not supported by webp' (PNG mit indizierten Farben)
                break;

             case 'gif':
                $w = $media->getWidth();
                $h = $media->getHeight();

                $transparencyColor = ['red' => 1, 'green' => 2, 'blue' => 3];

                $newimage = imagecreatetruecolor($w, $h);

                $transparencyIndex = imagecolortransparent($imageObject);
                if ($transparencyIndex >= 0) {
                    $transparencyColor = imagecolorsforindex($imageObject, $transparencyIndex);
                    $bgcolor = imagecolorallocate($newimage, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
                } else {
                    $bgcolor = imagecolorallocate($newimage, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
                }

                imagefill($newimage, 0, 0, $bgcolor);
                imagecolortransparent($newimage, $bgcolor);
                imagecopyresampled($newimage, $imageObject, 0, 0, 0, 0, $w, $h, $w, $h);
                $imageObject = $newimage;
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
        return rex_i18n::msg('media_manager_effect_image_format');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_image_format_convertto'),
                'name' => 'convert_to',
                'type' => 'select',
                'options' => self::$convert_tos,
                'default' => self::$convert_to_default,
            ],
        ];
    }
}
