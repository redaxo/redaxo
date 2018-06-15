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
        // 'svg'
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
    private static $convert_to_default = 'jpg'
    ;

    public function execute()
    {
        // error_reporting(E_ALL);ini_set("display_errors",1);

        if (!isset(self::$convert_to[$this->params['convert_to']])) {
            $convert_to = self::$convert_to[self::$convert_to_default];
        } else {
            $convert_to = self::$convert_to[$this->params['convert_to']];
        }

        $density = (int) $this->params['density'];

        if (!in_array($density, self::$densities)) {
            $density = self::$density_default;
        }

        $from_path = realpath($this->media->getMediaPath());
        $ext = rex_file::extension($from_path);

        if (!$ext) {
            return;
        }

        if (!in_array(strtolower($ext), self::$convert_types)) {
            return;
        }

        $convert_path = self::getConvertPath();

        if ($convert_path == '') {
            return;
        }

        $filename = $this->media->getMediaFilename();
        $filename_wo_ext = substr($filename, 0, (strlen($filename) - strlen($ext)));

        $to_path = rex_path::addonCache('media_manager', 'media_manager__convert2img_' . md5($this->media->getMediaPath()) . '_' . $filename_wo_ext . $convert_to['ext']);

        $cmd = $convert_path . ' -density '.$density.' "' . $from_path . '[0]" -colorspace RGB "' . $to_path . '"';

        exec($cmd, $out, $ret);

        if ($ret != 0) {
            return false;
        }

        $this->media->setSourcePath($to_path);
        $this->media->refreshImageDimensions();
        $this->media->setFormat($convert_to['ext']);
        $this->media->setMediaFilename($filename);
        $this->media->setHeader('Content-Type', $convert_to['content-type']);

        register_shutdown_function(function () use ($to_path) {
            rex_file::delete($to_path);
        });
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_convert2img');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_convert2img_convertto'),
                'name' => 'convert_to',
                'type' => 'select',
                'options' => self::$convert_tos,
                'default' => self::$convert_to_default,
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_convert2img_density'),
                'name' => 'density',
                'type' => 'select',
                'options' => self::$densities,
                'default' => self::$density_default,
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

            if ($ret === 0) {
                $path = $out[0];
            }
        }
        return $path;
    }
}
