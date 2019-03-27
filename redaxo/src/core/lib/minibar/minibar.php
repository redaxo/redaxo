<?php

/**
 * @package redaxo\core\minibar
 */
class rex_minibar
{
    use rex_singleton_trait;

    /**
     * @var bool|null
     */
    private $isActive = null;

    /* @var rex_minibar_element[] */
    private $elements = [];

    public function addElement(rex_minibar_element $instance)
    {
        $this->elements[] = $instance;
    }

    /**
     * @param string $className
     *
     * @return rex_minibar_element|null
     */
    public function elementByClass($className)
    {
        foreach ($this->elements as $element) {
            if (get_class($element) === $className) {
                return $element;
            }
        }
    }

    public function get()
    {
        if (!self::shouldRender()) {
            return null;
        }

        if (!count($this->elements)) {
            return null;
        }

        $fragment = new rex_fragment([
            'elements' => $this->elements,
        ]);

        if (rex::isBackend()) {
            return $fragment->parse('core/minibar/minibar_backend.php');
        }

        return $fragment->parse('core/minibar/minibar_frontend.php');
    }

    /**
     * Returns if the minibar should be rendered.
     *
     * @return bool
     */
    public function shouldRender()
    {
        if (is_bool($this->isActive)) {
            return $this->isActive;
        }

        $user = rex_backend_login::createUser();
        if (!$user) {
            return false;
        }

        if (rex::isBackend()) {
            return true;
        }

        return $user->getValue('minibar') == 1;
    }

    /**
     * Returns if the minibar is visible.
     *
     * @return bool
     */
    public function isVisible()
    {
        return !rex_cookie('rex_minibar_frontend_hidden', 'bool', false);
    }

    /**
     * Sets the visibility.
     *
     * @param bool $value
     */
    public function setVisibility($value)
    {
        if ($value) {
            rex_response::sendCookie('rex_minibar_frontend_hidden', '');
        } else {
            rex_response::sendCookie('rex_minibar_frontend_hidden', '1', ['expires' => time() + rex::getProperty('session_duration'), 'samesite' => 'strict']);
        }
    }

    /**
     * @param bool $isActive
     */
    public function setActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return bool|null
     */
    public function isActive()
    {
        return $this->isActive;
    }
}
