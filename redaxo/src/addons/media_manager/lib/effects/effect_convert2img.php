<?php

/**
 * Benutzt den Konsolen convert Befehl.
 *
 * @author jan
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
    ],
    $convert_to = [
        'jpg' => [
            'ext' => 'jpg',
            'content-type' => 'image/jpeg',
            'createfunc' => 'imagecreatefromjpeg',
        ],
        'png' => [
            'ext' => 'png',
            'content-type' => 'image/png',
            'createfunc' => 'imagecreatefrompng',
        ],
    ],
    $densities = [100, 150, 200, 300, 600],
    $density_default = 150,
    $convert_tos = ['jpg', 'png'],
    $convert_to_default = 'jpg'
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

        $from_path = realpath($this->media->getMediapath());
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

        $to_path = rex_path::addonCache('media_manager', 'media_manager__convert2img_' . md5($this->media->getMediapath()) . '_' . $filename_wo_ext . $convert_to['ext']);

        $cmd = $convert_path . ' -density '.$density.' "' . $from_path . '[0]" -colorspace RGB "' . $to_path . '"';

        exec($cmd, $out, $ret);

        if ($ret != 0) {
            return false;
        }

        $image_src = call_user_func($convert_to['createfunc'], $to_path);

        if (!$image_src) {
            return;
        }

        $this->media->setImage($image_src);
        $this->media->refreshImageDimensions();
        $this->media->setFormat($convert_to['ext']);
        $this->media->setMediaFilename($filename);
        $this->media->setHeader('Content-Type', $convert_to['content-type']);
        unlink($to_path);
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
            $cmd = 'which convert';
            exec($cmd, $out, $ret);

            if (isset($ret) && $ret !== null) {
                switch ($ret) {
                    case 0:
                        $path = $out[0];
                        break;
                    case 1:
                        $path = '';
                        break;
                    default:
                }
            }
        }
        return $path;
    }
}
