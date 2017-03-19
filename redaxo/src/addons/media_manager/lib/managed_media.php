<?php

/**
 * @package redaxo\media-manager
 */
class rex_managed_media
{
    private $media_path = '';
    private $media;
    private $asImage = false;
    private $image;
    private $header = [];
    private $sourcePath;
    private $format;

    private $mimetypeMap = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/vnd.wap.wbmp' => 'wbmp',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function __construct($media_path)
    {
        $this->setMediapath($media_path);
        $this->sourcePath = $media_path;
        $this->format = strtolower(rex_file::extension($this->getMediapath()));
    }

    /**
     * Returns the original path of the media.
     *
     * To get the current source path (can be changed by effects) use `getSourcePath` instead.
     *
     * @return string
     */
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

    public function setMediaFilename($filename)
    {
        $this->media = $filename;
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

        $this->image = [];
        $this->image['src'] = false;

        // if mimetype detected and in imagemap -> change format
        if (class_exists('finfo') && $finfo = new finfo(FILEINFO_MIME_TYPE)) {
            if ($ftype = @$finfo->file($this->getSourcePath())) {
                if (array_key_exists($ftype, $this->mimetypeMap)) {
                    $this->format = $this->mimetypeMap[$ftype];
                }
            }
        }

        if ($this->format == 'jpg' || $this->format == 'jpeg') {
            $this->format = 'jpeg';
            $this->image['src'] = @imagecreatefromjpeg($this->getSourcePath());
        } elseif ($this->format == 'gif') {
            $this->image['src'] = @imagecreatefromgif($this->getSourcePath());
        } elseif ($this->format == 'wbmp') {
            $this->image['src'] = @imagecreatefromwbmp($this->getSourcePath());
        } elseif ($this->format == 'webp') {
            $this->image['src'] = @imagecreatefromwebp($this->getSourcePath());
        } else {
            $this->image['src'] = @imagecreatefrompng($this->getSourcePath());
            if ($this->image['src']) {
                imagealphablending($this->image['src'], false);
                imagesavealpha($this->image['src'], true);
                $this->format = 'png';
            }
        }

        if (!$this->image['src']) {
            $this->setSourcePath(rex_path::addon('media_manager', 'media/warning.jpg'));
            $this->asImage();
        } else {
            $this->refreshImageDimensions();
        }
    }

    public function refreshImageDimensions()
    {
        if ($this->asImage) {
            $this->image['width'] = imagesx($this->image['src']);
            $this->image['height'] = imagesy($this->image['src']);

            return;
        }

        if ('jpeg' !== $this->format && !in_array($this->format, $this->mimetypeMap)) {
            return;
        }

        $size = getimagesize($this->sourcePath);
        $this->image['width'] = $size[0];
        $this->image['height'] = $size[1];
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function sendMedia($sourceCacheFilename, $headerCacheFilename, $save = false)
    {
        $src = $this->getSource();

        $this->prepareHeaders($src);

        rex_response::cleanOutputBuffers();
        foreach ($this->header as $t => $c) {
            header($t . ': ' . $c);
        }
        echo $src;

        if ($save) {
            $this->saveFiles($src, $sourceCacheFilename, $headerCacheFilename);
        }
    }

    public function save($sourceCacheFilename, $headerCacheFilename)
    {
        $src = $this->getSource();

        $this->prepareHeaders($src);
        $this->saveFiles($src, $sourceCacheFilename, $headerCacheFilename);
    }

    protected function getImageSource()
    {
        ob_start();
        if ($this->format == 'jpg' || $this->format == 'jpeg') {
            $this->image['quality'] = rex_config::get('media_manager', 'jpg_quality', 85);
            imagejpeg($this->image['src'], null, $this->image['quality']);
        } elseif ($this->format == 'png') {
            imagepng($this->image['src']);
        } elseif ($this->format == 'gif') {
            imagegif($this->image['src']);
        } elseif ($this->format == 'wbmp') {
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
        $this->asImage = true;
    }

    public function setSourcePath($path)
    {
        $this->sourcePath = $path;

        $this->asImage = false;

        if (isset($this->image['src']) && is_resource($this->image['src'])) {
            imagedestroy($this->image['src']);
        }
    }

    /**
     * Returns the current source path.
     *
     * To get the original media path use `getMediapath()` instead.
     *
     * @return string
     */
    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        if ($this->asImage) {
            return $this->getImageSource();
        }

        return rex_file::get($this->sourcePath);
    }

    public function getWidth()
    {
        return $this->image['width'];
    }

    public function getHeight()
    {
        return $this->image['height'];
    }

    /**
     * @deprecated since 2.3.0, use `getWidth()` instead
     */
    public function getImageWidth()
    {
        return $this->getWidth();
    }

    /**
     * @deprecated since 2.3.0, use `getHeight()` instead
     */
    public function getImageHeight()
    {
        return $this->getHeight();
    }

    /**
     * @param string $src Source content
     */
    private function prepareHeaders($src)
    {
        $this->setHeader('Content-Length', rex_string::size($src));

        $header = $this->getHeader();
        if (!isset($header['Content-Type'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $content_type = finfo_file($finfo, $this->getSourcePath());
            if ($content_type != '') {
                $this->setHeader('Content-Type', $content_type);
            }
        }
        if (!isset($header['Content-Disposition'])) {
            $this->setHeader('Content-Disposition', 'inline; filename="' . $this->getMediaFilename() . '";');
        }
        if (!isset($header['Last-Modified'])) {
            $this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s T'));
        }
    }

    /**
     * @param string $src                 Source content
     * @param string $sourceCacheFilename
     * @param string $headerCacheFilename
     */
    private function saveFiles($src, $sourceCacheFilename, $headerCacheFilename)
    {
        rex_file::putCache($headerCacheFilename, [
            'format' => $this->format,
            'headers' => $this->header,
        ]);

        rex_file::put($sourceCacheFilename, $src);
    }
}
