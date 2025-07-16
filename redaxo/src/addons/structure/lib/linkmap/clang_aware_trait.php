<?php

/**
 * @package redaxo\structure
 *
 * @internal
 */
trait rex_linkmap_clang_aware_trait
{
    /**
     * Get the language ID for this renderer
     * 
     * @return int
     */
    protected function getClangId(): int
    {
        return rex_clang::getStartId();
    }
}

/**
 * @package redaxo\structure
 *
 * @internal
 */
trait rex_linkmap_context_aware_trait
{
    private rex_context $context;

    /**
     * @return rex_context
     */
    public function getContext(): rex_context
    {
        return $this->context;
    }

    /**
     * Get the language ID from context
     * 
     * @return int
     */
    protected function getClangId(): int
    {
        return (int) $this->context->getParam('clang', rex_clang::getStartId());
    }
}
