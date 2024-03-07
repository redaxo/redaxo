<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Util;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Translation\I18n;

class rex_be_controller
{
    /** @var string */
    private static $page;

    /** @var list<string> */
    private static $pageParts = [];

    /** @var rex_be_page|null */
    private static $pageObject;

    /** @var array<string, rex_be_page> */
    private static $pages = [];

    /**
     * @param string $page
     * @return void
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
     * @template T of positive-int|null
     * @param T $part Part index, beginning with 1. If $part is null, an array of all current parts will be returned
     * @param string|null $default Default value
     * @return list<string>|string|null
     * @psalm-return (T is null ? list<string> : string|null)
     */
    public static function getCurrentPagePart($part = null, $default = null)
    {
        if (null === $part) {
            return self::$pageParts;
        }
        --$part;
        return self::$pageParts[$part] ?? $default;
    }

    /**
     * @return rex_be_page|null
     */
    public static function getCurrentPageObject()
    {
        if (!self::$pageObject) {
            self::$pageObject = self::getPageObject(self::getCurrentPage());
        }
        return self::$pageObject;
    }

    public static function requireCurrentPageObject(): rex_be_page
    {
        return rex_type::notNull(self::getCurrentPageObject());
    }

    /**
     * @param string|list<string> $page
     *
     * @return rex_be_page|null
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
     * @return array<string, rex_be_page>
     */
    public static function getPages()
    {
        return self::$pages;
    }

    /**
     * @param array<string, rex_be_page> $pages
     * @return void
     */
    public static function setPages(array $pages)
    {
        self::$pages = $pages;
    }

    /**
     * @return string
     */
    public static function getPageTitle()
    {
        $parts = [];

        $activePageObj = self::requireCurrentPageObject();
        if ($activePageObj->getTitle()) {
            $parts[] = $activePageObj->getTitle();
        }
        if (Core::getServerName()) {
            $parts[] = Core::getServerName();
        }
        $parts[] = 'REDAXO CMS';

        return implode(' · ', $parts);
    }

    /**
     * @return rex_be_page
     */
    public static function getSetupPage()
    {
        $page = new rex_be_page('setup', I18n::msg('setup'));
        $page->setPath(rex_path::core('pages/setup.php'));
        return $page;
    }

    /**
     * @return rex_be_page
     */
    public static function getLoginPage()
    {
        $page = new rex_be_page('login', 'Login');
        $page->setPath(rex_path::core('pages/login.php'));
        $page->setHasNavigation(false);
        return $page;
    }

