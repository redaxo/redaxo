<?php

/**
 * minibar element base class which provides lazy loading abilities for resource extensive use-cases.
 *
 * @package redaxo\core\minibar
 */
abstract class rex_minibar_lazy_element extends rex_minibar_element
{
    public function render()
    {
        if (self::isFirstView()) {
            return $this->renderFirstView();
        }
        return $this->renderComplete();
    }

    public static function isFirstView()
    {
        $apiFn = rex_api_function::factory();
        return !($apiFn instanceof rex_api_minibar);
    }

    /**
     * Returns the initial/light-weight html representation of this element.
     *
     * @return string
     */
    abstract protected function renderFirstView();

    /**
     * Returns the full html for this element.
     * This method will be called asynchronously after user starts interacting with the initial element.
     *
     * @return string
     */
    abstract protected function renderComplete();
}
