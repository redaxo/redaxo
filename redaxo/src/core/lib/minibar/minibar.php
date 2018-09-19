<?php

/**
 * @package redaxo\core
 */
class rex_minibar
{
    use rex_singleton_trait;

    /* @var rex_minibar_element[] */
    private $elements = [];

    public function addElement(rex_minibar_element $instance)
    {
        $this->elements[] = $instance;
    }

    public function get()
    {
        if (!self::isActive()) {
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
     * Returns if the minibar is active.
     *
     * @return bool
     */
    public function isActive()
    {
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
}