    /**
     * @return void
     */
    public static function appendLoggedInPages()
    {
        self::$pages['profile'] = (new rex_be_page('profile', I18n::msg('profile')))
            ->setPath(rex_path::core('pages/profile.php'))
            ->setPjax();

        self::$pages['credits'] = (new rex_be_page('credits', I18n::msg('credits')))
            ->setPath(rex_path::core('pages/credits.php'));

        $logsPage = (new rex_be_page('log', I18n::msg('logfiles')))->setSubPath(rex_path::core('pages/system.log.php'));
        $logsPage->addSubpage((new rex_be_page('redaxo', I18n::msg('syslog_redaxo')))->setSubPath(rex_path::core('pages/system.log.redaxo.php')));
        if ('' != ini_get('error_log') && @is_readable(ini_get('error_log'))) {
            $logsPage->addSubpage((new rex_be_page('php', I18n::msg('syslog_phperrors')))->setSubPath(rex_path::core('pages/system.log.external.php')));
        }
        $logsPage->addSubpage((new rex_be_page('phpmailer', I18n::msg('phpmailer_title')))->setSubPath(rex_path::core('pages/phpmailer.log.php')));

        if ('system' === self::getCurrentPagePart(1) && 'log' === self::getCurrentPagePart(2)) {
            $slowQueryLogPath = Util::slowQueryLogPath();
            if (null !== $slowQueryLogPath && @is_readable($slowQueryLogPath)) {
                $logsPage->addSubpage((new rex_be_page('slow-queries', I18n::msg('syslog_slowqueries')))->setSubPath(rex_path::core('pages/system.log.slow-queries.php')));
            }
        }

        $logsPage->addSubpage((new rex_be_page('cronjob', I18n::msg('cronjob_title')))->setSubPath(rex_path::core('pages/system.log.cronjob.php')));

        $beStylePage = (new rex_be_page('be_style', I18n::msg('be_style')));
        $beStylePage
            ->addSubpage((new rex_be_page('customizer', I18n::msg('customizer')))->setSubPath(rex_path::core('pages/system.be_style.customizer.php')))
            ->addSubpage((new rex_be_page('icons', I18n::msg('be_style_icons')))->setSubPath(rex_path::core('pages/system.be_style.icons.php')))
            ->addSubpage((new rex_be_page('help', I18n::msg('be_style_help')))->setSubPath(rex_path::core('pages/system.be_style.README.md')));

        rex_extension::register('PACKAGES_INCLUDED', static function () use ($beStylePage) {
            if (rex_extension::isRegistered('BE_STYLE_PAGE_CONTENT')) {
                $beStylePage->addSubpage((new rex_be_page('themes', I18n::msg('be_style_themes')))->setSubPath(rex_path::core('pages/system.be_style.themes.php')));
            }
        });

        self::$pages['structure'] = (new rex_be_page_main('system', 'structure', I18n::msg('structure')))
            ->setPath(rex_path::core('pages/structure.php'))
            ->setRequiredPermissions('structure/hasStructurePerm')
            ->setPrio(10)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-open-category')
        ;
        self::$pages['modules'] = (new rex_be_page_main('system', 'modules', I18n::msg('modules')))
            ->setPath(rex_path::core('pages/structure.modules.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(40)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-module')
            ->addSubpage((new rex_be_page('modules', I18n::msg('modules')))->setSubPath(rex_path::core('pages/structure.modules.modules.php')))
            ->addSubpage((new rex_be_page('actions', I18n::msg('actions')))->setSubPath(rex_path::core('pages/structure.modules.actions.php')))
        ;
        self::$pages['templates'] = (new rex_be_page_main('system', 'templates', I18n::msg('templates')))
            ->setPath(rex_path::core('pages/structure.templates.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(30)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-template')
        ;
        self::$pages['content'] = (new rex_be_page_main('system', 'content', I18n::msg('content')))
            ->setPath(rex_path::core('pages/structure.content.php'))
            ->setRequiredPermissions('structure/hasStructurePerm')
            ->setPjax(false)
            ->setHidden()
            ->addSubpage((new rex_be_page('edit', I18n::msg('edit_mode')))
                ->setSubPath(rex_path::core('pages/structure.content.edit.php'))
                ->setIcon('rex-icon rex-icon-editmode')
                ->setItemAttr('left', 'true'),
            )
            ->addSubpage((new rex_be_page('functions', I18n::msg('metafuncs')))
                ->setSubPath(rex_path::core('pages/structure.content.functions.php'))
                ->setIcon('rex-icon rex-icon-metafuncs'),
            )
        ;
        self::$pages['linkmap'] = (new rex_be_page_main('system', 'linkmap', I18n::msg('linkmap')))
            ->setPath(rex_path::core('pages/structure.linkmap.php'))
            ->setRequiredPermissions('structure/hasStructurePerm')
            ->setPjax()
            ->setPopup(true)
            ->setHidden()
        ;

        self::$pages['system'] = (new rex_be_page_main('system', 'system', I18n::msg('system')))
            ->setPath(rex_path::core('pages/system.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(100)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-system')
            ->addSubpage((new rex_be_page('settings', I18n::msg('main_preferences')))->setSubPath(rex_path::core('pages/system.settings.php')))
            ->addSubpage((new rex_be_page('lang', I18n::msg('languages')))->setSubPath(rex_path::core('pages/system.clangs.php')))
            ->addSubpage($logsPage)
            ->addSubpage(
                (new rex_be_page('report', I18n::msg('system_report')))
                ->addSubpage((new rex_be_page('html', I18n::msg('system_report')))->setSubPath(rex_path::core('pages/system.report.html.php')))
                ->addSubpage((new rex_be_page('markdown', I18n::msg('system_report_markdown')))->setSubPath(rex_path::core('pages/system.report.markdown.php'))),
            )
            ->addSubpage($beStylePage)
            ->addSubpage((new rex_be_page('phpinfo', 'phpinfo'))
                ->setHidden(true)
                ->setHasLayout(false)
                ->setPath(rex_path::core('pages/system.phpinfo.php')),
            )
        ;

        if (Core::getConfig('article_history', false)) {
            self::$pages['content']->addSubpage((new rex_be_page('history', ''))
                ->setRequiredPermissions('history[article_rollback]')
                ->setIcon('fa fa-history')
                ->setHref('#')
                ->setItemAttr('left', 'true')
                ->setLinkAttr('data-history-layer', 'open'),
            );
            self::$pages['system']->addSubpage((new rex_be_page('history', I18n::msg('structure_history')))->setSubPath(rex_path::core('pages/structure.system.history.php')));
        }

        self::$pages['users'] = (new rex_be_page_main('system', 'users', I18n::msg('users')))
            ->setPath(rex_path::core('pages/users.php'))
            ->setRequiredPermissions('users[]')
            ->setPrio(50)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-user')
            ->addSubpage(
                (new rex_be_page('users', I18n::msg('users')))
                    ->setSubPath(rex_path::core('pages/users.users.php')),
            )
            ->addSubpage(
                (new rex_be_page('roles', I18n::msg('roles')))
                    ->setSubPath(rex_path::core('pages/users.roles.php'))
                    ->setRequiredPermissions('isAdmin'),
            )
        ;

        self::$pages['cronjob'] = (new rex_be_page_main('system', 'cronjob', I18n::msg('cronjob_title')))
            ->setPath(rex_path::core('pages/cronjob.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(80)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-cronjob')
            ->addSubpage((new rex_be_page('cronjobs', I18n::msg('cronjob_title')))->setSubPath(rex_path::core('pages/cronjob.cronjobs.php')))
            ->addSubpage((new rex_be_page('log', I18n::msg('cronjob_log')))->setSubPath(rex_path::core('pages/cronjob.log.php')))
        ;

        self::$pages['mediapool'] = (new rex_be_page_main('system', 'mediapool', I18n::msg('mediapool')))
            ->setPath(rex_path::core('pages/mediapool.php'))
            ->setRequiredPermissions('media/hasMediaPerm')
            ->setPrio(20)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-media')
            ->setPopup('openMediaPool(); return false;')
            ->addSubpage((new rex_be_page('media', I18n::msg('pool_file_list')))->setSubPath(rex_path::core('pages/mediapool.media.php')))
            ->addSubpage((new rex_be_page('upload', I18n::msg('pool_file_insert')))->setSubPath(rex_path::core('pages/mediapool.upload.php')))
            ->addSubpage((new rex_be_page('structure', I18n::msg('pool_cat_list')))->setRequiredPermissions('media/hasAll')->setSubPath(rex_path::core('pages/mediapool.structure.php')))
            ->addSubpage((new rex_be_page('sync', I18n::msg('pool_sync_files')))->setRequiredPermissions('media[sync]')->setSubPath(rex_path::core('pages/mediapool.sync.php')))
        ;

        self::$pages['phpmailer'] = (new rex_be_page_main('system', 'phpmailer', I18n::msg('phpmailer_title')))
            ->setPath(rex_path::core('pages/phpmailer.php'))
            ->setRequiredPermissions('phpmailer[]')
            ->setPrio(90)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-envelope' . (Core::getConfig('phpmailer_detour_mode') ? ' text-danger' : ''))
            ->addSubpage((new rex_be_page('config', I18n::msg('phpmailer_configuration')))->setSubPath(rex_path::core('pages/phpmailer.config.php')))
            ->addSubpage((new rex_be_page('log', I18n::msg('phpmailer_logging')))->setSubPath(rex_path::core('pages/phpmailer.log.php')))
            ->addSubpage((new rex_be_page('help', I18n::msg('phpmailer_help')))->setSubPath(rex_path::core('pages/phpmailer.README.md')))
            ->addSubpage((new rex_be_page('checkmail', I18n::msg('phpmailer_checkmail')))->setSubPath(rex_path::core('pages/phpmailer.checkmail.php'))->setHidden(true))
        ;

        self::$pages['backup'] = $backup = (new rex_be_page_main('system', 'backup', I18n::msg('backup_title')))
            ->setPath(rex_path::core('pages/backup.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(110)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-backup')
            ->addSubpage(
                (new rex_be_page('export', I18n::msg('backup_export')))
                    ->setSubPath(rex_path::core('pages/backup.export.php'))
                    ->setRequiredPermissions('backup[export]'),
            )
        ;

        if (Core::isLiveMode()) {
            return;
        }

        $backup->addSubpage((new rex_be_page('import', I18n::msg('backup_import')))
            ->addSubpage((new rex_be_page('upload', I18n::msg('backup_upload')))->setSubPath(rex_path::core('pages/backup.import.upload.php')))
            ->addSubpage((new rex_be_page('server', I18n::msg('backup_load_from_server')))->setSubPath(rex_path::core('pages/backup.import.server.php'))),
        );

        self::$pages['packages'] = (new rex_be_page_main('system', 'packages', I18n::msg('addons')))
            ->setPath(rex_path::core('pages/packages.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(60)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-package-addon');

        self::$pages['media_manager'] = (new rex_be_page_main('system', 'media_manager', I18n::msg('media_manager')))
            ->setPath(rex_path::core('pages/media_manager.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(70)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-media')
            ->addSubpage((new rex_be_page('types', I18n::msg('media_manager_subpage_types')))->setSubPath(rex_path::core('pages/media_manager.types.php')))
            ->addSubpage((new rex_be_page('settings', I18n::msg('media_manager_subpage_config')))->setSubPath(rex_path::core('pages/media_manager.settings.php')))
            ->addSubpage((new rex_be_page('overview', I18n::msg('media_manager_subpage_desc')))->setSubPath(rex_path::core('pages/media_manager.README.md')))
            ->addSubpage((new rex_be_page('clear_cache', I18n::msg('media_manager_subpage_clear_cache')))
                ->setItemAttr('class', 'pull-right')
                ->setLinkAttr('class', 'btn btn-delete')
                ->setHref(['page' => 'media_manager/types', 'func' => 'clear_cache']),
            )
        ;

        self::$pages['metainfo'] = (new rex_be_page_main('system', 'metainfo', I18n::msg('metainfo')))
            ->setPath(rex_path::core('pages/metainfo.php'))
            ->setRequiredPermissions('isAdmin')
            ->setPrio(75)
            ->setPjax()
            ->setIcon('rex-icon rex-icon-metainfo')
            ->addSubpage(new rex_be_page('articles', I18n::msg('metainfo_articles')))
            ->addSubpage(new rex_be_page('categories', I18n::msg('metainfo_categories')))
            ->addSubpage(new rex_be_page('media', I18n::msg('metainfo_media')))
            ->addSubpage(new rex_be_page('clangs', I18n::msg('metainfo_clangs')))
            ->addSubpage((new rex_be_page('help', I18n::msg('metainfo_help')))->setSubPath(rex_path::core('pages/metainfo.README.md')))
        ;
    }

    /**
     * @return void
     */
    public static function appendPackagePages()
    {
        $insertPages = [];
        $addons = Core::isSafeMode() ? rex_addon::getSetupAddons() : rex_addon::getAvailableAddons();
        foreach ($addons as $addon) {
            $mainPage = self::pageCreate($addon->getProperty('page'), $addon, true);

            if (is_array($pages = $addon->getProperty('pages'))) {
                foreach ($pages as $key => $page) {
                    if (str_contains($key, '/')) {
                        $insertPages[$key] = [$addon, $page];
                    } else {
                        self::pageCreate($page, $addon, false, $mainPage, $key, true);
                    }
                }
            }
        }
        foreach ($insertPages as $key => $packagePage) {
            [$package, $page] = $packagePage;
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
     * @param bool $createMainPage
     * @param string $pageKey
     * @param bool|string $prefix
     *
     * @return rex_be_page|null
     */
    private static function pageCreate($page, rex_addon $package, $createMainPage, ?rex_be_page $parentPage = null, $pageKey = null, $prefix = false)
    {
        if (is_array($page) && isset($page['title']) && (false !== ($page['live_mode'] ?? null) || !Core::isLiveMode())) {
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
     * @param string $prefix
     * @return void
     */
    private static function pageSetSubPaths(rex_be_page $page, rex_addon $package, $prefix = '')
    {
        foreach ($page->getSubpages() as $subpage) {
            if (!$subpage->hasSubPath()) {
                $subpage->setSubPath($package->getPath('pages/' . $prefix . $subpage->getKey() . '.php'));
            }
            self::pageSetSubPaths($subpage, $package, $prefix . $subpage->getKey() . '.');
        }
    }

    /**
     * @return void
     */
    private static function pageAddProperties(rex_be_page $page, array $properties, rex_addon $package)
    {
        foreach ($properties as $key => $value) {
            switch (strtolower($key)) {
                case 'subpages':
                    if (is_array($value)) {
                        foreach ($value as $pageKey => $subProperties) {
                            if (isset($subProperties['title']) && (false !== ($subProperties['live_mode'] ?? null) || !Core::isLiveMode())) {
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
                    if (is_file($path = $package->getPath($value))) {
                        $value = $path;
                    }
                    // no break
                default:
                    $adder = [$page, 'add' . ucfirst($key)];
                    if (is_callable($adder)) {
                        foreach ((array) $value as $v) {
                            call_user_func($adder, $v);
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

    /**
     * @return void
     */
    public static function checkPagePermissions(rex_user $user)
    {
        $check = static function (rex_be_page $page) use (&$check, $user) {
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
            $page = $user->getStartPage();
            $page = $page ? self::getPageObject($page) : null;
            if (!$page) {
                // --- fallback zur system startpage -> rechte checken
                $page = self::getPageObject(Core::getProperty('start_page'));
                if (!$page) {
                    // --- fallback zur profile page
                    $page = rex_type::notNull(self::getPageObject('profile'));
                }
            }
            rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
            rex_response::sendRedirect($page->getHref());
        }
        if ($page !== $leaf = $page->getFirstSubpagesLeaf()) {
            rex_response::setStatus(rex_response::HTTP_MOVED_PERMANENTLY);
            $url = $leaf->hasHref() ? $leaf->getHref() : rex_context::fromGet()->getUrl(['page' => $leaf->getFullKey()]);
            rex_response::sendRedirect($url);
        }
    }

    /**
     * Includes the current page. A page may be provided by the core or an addon.
     * @return void
     */
    public static function includeCurrentPage()
    {
        $currentPage = self::requireCurrentPageObject();

        if (rex_request::isPJAXRequest() && !rex_request::isPJAXContainer('#rex-js-page-container')) {
            // non-core pjax containers should not have a layout.
            // they render their whole response on their own
            $currentPage->setHasLayout(false);
        }

        rex_timer::measure('Layout: top.php', function () {
            require rex_path::core('layout/top.php');
        });

        self::includePath(rex_type::string($currentPage->getPath()));

        rex_timer::measure('Layout: bottom.php', function () {
            require rex_path::core('layout/bottom.php');
        });
    }

    /**
     * Includes the sub-path of current page.
     *
     * @return mixed
     */
    public static function includeCurrentPageSubPath(array $context = [])
    {
        $path = rex_type::string(self::requireCurrentPageObject()->getSubPath());

        if ('.md' !== strtolower(substr($path, -3))) {
            return self::includePath($path, $context);
        }

        $languagePath = substr($path, 0, -3) . '.' . I18n::getLanguage() . '.md';
        if (is_readable($languagePath)) {
            $path = $languagePath;
        }

        [$toc, $content] = rex_markdown::factory()->parseWithToc(File::require($path), 2, 3, [
            rex_markdown::SOFT_LINE_BREAKS => false,
            rex_markdown::HIGHLIGHT_PHP => true,
        ]);
        $fragment = new rex_fragment();
        $fragment->setVar('content', $content, false);
        $fragment->setVar('toc', $toc, false);
        $content = $fragment->parse('core/page/docs.php');

        $fragment = new rex_fragment();
        $fragment->setVar('title', self::requireCurrentPageObject()->getTitle(), false);
        $fragment->setVar('body', $content, false);
        echo $fragment->parse('core/page/section.php');

        return null;
    }

    /**
     * Includes a path in correct package context.
     *
     * @param string $path
     *
     * @return mixed
     */
    private static function includePath($path, array $context = [])
    {
        return rex_timer::measure('Page: ' . rex_path::relative($path, rex_path::src()), function () use ($path, $context) {
            $pattern = '@' . preg_quote(rex_path::src('addons/'), '@') . '([^/\\\]+)@';

            if (!preg_match($pattern, $path, $matches)) {
                $__context = $context;
                $__path = $path;

                unset($context, $path, $pattern, $matches);

                extract($__context, EXTR_SKIP);

                return include $__path;
            }

            $package = rex_addon::get($matches[1]);
            return $package->includeFile(str_replace($package->getPath(), '', $path), $context);
        });
    }
}
