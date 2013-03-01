<?php

/**
 * @package redaxo\media-manager
 */
class rex_managed_media
{
    private
        $media_path = '',
        $media,
        $asImage = false,
        $image,
        $header = array();

    public function __construct($media_path)
    {
        $this->setMediapath($media_path);
    }

    public function getMediapath()
    {
        return $this->media_path;
    }

    public function setMediapath($media_path)
    {
        if (!file_exists($media_path)) {
            $media_path = rex_path::addon('media_manager', 'media/warning.jpg');
        }
        $this->media_path = $media_path;
        $this->media = basename($media_path);
        $this->asImage = false;
    }

    public function getMediaFilename()
    {
        return $this->media;
    }

    public function setHeader($type, $content)
    {
        $this->header[$type] = $content;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function asImage()
    {

        if ($this->asImage) {
            return;
        }

        $this->asImage = true;

        $this->image = array();
        $this->image['format'] = strtoupper(rex_file::extension($this->getMediapath()));
        $this->image['src'] = false;

        if ($this->image['format'] == 'JPG' || $this->image['format'] == 'JPEG') {
            $this->image['format'] = 'JPEG';
            $this->image['quality'] = rex_config::get('media_manager', 'jpg_quality', 80);
            $this->image['src'] = @imagecreatefromjpeg($this->getMediapath());

        } elseif ($this->image['format'] == 'PNG') {
            $this->image['src'] = @imagecreatefrompng($this->getMediapath());

        } elseif ($this->image['format'] == 'GIF') {
            $this->image['src'] = @imagecreatefromgif($this->getMediapath());

        } elseif ($this->image['format'] == 'WBMP') {
            $this->image['src'] = @imagecreatefromwbmp($this->getMediapath());

        } else {
            $this->image['src'] = @imagecreatefrompng($this->getMediapath());
            $this->image['format'] = 'PNG';
        }

        if (!$this->image['src']) {
            $this->setMediapath(rex_path::addon('media_manager', 'media/warning.jpg'));
            $this->asImage();

        } else {
            $this->refreshImageDimensions();

        }

    }

    public function refreshImageDimensions()
    {
        $this->image['width'] = imagesx($this->image['src']);
        $this->image['height'] = imagesy($this->image['src']);
    }

    public function getFormat()
    {
        return $this->image['format'];
    }

    public function setFormat($format)
    {
        $this->image['format'] = $format;
    }

    public function getImageWidth()
    {
        return $this->image['format'];
    }

    public function getImageHeight()
    {
        return $this->image['height'];
    }

    public function sendMedia($sourceCacheFilename, $headerCacheFilename, $save = false)
    {
        if ($this->asImage) {
            $src = $this->getImageSource();
        } else {
            $src = rex_file::get($this->getMediapath());
        }

        $this->setHeader('Content-Length', rex_string::size($src));
        $header = $this->getHeader();
        if (!array_key_exists('Content-Type', $header)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $content_type = finfo_file($finfo, $this->getMediapath());
            if ($content_type != '') {
                $this->setHeader('Content-Type', $content_type);
            }
        }
        if (!array_key_exists('Content-Disposition', $header)) {
            $this->setHeader('Content-Disposition', "inline; filename=\"" . $this->getMediaFilename() . "\";");
        }
        if (!array_key_exists('Last-Modified', $header)) {
            $this->setHeader('Last-Modified', date('r'));
        }

        rex_response::cleanOutputBuffers();
        foreach ($this->header as $t => $c) {
            header($t . ': ' . $c);
        }
        echo $src;
        if ($save) {
            rex_file::putCache($headerCacheFilename, $this->header);
            rex_file::put($sourceCacheFilename, $src);
        }

    }

    protected function getImageSource()
    {
        ob_start();
        if ($this->image['format'] == 'JPG' || $this->image['format'] == 'JPEG') {
            imagejpeg($this->image['src'], null, $this->image['quality']);
        } elseif ($this->image['format'] == 'PNG') {
            imagepng($this->image['src']);
        } elseif ($this->image['format'] == 'GIF') {
            imagegif($this->image['src']);
        } elseif ($this->image['format'] == 'WBMP') {
            imagewbmp($this->image['src']);
        }
        $src = ob_get_contents();
        ob_end_clean();
        return $src;

    }

    public function getImage()
    {
        return $this->image['src'];
    }

    public function setImage($src)
    {
        $this->image['src'] = $src;
    }

    public function getWidth()
    {
        return $this->image['width'];
    }

    public function getHeight()
    {
        return $this->image['height'];
    }


}
