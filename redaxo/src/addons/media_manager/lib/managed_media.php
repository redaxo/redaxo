<?php

/**
 * @package redaxo\media-manager
 */
class rex_managed_media
{
    public const PROP_JPG_QUALITY = 'jpg_quality';
    public const PROP_PNG_COMPRESSION = 'png_compression';
    public const PROP_WEBP_QUALITY = 'webp_quality';
    public const PROP_INTERLACE = 'interlace';

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
        $this->setMediaPath($media_path);
        $this->format = strtolower(rex_file::extension($this->getMediaPath()));
    }

    /**
     * Returns the original path of the media.
     *
     * To get the current source path (can be changed by effects) use `getSourcePath` instead.
     *
     * @return null|string
     */
    public function getMediaPath()
    {
        return $this->media_path;
    }

    public function setMediaPath($media_path)
    {
        $this->media_path = $media_path;

        if (null === $media_path) {
            return;
        }

        $this->media = basename($media_path);
        $this->asImage = false;

        $this->sourcePath = $media_path;
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

        if (!$this->sourcePath || !file_exists($this->sourcePath)) {
            throw new rex_media_manager_not_found_exception(sprintf('Source path "%s" does not exist.', $this->sourcePath));
        }

        $this->image = [];
        $this->image['src'] = false;

        $format = $this->format;

        // if mimetype detected and in imagemap -> change format
        if ($ftype = rex_file::mimeType($this->getSourcePath())) {
            if (array_key_exists($ftype, $this->mimetypeMap)) {
                $format = $this->mimetypeMap[$ftype];
            }
        }

        if ('jpg' == $format || 'jpeg' == $format) {
            $format = 'jpeg';
            $this->image['src'] = @imagecreatefromjpeg($this->getSourcePath());
        } elseif ('gif' == $format) {
            $this->image['src'] = @imagecreatefromgif($this->getSourcePath());
        } elseif ('wbmp' == $format) {
            $this->image['src'] = @imagecreatefromwbmp($this->getSourcePath());
        } elseif ('webp' == $format) {
            if (function_exists('imagecreatefromwebp')) {
                $this->image['src'] = @imagecreatefromwebp($this->getSourcePath());
                imagealphablending($this->image['src'], false);
                imagesavealpha($this->image['src'], true);
            }
        } else {
            $this->image['src'] = @imagecreatefrompng($this->getSourcePath());
            if ($this->image['src']) {
                imagealphablending($this->image['src'], false);
                imagesavealpha($this->image['src'], true);
                $format = 'png';
            }
        }

        if (!$this->image['src']) {
            throw new rex_media_manager_not_found_exception(sprintf('Source path "%s" could not be converted to gd resource.', $this->sourcePath));
        }

        $this->asImage = true;
        $this->format = $format;

        $this->fixOrientation();
        $this->refreshImageDimensions();
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

        $size = @getimagesize($this->sourcePath);
        $this->image['width'] = $size[0] ?? null;
        $this->image['height'] = $size[1] ?? null;
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
        $this->prepareHeaders();

        if ($this->asImage) {
            $src = $this->getSource();
            $this->setHeader('Content-Length', rex_string::size($src));

            rex_response::cleanOutputBuffers();
            foreach ($this->header as $t => $c) {
                header($t . ': ' . $c);
            }

            echo $src;

            if ($save) {
                rex_file::putCache($headerCacheFilename, [
                    'media_path' => $this->getMediaPath(),
                    'format' => $this->format,
                    'headers' => $this->header,
                ]);

                rex_file::put($sourceCacheFilename, $src);
            }
        } else {
            $this->setHeader('Content-Length', filesize($this->getSourcePath()));

            rex_response::cleanOutputBuffers();
            foreach ($this->header as $t => $c) {
                rex_response::setHeader($t, $c);
            }

            rex_response::sendFile($this->getSourcePath(), $this->header['Content-Type']);

            if ($save) {
                rex_file::putCache($headerCacheFilename, [
                    'media_path' => $this->getMediaPath(),
                    'format' => $this->format,
                    'headers' => $this->header,
                ]);

                rex_file::copy($this->getSourcePath(), $sourceCacheFilename);
            }
        }
    }

    public function save($sourceCacheFilename, $headerCacheFilename)
    {
        $src = $this->getSource();

        $this->prepareHeaders($src);
        $this->saveFiles($src, $sourceCacheFilename, $headerCacheFilename);
    }

    public function exists(): bool
    {
        return $this->asImage || file_exists($this->sourcePath);
    }

    /**
     * @return string
     */
    protected function getImageSource()
    {
        $addon = rex_addon::get('media_manager');

        $format = $this->format;
        $format = 'jpeg' === $format ? 'jpg' : $format;

        $interlace = $this->getImageProperty(self::PROP_INTERLACE, $addon->getConfig('interlace'));
        imageinterlace($this->image['src'], in_array($format, $interlace) ? 1 : 0);

        ob_start();
        if ('jpg' == $format) {
            $quality = $this->getImageProperty(self::PROP_JPG_QUALITY, $addon->getConfig('jpg_quality'));
            imagejpeg($this->image['src'], null, $quality);
        } elseif ('png' == $format) {
            $compression = $this->getImageProperty(self::PROP_PNG_COMPRESSION, $addon->getConfig('png_compression'));
            imagepng($this->image['src'], null, $compression);
        } elseif ('gif' == $format) {
            imagegif($this->image['src']);
        } elseif ('wbmp' == $format) {
            imagewbmp($this->image['src']);
        } elseif ('webp' == $format) {
            $quality = $this->getImageProperty(self::PROP_WEBP_QUALITY, $addon->getConfig('webp_quality'));
            imagewebp($this->image['src'], null, $quality);
        }
        return ob_get_clean();
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
     * To get the original media path use `getMediaPath()` instead.
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

    public function setImageProperty($name, $value)
    {
        $this->image[$name] = $value;
    }

    public function getImageProperty($name, $default = null)
    {
        return isset($this->image[$name]) ? $this->image[$name] : $default;
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

    private function fixOrientation()
    {
        if (!function_exists('exif_read_data')) {
            return;
        }
        // exif_read_data() only works on jpg/jpeg/tiff
        if (!in_array($this->getFormat(), ['jpg', 'jpeg', 'tiff'])) {
            return;
        }
        // suppress warning in case of corrupt/ missing exif data
        $exif = @exif_read_data($this->getSourcePath());

        if (!isset($exif['Orientation']) || !in_array($exif['Orientation'], [3, 6, 8])) {
            return;
        }

        switch ($exif['Orientation']) {
            case 8:
                $this->image['src'] = imagerotate($this->image['src'], 90, 0);
                break;
            case 3:
                $this->image['src'] = imagerotate($this->image['src'], 180, 0);
                break;
            case 6:
                $this->image['src'] = imagerotate($this->image['src'], -90, 0);
                break;
        }
    }

    /**
     * @param string $src Source content
     */
    private function prepareHeaders($src = null)
    {
        if (null !== $src) {
            $this->setHeader('Content-Length', rex_string::size($src));
        }

        $header = $this->getHeader();
        if (!isset($header['Content-Type']) && $this->sourcePath) {
            $content_type = rex_file::mimeType($this->sourcePath);

            if ($content_type) {
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
            'media_path' => $this->getMediaPath(),
            'format' => $this->format,
            'headers' => $this->header,
        ]);

        rex_file::put($sourceCacheFilename, $src);
    }
}
