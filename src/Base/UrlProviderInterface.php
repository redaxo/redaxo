<?php

namespace Redaxo\Core\Base;

/**
 * Generic interface for classes which provide urls.
 */
interface UrlProviderInterface
{
    /**
     * Returns a Url which contains the given parameters.
     *
     * @param array $params A scalar array containing key value pairs for the parameter and its value
     * @return string The generated Url
     */
    public function getUrl(array $params = []);
}
