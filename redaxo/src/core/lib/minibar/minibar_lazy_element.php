<?php

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

    abstract protected function renderFirstView();

    abstract protected function renderComplete();
}
