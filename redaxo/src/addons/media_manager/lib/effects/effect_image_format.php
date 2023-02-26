<?php

/**
 * @package redaxo\media-manager
 */

class rex_effect_image_format extends rex_effect_abstract
{
    private const CONVERT_TYPES = [
        'jpg',
        'png',
        'gif',
        'webp',
        'avif',
    ];

    private const CONVERT_TO = [
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
        'avif' => [
            'ext' => 'avif',
            'content-type' => 'image/avif',
        ],
    ];

    private const CONVERT_TOS = ['jpg', 'png', 'gif', 'webp', 'avif'];
    private const CONVERT_TO_DEFAULT = 'webp';

    public function execute()
    {
        $media = $this->media;

        $ext = strtolower($media->getFormat());
        $ext = 'jpeg' === $ext ? 'jpg' : $ext;
        // skip if extension is not in list
        if (!in_array($ext, self::CONVERT_TYPES)) {
            return;
        }

        if (!isset(self::CONVERT_TO[(string) $this->params['convert_to']])) {
            $convertTo = self::CONVERT_TO[self::CONVERT_TO_DEFAULT];
        } else {
            $convertTo = self::CONVERT_TO[(string) $this->params['convert_to']];
        }
        if ($convertTo['ext'] == $ext) {
            return;
        }

        $media->asImage();
        $imageObject = $media->getImage();

        switch ($convertTo['ext']) {
            case 'webp':
                imagepalettetotruecolor($imageObject); // Prevent error 'Paletter image not supported by webp' (PNG mit indizierten Farben)
                break;

            case 'gif':
                $w = (int) $media->getWidth();
                $h = (int) $media->getHeight();

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
        $filenameWoExt = substr($filename, 0, strlen($filename) - strlen(rex_file::extension($filename))); // do not use $ext or getFormat because of jpeg vs. jpg
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
                'options' => array_filter(self::CONVERT_TOS, static function (string $format): bool {
                    return match ($format) {
                        'webp' => function_exists('imagewebp'),
                        'avif' => function_exists('imageavif'),
                        default => true,
                    };
                }),
                'default' => self::CONVERT_TO_DEFAULT,
            ],
        ];
    }
}
