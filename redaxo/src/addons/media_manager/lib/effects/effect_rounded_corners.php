<?php

/**
 * Runde Ecken.
 *
 * @author staabm
 *
 * @package redaxo\media-manager
 */

class rex_effect_rounded_corners extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();
        $gdimage = $this->media->getImage();
        $w = $this->media->getWidth();
        $h = $this->media->getHeight();

        $radius = [
            'tl' => $this->params['topleft'],
            'tr' => $this->params['topright'],
            'br' => $this->params['bottomright'],
            'bl' => $this->params['bottomleft'],
        ];

        $colour = 'ffffff';

        foreach ($radius as $k => $r) {
            if (empty($r) || $r < 0) {
                continue;
            }

            $cornerImage = imagecreatetruecolor($r, $r);

            $clearColour = imagecolorallocate($cornerImage, 0, 0, 0);

            $solidColour = imagecolorallocate(
                $cornerImage,
                hexdec(substr($colour, 0, 2)),
                hexdec(substr($colour, 2, 2)),
                hexdec(substr($colour, 4, 2))
            );

            imagecolortransparent($cornerImage, $clearColour);

            imagefill($cornerImage, 0, 0, $solidColour);

            imagefilledellipse($cornerImage, $r, $r, $r * 2, $r * 2, $clearColour);

            switch ($k) {
                case 'tl':
                    imagecopymerge($gdimage, $cornerImage, 0, 0, 0, 0, $r, $r, 100);
                    break;

                case 'tr':
                    $cornerImage = imagerotate($cornerImage, 270, 0);
                    imagecopymerge($gdimage, $cornerImage, $w - $r, 0, 0, 0, $r, $r, 100);
                    break;

                case 'br':
                    $cornerImage = imagerotate($cornerImage, 180, 0);
                    imagecopymerge($gdimage, $cornerImage, $w - $r, $h - $r, 0, 0, $r, $r, 100);
                    break;

                case 'bl':
                    $cornerImage = imagerotate($cornerImage, 90, 0);
                    imagecopymerge($gdimage, $cornerImage, 0, $h - $r, 0, 0, $r, $r, 100);
                    break;
            }
        }

        // Transparenz erhalten
        //$this->keepTransparent($des);
        //imagecopyresampled($des, $gdimage, 0, 0, $offset_width, $offset_height, $this->params['width'], $this->params['height'], $this->params['width'], $this->params['height']);

        //$gdimage = $des;
        //$this->image->refreshDimensions();
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_rounded_corners');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_rounded_corners_topleft'),
                'name' => 'topleft',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_rounded_corners_topright'),
                'name' => 'topright',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_rounded_corners_bottomleft'),
                'name' => 'bottomleft',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_rounded_corners_bottomright'),
                'name' => 'bottomright',
                'type' => 'int',
            ],
        ];
    }
}
