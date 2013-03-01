<?php

/**
 * @package redaxo\media-manager
 */
abstract class rex_effect_abstract
{
    /**
     * @var rex_managed_media
     */
    public $media;

    public $params = array(); // effekt parameter

    public function setMedia(rex_managed_media $media)
    {
        $this->media = $media;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    abstract public function execute();

    public function getParams()
    {
        // NOOP
    }
}
