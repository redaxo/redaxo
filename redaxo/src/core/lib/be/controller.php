<?php

/**
 * @package redaxo\core
 */
class rex_be_controller
{
    /**
     * @var string
     */
    private static $page;

    /**
     * @var array
     */
    private static $pageParts = [];

    /**
     * @var rex_be_page
     */
    private static $pageObject;

    /**
     * @var rex_be_page[]
     */
    private static $pages = [];

    /**
     * @param string $page
     */
    public static function setCurrentPage($page)
    {
        self::$page = trim($page, '/ ');
        self::$pageParts = explode('/', self::$page);
        self::$pageObject = null;
    }

    /**
     * @return string
     */
    public static function getCurrentPage()
    {
        return self::$page;
    }

    /**
     * @param null|int    $part    Part index, beginning with 1. If $part is null, an array of all current parts will be returned
     * @param null|string $default Default value
     *
     * @return array|string|null
     */
    public static function getCurrentPagePart($part = null, $default = null)
    {
        if ($part === null) {
            return self::$pageParts;
        }
        --$part;
        return isset(self::$pageParts[$part]) ? self::$pageParts[$part] : $default;
    }

    /**
     * @return rex_be_page
     */
    public static function getCurrentPageObject()
    {
        if (!self::$pageObject) {
            self::$pageObject = self::getPageObject(self::getCurrentPage());
        }
        return self::$pageObject;
    }

    /**
     * @param string|array $page
     *
     * @return rex_be_page
     */
    public static function getPageObject($page)
    {
        if (!is_array($page)) {
            $page = explode('/', $page);
        }
        if (!isset($page[0]) || !isset(self::$pages[$page[0]])) {
            return null;
        }
        $obj = self::$pages[$page[0]];
        for ($i = 1, $count = count($page); $i < $count; ++$i) {
            if ($new = $obj->getSubpage($page[$i])) {
                $obj = $new;
            } else {
                return null;
            }
        }
        return $obj;
    }

    /**
     * @return rex_be_page[]
     */
    public static function getPages()
    {
        return self::$pages;
    }

    /**
     * @param rex_be_page[] $pages
     */
    public static function setPages(array $pages)
    {
        self::$pages = $pages;
    }

    public static function getPageTitle()
    {
        $parts = [];

        $activePageObj = self::getCurrentPageObject();
        if ($activePageObj->getTitle()) {
            $parts[] = $activePageObj->getTitle();
        }
        if (rex::getServerName()) {
            $parts[] = rex::getServerName();
        }
        $parts[] = 'REDAXO CMS';

        return implode(' · ', $parts);
    }

    public static function getSetupPage()
    {
        $page = new rex_be_page('setup', rex_i18n::msg('setup'));
        $page->setPath(rex_path::core('pages/setup.php'));
        return $page;
    }

    public static function getLoginPage()
    {
        $page = new rex_be_page('login', 'Login');
        $page->setPath(rex_path::core('pages/login.php'));
        $page->setHasNavigation(false);
        return $page;
    }

