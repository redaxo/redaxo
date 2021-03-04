<?php

/**
 * @package redaxo\media-manager
 */

class rex_effect_image_format extends rex_effect_abstract
{
    private static $convertTypes = [
        'jpg',
        'png',
        'gif',
        'webp',
    ];

    private static $convertTo = [
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

    private static $convertTos = ['jpg', 'png', 'gif', 'webp'];
    private static $convertToDefault = 'webp';

    public function execute()
    {
        $media = $this->media;

        $ext = strtolower($media->getFormat());
        $ext = 'jpeg' === $ext ? 'jpg' : $ext;
        // skip if extension is not in list
        if (!in_array($ext, self::$convertTypes)) {
            return;
        }

        if (!isset(self::$convertTo[$this->params['convert_to']])) {
            $convertTo = self::$convertTo[self::$convertToDefault];
        } else {
            $convertTo = self::$convertTo[$this->params['convert_to']];
        }
        if ($convertTo['ext'] == $ext) {
            return;
        }

        $media->asImage();
        $imageObject = $media->getImage();
        if (null === $imageObject) {
            return;
        }

        switch ($convertTo['ext']) {
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
        $filenameWoExt = substr($filename, 0, (strlen($filename) - strlen($ext)));
        $targetFilename = $filenameWoExt . $convertTo['ext'];

        $media->setImage($imageObject);
        $media->setFormat($convertTo['ext']);
        $media->setMediaFilename($targetFilename);
        $media->setHeader('Content-Type', $convertTo['content-type']);
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
                'options' => self::$convertTos,
                'default' => self::$convertToDefault,
            ],
        ];
    }
}
