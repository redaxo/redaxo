<?php

/**
 * @package redaxo\core
 */
class rex_be_navigation
{
    use rex_factory_trait;

    private $headlines = [];
    private $pages = [];

    /**
     * @return rex_be_navigation
     */
    public static function factory()
    {
        $class = self::getFactoryClass();
        return new $class;
    }

    /**
     * @param rex_be_page $page
     */
    public function addPage(rex_be_page $page)
    {
        $blockName = 'default';
        if ($page instanceof rex_be_page_main) {
            $blockName = $page->getBlock();
        }

        if (!isset($this->pages[$blockName])) {
            $this->pages[$blockName] = [];
        }

        $this->pages[$blockName][] = $page;
    }

    /**
     * @return array
     */
    public function getNavigation()
    {
        $this->setActiveElements();
        $return = [];
        foreach ($this->pages as $block => $blockPages) {
            if (is_array($blockPages) && count($blockPages) > 0 && $blockPages[0] instanceof rex_be_page_main) {
                uasort($blockPages, function (rex_be_page_main $a, rex_be_page_main $b) {
                    $a_prio = (int) $a->getPrio();
                    $b_prio = (int) $b->getPrio();
                    if ($a_prio == $b_prio || ($a_prio <= 0 && $b_prio <= 0)) {
                        return strcmp($a->getTitle(), $b->getTitle());
                    }

                    if ($a_prio <= 0) {
                        return 1;
                    }

                    if ($b_prio <= 0) {
                        return -1;
                    }

                    return $a_prio > $b_prio ? 1 : -1;
                });
            }

            $n = $this->_getNavigation($blockPages);
            if (count($n) > 0) {
                $fragment = new rex_fragment();
                $fragment->setVar('navigation', $n, false);

                $return[] = [
                    'navigation' => $n,
                    'headline' => ['title' => $this->getHeadline($block)]
                ];
            }
        }
        return $return;

    }

    /**
     * @param rex_be_page[] $blockPages
     * @return array
     */
    private function _getNavigation(array $blockPages)
    {
        $navigation = [];

        foreach ($blockPages as $page) {
            if ($page->isHidden() || !$page->checkPermission(rex::getUser())) {
                continue;
            }
            $n = [];
            $n['linkClasses'] = [];
            $n['itemClasses'] = [];
            $n['linkAttr'] = [];
            $n['itemAttr'] = [];

            $n['itemClasses'][] = $page->getItemAttr('class');
            $n['linkClasses'][] = $page->getItemAttr('class');

            foreach ($page->getItemAttr(null) as $name => $value) {
                $n['itemAttr'][$name] = trim($value);
            }

            foreach ($page->getLinkAttr(null) as $name => $value) {
                $n['linkAttr'][$name] = trim($value);
            }

            $n['href']   = str_replace('&', '&amp;', $page->getHref());
            $n['title']  = $page->getTitle();
            $n['active'] = $page->isActive();

            $subpages = $page->getSubpages();
            if (is_array($subpages) && !empty($subpages)) {
                $n['children'] = $this->_getNavigation($subpages);
            }

            $navigation[] = $n;
        }

        return $navigation;
    }

    protected function setActiveElements()
    {
        foreach ($this->pages as $blockPages) {
            foreach ($blockPages as $page) {
                // check main pages
                if ($page->isActive()) {
                    $page->addItemClass('rex-active');

                    // check for subpages
                    foreach ($page->getSubpages() as $subpage) {
                        if ($subpage->isActive()) {
                            $subpage->addItemClass('rex-active');
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $block
     * @param string $headline
     */
    public function setHeadline($block, $headline)
    {
        $this->headlines[$block] = $headline;
    }

    /**
     * @param string $block
     * @return string
     */
    public function getHeadline($block)
    {
        if (isset($this->headlines[$block])) {
            return $this->headlines[$block];
        }

        if ($block != 'default') {
            return rex_i18n::msg('navigation_' . $block);
        }

        return '';
    }
}
