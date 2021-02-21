<?php

/**
 * Benutzt den Konsolen convert Befehl.
 *
 * @author jan
 *
 * @package redaxo\media-manager
 */

class rex_effect_convert2img extends rex_effect_abstract
{
    private static $convert_types = [
        'pdf',
        'ps',
        'psd',
        'tif',
        'tiff',
        'bmp',
        'eps',
        'ico',
        'svg',
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
    ];
    private static $densities = [100, 150, 200, 300, 600];
    private static $density_default = 150;
    private static $convert_tos = ['jpg', 'png'];
    private static $convert_to_default = 'jpg';

    public function execute()
    {
        if (!isset(self::$convert_to[$this->params['convert_to']])) {
            $convertTo = self::$convert_to[self::$convert_to_default];
        } else {
            $convertTo = self::$convert_to[$this->params['convert_to']];
        }

        $density = (int) $this->params['density'];

        $color = $this->params['color'] ?? '';

        if (!in_array($density, self::$densities)) {
            $density = self::$density_default;
        }

        $fromPath = realpath($this->media->getMediaPath());
        $ext = rex_file::extension($fromPath);

        if (!$ext) {
            return;
        }

        if (!in_array(strtolower($ext), self::$convert_types)) {
            return;
        }

        if (class_exists(Imagick::class)) {
            $imagick = new Imagick();
            $imagick->setResolution($density, $density);
            $imagick->readImage($fromPath.'[0]');

            if ('' != $color) {
                $imagick->setImageBackgroundColor($color);
                $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            }

            $imagick->transformImageColorspace(Imagick::COLORSPACE_RGB);
            $imagick->setImageFormat($convertTo['ext']);

            $gd = imagecreatefromstring($imagick->getImageBlob());

            $this->media->setImage($gd);
            $this->media->setFormat($convertTo['ext']);
            $this->media->setHeader('Content-Type', $convertTo['content-type']);
            $this->media->refreshImageDimensions();

            return;
        }

        $convertPath = self::getConvertPath();

        if ('' == $convertPath) {
            return;
        }

        $filename = $this->media->getMediaFilename();
        $filenameWoExt = substr($filename, 0, (strlen($filename) - strlen($ext)));

        $toPath = rex_path::addonCache('media_manager', 'media_manager__convert2img_' . md5($this->media->getMediaPath()) . '_' . $filenameWoExt . $convertTo['ext']);

        $addColor = ('' != $color) ? ' -background "' . $color  . '" -flatten' : '';

        $cmd = $convertPath . ' -density '.$density.' "' . $fromPath . '[0]"  ' . $addColor . ' -colorspace RGB "' . $toPath . '"';
        exec($cmd, $out, $ret);

        if (0 != $ret) {
            if ($error = error_get_last()) {
                throw new rex_exception('Unable to exec command '. $cmd .': '.$error['message']);
            }
            throw new rex_exception('Unable to exec command '. $cmd);
        }

        $this->media->setSourcePath($toPath);
        $this->media->refreshImageDimensions();
        $this->media->setFormat($convertTo['ext']);
        $this->media->setMediaFilename($filename);
        $this->media->setHeader('Content-Type', $convertTo['content-type']);

        register_shutdown_function(static function () use ($toPath) {
            rex_file::delete($toPath);
        });
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_convert2img');
    }

    public function getParams()
    {
        $imNotfound = '';
        if (!class_exists(Imagick::class) && '' == self::getConvertPath()) {
            $imNotfound = '<strong>'.rex_i18n::msg('media_manager_effect_convert2img_noimagemagick').'</strong>';
        }
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_convert2img_convertto'),
                'name' => 'convert_to',
                'type' => 'select',
                'options' => self::$convert_tos,
                'default' => self::$convert_to_default,
                'prefix' => $imNotfound,
                'notice' => rex_i18n::msg('media_manager_effect_convert2img_convertto_notice'),
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_convert2img_density'),
                'name' => 'density',
                'type' => 'select',
                'options' => self::$densities,
                'default' => self::$density_default,
                'notice' => rex_i18n::msg('media_manager_effect_convert2img_density_notice'),
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_convert2img_color'),
                'name' => 'color',
                'type' => 'int',
                'notice' => rex_i18n::msg('media_manager_effect_convert2img_color_notice'),
            ],
        ];
    }

    private function getConvertPath()
    {
        $path = '';

        if (function_exists('exec')) {
            $out = [];
            $cmd = 'command -v convert || which convert';
            exec($cmd, $out, $ret);

            if (0 === $ret) {
                $path = $out[0];
            }
        }
        return $path;
    }
}
