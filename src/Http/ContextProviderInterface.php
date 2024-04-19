<?php

namespace Redaxo\Core\Http;

use Redaxo\Core\Base\UrlProviderInterface;

/**
 * Generic interface for classes which provide a complete Context.
 * A Context consists of a set of parameters which may get passed using urls (via parameter) or forms (via hidden input fields).
 */
interface ContextProviderInterface extends UrlProviderInterface
{
    /**
     * Returns a html string containing hidden input fields for the given parameters.
     *
     * @param array $params A array containing key value pairs for the parameter and its value
     *
     * @return string The generated html source containing the hidden input fields
     */
    public function getHiddenInputFields(array $params = []);
}