    public static function appendLoggedInPages()
    {
        self::$pages['profile'] = (new rex_be_page('profile', rex_i18n::msg('profile')))
            ->setPath(rex_path::core('pages/profile.php'))
            ->setPjax();

        self::$pages['credits'] = (new rex_be_page('credits', rex_i18n::msg('credits')))
            ->setPath(rex_path::core('pages/credits.php'));

        self::$pages['packages'] = (new rex_be_page_main('system', 'packages', rex_i18n::msg('addons')))
            ->setPath(rex_path::core('pages/packages.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(60)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-package-addon');

        $logsPage = (new rex_be_page('log', rex_i18n::msg('logfiles')))->setSubPath(rex_path::core('pages/system.log.php'));
        $logsPage->addSubpage((new rex_be_page('redaxo', rex_i18n::msg('syslog_redaxo')))->setSubPath(rex_path::core('pages/system.log.redaxo.php')));
        if (is_readable(ini_get('error_log'))) {
            $logsPage->addSubpage((new rex_be_page('php', rex_i18n::msg('syslog_phperrors')))->setSubPath(rex_path::core('pages/system.log.external.php')));
        }

        self::$pages['system'] = (new rex_be_page_main('system', 'system', rex_i18n::msg('system')))
            ->setPath(rex_path::core('pages/system.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(70)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-system')
            ->addSubpage((new rex_be_page('settings', rex_i18n::msg('main_preferences')))->setSubPath(rex_path::core('pages/system.settings.php')))
            ->addSubpage((new rex_be_page('lang', rex_i18n::msg('languages')))->setSubPath(rex_path::core('pages/system.clangs.php')))
            ->addSubpage($logsPage)
            ->addSubpage((new rex_be_page('report', rex_i18n::msg('system_report')))
                ->addSubpage((new rex_be_page('html', rex_i18n::msg('system_report')))->setSubPath(rex_path::core('pages/system.report.html.php')))
                ->addSubpage((new rex_be_page('markdown', rex_i18n::msg('system_report_markdown')))->setSubPath(rex_path::core('pages/system.report.markdown.php')))
            )
            ->addSubpage((new rex_be_page('phpinfo', 'phpinfo'))
                ->setHidden(true)
                ->setHasLayout(false)
                ->setPath(rex_path::core('pages/system.phpinfo.php'))
            );
    }

    public static function appendPackagePages()
    {
        $insertPages = [];
        $addons = rex::isSafeMode() ? rex_addon::getSetupAddons() : rex_addon::getAvailableAddons();
        foreach ($addons as $addon) {
            $mainPage = self::pageCreate($addon->getProperty('page'), $addon, true);

            if (is_array($pages = $addon->getProperty('pages'))) {
                foreach ($pages as $key => $page) {
                    if (strpos($key, '/') !== false) {
                        $insertPages[$key] = [$addon, $page];
                    } else {
                        self::pageCreate($page, $addon, false, $mainPage, $key, true);
                    }
                }
            }

            // handle plugins
            $plugins = rex::isSafeMode() ? $addon->getSystemPlugins() : $addon->getAvailablePlugins();
            foreach ($plugins as $plugin) {
                self::pageCreate($plugin->getProperty('page'), $plugin, false, $mainPage);

                if (is_array($pages = $plugin->getProperty('pages'))) {
                    foreach ($pages as $key => $page) {
                        if (strpos($key, '/') !== false) {
                            $insertPages[$key] = [$plugin, $page];
                        } else {
                            self::pageCreate($page, $plugin, false, $mainPage, $key, true);
                        }
                    }
                }
            }
        }
        foreach ($insertPages as $key => $packagePage) {
            list($package, $page) = $packagePage;
            $key = explode('/', $key);
            if (!isset(self::$pages[$key[0]])) {
                continue;
            }
            $parentPage = self::$pages[$key[0]];
            for ($i = 1, $count = count($key) - 1; $i < $count && $parentPage; ++$i) {
                $parentPage = $parentPage->getSubpage($key[$i]);
            }
            if ($parentPage) {
                self::pageCreate($page, $package, false, $parentPage, $key[$i], strtr($parentPage->getFullKey(), '/', '.') . '.' . $key[$i] . '.');
            }
        }
    }

    /**
     * @param rex_be_page|array $page
     * @param rex_package       $package
     * @param bool              $createMainPage
     * @param rex_be_page|null  $parentPage
     * @param string            $pageKey
     * @param bool              $prefix
     *
     * @return null|rex_be_page
     */
    private static function pageCreate($page, rex_package $package, $createMainPage, rex_be_page $parentPage = null, $pageKey = null, $prefix = false)
    {
        if (is_array($page) && isset($page['title'])) {
            $pageArray = $page;
            $pageKey = $pageKey ?: $package->getName();
            if ($createMainPage || isset($pageArray['main']) && $pageArray['main']) {
                $page = new rex_be_page_main('addons', $pageKey, $pageArray['title']);
            } else {
                $page = new rex_be_page($pageKey, $pageArray['title']);
            }
            self::pageAddProperties($page, $pageArray, $package);
        }

        if ($page instanceof rex_be_page) {
            if (!is_string($prefix)) {
                $prefix = $prefix ? $page->getKey() . '.' : '';
            }
            if ($page instanceof rex_be_page_main) {
                if (!$page->hasPath()) {
                    $page->setPath($package->getPath('pages/' . ($prefix ?: 'index.') . 'php'));
                }
                self::$pages[$page->getKey()] = $page;
            } else {
                if (!$page->hasSubPath()) {
                    $page->setSubPath($package->getPath('pages/' . ($prefix ?: 'index.') . 'php'));
                }
                if ($parentPage) {
                    $parentPage->addSubpage($page);
                }
            }
            self::pageSetSubPaths($page, $package, $prefix);
            return $page;
        }
        return null;
    }

    /**
     * @param rex_be_page $page
     * @param rex_package $package
     * @param string      $prefix
     */
    private static function pageSetSubPaths(rex_be_page $page, rex_package $package, $prefix = '')
    {
        foreach ($page->getSubpages() as $subpage) {
            if (!$subpage->hasSubPath()) {
                $subpage->setSubPath($package->getPath('pages/' . $prefix . $subpage->getKey() . '.php'));
            }
            self::pageSetSubPaths($subpage, $package, $prefix . $subpage->getKey() . '.');
        }
    }

    /**
     * @param rex_be_page $page
     * @param array       $properties
     * @param rex_package $package
     */
    private static function pageAddProperties(rex_be_page $page, array $properties, rex_package $package)
    {
        foreach ($properties as $key => $value) {
            switch (strtolower($key)) {
                case 'subpages':
                    if (is_array($value)) {
                        foreach ($value as $pageKey => $subProperties) {
                            if (isset($subProperties['title'])) {
                                $subpage = new rex_be_page($pageKey, $subProperties['title']);
                                $page->addSubpage($subpage);
                                self::pageAddProperties($subpage, $subProperties, $package);
                            }
                        }
                    }
                    break;

                case 'itemattr':
                case 'linkattr':
                    $setter = [$page, 'set' . ucfirst($key)];
                    foreach ($value as $k => $v) {
                        call_user_func($setter, $k, $v);
                    }
                    break;

                case 'perm':
                    $page->setRequiredPermissions($value);
                    break;

                case 'path':
                case 'subpath':
                    if (file_exists($path = $package->getPath($value))) {
                        $value = $path;
                    }
                    // no break
                default:
                    $setter = [$page, 'add' . ucfirst($key)];
                    if (is_callable($setter)) {
                        foreach ((array) $value as $v) {
                            call_user_func($setter, $v);
                        }
                        break;
                    }
                    $setter = [$page, 'set' . ucfirst($key)];
                    if (is_callable($setter)) {
                        call_user_func($setter, $value);
                    }
            }
        }
    }

    public static function checkPagePermissions(rex_user $user)
    {
        $check = function (rex_be_page $page) use (&$check, $user) {
            if (!$page->checkPermission($user)) {
                return false;
            }

            $subpages = $page->getSubpages();
            foreach ($subpages as $key => $subpage) {
                if (!$check($subpage)) {
                    unset($subpages[$key]);
                }
            }
            $page->setSubpages($subpages);

            return true;
        };

        foreach (self::$pages as $key => $page) {
            if (!$check($page)) {
                unset(self::$pages[$key]);
            }
        }
        self::$pageObject = null;

        $page = self::getCurrentPageObject();
        // --- page pruefen und benoetigte rechte checken
        if (!$page) {
            // --- fallback zur user startpage -> rechte checken
            $page = self::getPageObject($user->getStartPage());
            if (!$page) {
                // --- fallback zur system startpage -> rechte checken
                $page = self::getPageObject(rex::getProperty('start_page'));
                if (!$page) {
                    // --- fallback zur profile page
                    $page = self::getPageObject('profile');
                }
            }
            rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
            rex_response::sendRedirect($page->getHref());
        }
        if ($page !== $leaf = $page->getFirstSubpagesLeaf()) {
            rex_response::setStatus(rex_response::HTTP_MOVED_PERMANENTLY);
            $url = $leaf->hasHref() ? $leaf->getHref() : rex_context::fromGet()->getUrl(['page' => $leaf->getFullKey()], false);
            rex_response::sendRedirect($url);
        }
    }

    /**
     * Includes the current page. A page may be provided by the core, an addon or plugin.
     */
    public static function includeCurrentPage()
    {
        $currentPage = self::getCurrentPageObject();

        if (rex_request::isPJAXRequest() && !rex_request::isPJAXContainer('#rex-js-page-container')) {
            // non-core pjax containers should not have a layout.
            // they render their whole response on their own
            $currentPage->setHasLayout(false);
        }

        require rex_path::core('layout/top.php');

        self::includePath($currentPage->getPath());

        require rex_path::core('layout/bottom.php');
    }

    /**
     * Includes the sub-path of current page.
     *
     * @param array $context
     *
     * @return mixed
     */
    public static function includeCurrentPageSubPath(array $context = [])
    {
        $path = self::getCurrentPageObject()->getSubPath();

        if ('.md' !== strtolower(substr($path, -3))) {
            return self::includePath($path, $context);
        }

        $fragment = new rex_fragment();
        $fragment->setVar('content', rex_markdown::factory()->parse(rex_file::get($path)), false);
        $content = $fragment->parse('core/page/docs.php');

        $fragment = new rex_fragment();
        $fragment->setVar('body', $content, false);
        echo $fragment->parse('core/page/section.php');
    }

    /**
     * Includes a path in correct package context.
     *
     * @param string $path
     * @param array  $context
     *
     * @return mixed
     */
    private static function includePath($path, array $context = [])
    {
        $pattern = '@' . preg_quote(rex_path::src('addons/'), '@') . '([^/\\\]+)(?:[/\\\]plugins[/\\\]([^/\\\]+))?@';

        if (!preg_match($pattern, $path, $matches)) {
            $__context = $context;
            $__path = $path;
            unset($context, $path, $pattern, $matches);
            extract($__context, EXTR_SKIP);
            return include $__path;
        }

        $package = rex_addon::get($matches[1]);
        if (isset($matches[2])) {
            $package = $package->getPlugin($matches[2]);
        }
        return $package->includeFile(str_replace($package->getPath(), '', $path), $context);
    }
}
