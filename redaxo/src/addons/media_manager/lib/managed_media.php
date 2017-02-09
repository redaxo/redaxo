<?php

/**
 * @package redaxo\media-manager
 */
class rex_managed_media
{
    private $media_path = '';
    private $media;
    private $isImage = false;
    private $image;
    private $header = [];

    private $mimetypeMap = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/vnd.wap.wbmp' => 'wbmp',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

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
        $this->isImage = false;
    }

    public function getMediaFilename()
    {
        return $this->media;
    }

    public function setMediaFilename($filename)
    {
        $this->media = $filename;
    }

    public function setHeaders($headers)
    {
        $this->header = $headers;
    }

    public function setHeader($type, $content)
    {
        $this->header[$type] = $content;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function isImage()
    {
        if (array_key_exists($this->header['Content-Type'], $this->mimetypeMap)) {
            $this->isImage = false;
            $this->asImage();
        }
    }


    public function asImage()
    {
        if ($this->isImage) {
            return;
        }
        $this->isImage = true;

        $this->image = [];
        $this->image['format'] = strtolower(rex_file::extension($this->getMediapath()));
        $this->image['src'] = false;

        // if mimetype detected and in imagemap -> change format
        if (class_exists('finfo') && $finfo = new finfo(FILEINFO_MIME_TYPE)) {
            if ($ftype = @$finfo->file($this->image['filepath'])) {
                if (array_key_exists($ftype, $this->mimetypeMap)) {
                    $this->image['format'] = $this->mimetypeMap[$ftype];
                }
            }
        }

        if ($this->image['format'] == 'jpg' || $this->image['format'] == 'jpeg') {
            $this->image['format'] = 'jpeg';
            $this->image['src'] = @imagecreatefromjpeg($this->getMediapath());

        } elseif ($this->image['format'] == 'gif') {
            $this->image['src'] = @imagecreatefromgif($this->getMediapath());

        } elseif ($this->image['format'] == 'wbmp') {
            $this->image['src'] = @imagecreatefromwbmp($this->getMediapath());

        } else {
            $this->image['src'] = @imagecreatefrompng($this->getMediapath());
            if ($this->image['src']) {
                imagealphablending($this->image['src'], false);
                imagesavealpha($this->image['src'], true);
                $this->image['format'] = 'png';
            }
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
        foreach ($this->mimetypeMap as $contentType => $ext) {
            if ($ext == $format) {
                $this->setHeader('Content-Type', $contentType);
                if ($format != $this->image['format']) {
                    $this->setHeader('Fileextension', $format);
                }
            }
        }

        $this->image['format'] = $format;
    }

    public function getSource()
    {
        if ($this->isImage) {
            return $this->getImageSource();
        }
        return rex_file::get($this->getMediapath());

    }

    public function getImageSource()
    {
        ob_start();
        if ($this->image['format'] == 'jpg' || $this->image['format'] == 'jpeg') {
            $this->image['quality'] = rex_config::get('media_manager', 'jpg_quality', 80);
            imagejpeg($this->image['src'], null, $this->image['quality']);
        } elseif ($this->image['format'] == 'png') {
            imagepng($this->image['src']);
        } elseif ($this->image['format'] == 'gif') {
            imagegif($this->image['src']);
        } elseif ($this->image['format'] == 'wbmp') {
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
        $this->isImage = true;
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