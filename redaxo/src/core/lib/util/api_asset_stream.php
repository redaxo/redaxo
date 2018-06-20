<?php

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_api_asset_stream extends rex_api_function
{
    public function __construct()
    {
        parent::__construct();

        $this->published = true;
    }

    public function execute()
    {
        // strip leading "../" of the backend generated path, to make it frontend compatible
        $assetFile = ltrim(rex_get('asset'), './');

        $fullPath = realpath($assetFile);
        $assetDir = rex_path::assets();

        if (strpos($fullPath, $assetDir) !== 0) {
            throw new Exception('Assets can only be streamed from within the assets folder');
        }

        if (rex_get('buster')) {
            // assets which contain are passed with a cachebuster will be cached very long,
            // as we assume their url will change when the underlying content changes
            rex_response::setHeader('Cache-Control', 'max-age=31536000, immutable');
        }

        $ext = rex_file::extension($assetFile);
        if ('js' === $ext) {
            rex_response::sendFile($assetFile, 'application/javascript');
        } elseif ('css' === $ext) {
            rex_response::sendFile($assetFile, 'text/css');
        } else {
            rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
            rex_response::sendContent("file not found");
        }

        exit();
    }
}
