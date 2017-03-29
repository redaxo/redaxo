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

            $corner_image = imagecreatetruecolor($r, $r);

            $clear_colour = imagecolorallocate($corner_image, 0, 0, 0);

            $solid_colour = imagecolorallocate(
                $corner_image,
                hexdec(substr($colour, 0, 2)),
                hexdec(substr($colour, 2, 2)),
                hexdec(substr($colour, 4, 2))
            );

            imagecolortransparent($corner_image, $clear_colour);

            imagefill($corner_image, 0, 0, $solid_colour);

            imagefilledellipse($corner_image, $r, $r, $r * 2, $r * 2, $clear_colour);

            switch ($k) {
                case 'tl':
                    imagecopymerge($gdimage, $corner_image, 0, 0, 0, 0, $r, $r, 100);
                    break;

                case 'tr':
                    $corner_image = imagerotate($corner_image, 270, 0);
                    imagecopymerge($gdimage, $corner_image, $w - $r, 0, 0, 0, $r, $r, 100);
                    break;

                case 'br':
                    $corner_image = imagerotate($corner_image, 180, 0);
                    imagecopymerge($gdimage, $corner_image, $w - $r, $h - $r, 0, 0, $r, $r, 100);
                    break;

                case 'bl':
                    $corner_image = imagerotate($corner_image, 90, 0);
                    imagecopymerge($gdimage, $corner_image, 0, $h - $r, 0, 0, $r, $r, 100);
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
