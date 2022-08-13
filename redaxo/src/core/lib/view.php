<?php

/**
 * @package redaxo\core
 */
class rex_view
{
    public const JS_DEFERED = 'defer';
    public const JS_ASYNC = 'async';
    public const JS_IMMUTABLE = 'immutable';

    /** @var string[][] */
    private static $cssFiles = [];
    /** @var list<array{string, array}> */
    private static $jsFiles = [];
    /** @var array<string, mixed> */
    private static $jsProperties = [];
    /** @var string */
    private static $favicon;

    /**
     * Adds a CSS file.
     *
     * @param string $file
     * @param string $media
     *
     * @throws rex_exception
     * @return void
     */
    public static function addCssFile($file, $media = 'all')
    {
        if (isset(self::$cssFiles[$media]) && in_array($file, self::$cssFiles[$media])) {
            throw new rex_exception(sprintf('The CSS file "%s" is already added to media "%s".', $file, $media));
        }

        self::$cssFiles[$media][] = $file;
    }

    /**
     * Returns the CSS files.
     *
     * @return string[][]
     */
    public static function getCssFiles()
    {
        return self::$cssFiles;
    }

    /**
     * Adds a JS file.
     *
     * @param string $file
     * @psalm-param array<self::JS_*, bool>|array<self::JS_*> $options
     *
     * @throws rex_exception
     * @return void
     */
    public static function addJsFile($file, array $options = [])
    {
        if (empty($options)) {
            $options[self::JS_IMMUTABLE] = false;
        }

        if (in_array($file, self::$jsFiles)) {
            throw new rex_exception(sprintf('The JS file "%s" is already added.', $file));
        }

        self::$jsFiles[] = [$file, $options];
    }

    /**
     * Returns the JS files.
     *
     * @return string[]
     */
    public static function getJsFiles()
    {
        // transform for BC
        return array_map(static function ($jsFile) {
            return $jsFile[0];
        }, self::$jsFiles);
    }

    /**
     * Returns all JS files besides their options.
     *
     * @psalm-return list<array{string, array}>
     *
     * @return array
     */
    public static function getJsFilesWithOptions()
    {
        return self::$jsFiles;
    }

    /**
     * Sets a JS property.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public static function setJsProperty($key, $value)
    {
        self::$jsProperties[$key] = $value;
    }

    /**
     * Returns the JS properties.
     *
     * @psalm-return array<string, mixed>
     *
     * @return array
     */
    public static function getJsProperties()
    {
        return self::$jsProperties;
    }

    /**
     * Sets the favicon path.
     *
     * @param string $file
     * @return void
     */
    public static function setFavicon($file)
    {
        self::$favicon = $file;
    }

    /**
     * Returns the favicon.
     *
     * @return string
     */
    public static function getFavicon()
    {
        return self::$favicon;
    }

    /**
     * Returns an info message.
     *
     * @param string $message
     * @param string $cssClass
     *
     * @return string
     *
     * @psalm-taint-specialize
     */
    public static function info($message, $cssClass = '')
    {
        $cssClassMessage = 'alert-info';
        if ('' != $cssClass) {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns a success message.
     *
     * @param string $message
     * @param string $cssClass
     *
     * @return string
     *
     * @psalm-taint-specialize
     */
    public static function success($message, $cssClass = '')
    {
        $cssClassMessage = 'alert-success';
        if ('' != $cssClass) {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns an warning message.
     *
     * @param string $message
     * @param string $cssClass
     *
     * @return string
     *
     * @psalm-taint-specialize
     */
    public static function warning($message, $cssClass = '')
    {
        $cssClassMessage = 'alert-warning';
        if ('' != $cssClass) {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns an error message.
     *
     * @param string $message
     * @param string $cssClass
     *
     * @return string
     *
     * @psalm-taint-specialize
     */
    public static function error($message, $cssClass = '')
    {
        $cssClassMessage = 'alert-danger';
        if ('' != $cssClass) {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns a message.
     *
     * @param string $message
     * @param string $cssClass
     *
     * @return string
     */
    private static function message($message, $cssClass)
    {
        $cssClassMessage = 'alert';
        if ('' != $cssClass) {
            $cssClassMessage .= ' ' . $cssClass;
        }

        /*
        $fragment = new rex_fragment();
        $fragment->setVar('class', $cssClass);
        $fragment->setVar('message', $content, false);
        $return = $fragment->parse('message.php');
        */
        return '<div class="' . $cssClassMessage . '">' . $message . '</div>';
    }

    /**
     * Returns a toolbar.
     *
     * @param string $content
     * @param string $brand
     * @param string $cssClass
     *
     * @return string
     */
    public static function toolbar($content, $brand = null, $cssClass = null, $inverse = false)
    {
        $fragment = new rex_fragment();
        $fragment->setVar('inverse', $inverse);
        $fragment->setVar('cssClass', $cssClass);
        $fragment->setVar('brand', $brand);
        $fragment->setVar('content', $content, false);

        return $fragment->parse('core/toolbar.php');
    }

    /**
     * Returns a content block.
     *
     * @param string $content
     * @param string $title
     *
     * @return string
     */
    public static function content($content, $title = '')
    {
        $fragment = new rex_fragment();
        $fragment->setVar('title', $title, false);
        $fragment->setVar('body', $content, false);
        return $fragment->parse('core/page/section.php');
    }

    /**
     * Returns the formatted title.
     *
     * @param string            $head
     * @param null|string|array $subtitle
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function title($head, $subtitle = null)
    {
        if (null !== $subtitle && !is_string($subtitle) && (!is_array($subtitle) || count($subtitle) > 0 && !reset($subtitle) instanceof rex_be_page)) {
            throw new InvalidArgumentException('Expecting $subtitle to be a string or an array of rex_be_page!');
        }

        if (null === $subtitle) {
            $subtitle = rex_be_controller::getPageObject(rex_be_controller::getCurrentPagePart(1))->getSubpages();
        }

        if (is_array($subtitle) && count($subtitle) && reset($subtitle) instanceof rex_be_page) {
            $nav = rex_be_navigation::factory();
            $nav->setHeadline('default', rex_i18n::msg('subnavigation', $head));
            foreach ($subtitle as $pageObj) {
                $nav->addPage($pageObj);
            }
            $blocks = $nav->getNavigation();
            $navigation = [];
            if (1 == count($blocks)) {
                $navigation = current($blocks);
                $navigation = $navigation['navigation'];
            }

            if (!empty($navigation)) {
                $fragment = new rex_fragment();
                $fragment->setVar('left', $navigation, false);
                $subtitle = $fragment->parse('core/navigations/content.php');
            } else {
                $subtitle = '';
            }
        } elseif (!is_string($subtitle)) {
            $subtitle = '';
        }

        $title = rex_extension::registerPoint(new rex_extension_point('PAGE_TITLE', $head));

        $fragment = new rex_fragment();
        $fragment->setVar('heading', $title, false);
        $fragment->setVar('subtitle', $subtitle, false);
        $return = $fragment->parse('core/page/header.php');

        return $return . rex_extension::registerPoint(new rex_extension_point('PAGE_TITLE_SHOWN', ''));
    }

    /**
     * Returns a clang switch.
     *
     * @param bool $asDropDown
     *
     * @return string
     */
    public static function clangSwitch(rex_context $context, $asDropDown = true)
    {
        if (1 == rex_clang::count()) {
            return '';
        }

        if ($asDropDown && rex_clang::count() >= 4) {
            return self::clangSwitchAsDropdown($context);
        }

        $items = [];
        foreach (rex_clang::getAll() as $id => $clang) {
            if (rex::requireUser()->getComplexPerm('clang')->hasPerm($id)) {
                $icon = ($id == $context->getParam('clang')) ? '<i class="rex-icon rex-icon-language-active"></i> ' : '<i class="rex-icon rex-icon-language"></i> ';
                $item = [];
                $item['href'] = $context->getUrl(['clang' => $id]);
                $item['title'] = $icon . rex_i18n::translate($clang->getName());
                if ($id == $context->getParam('clang')) {
                    $item['active'] = true;
                }
                $items[] = $item;
            }
        }
        $fragment = new rex_fragment();
        $fragment->setVar('left', $items, false);

        return $fragment->parse('core/navigations/content.php');
    }

    /**
     * Returns a clang switch.
     *
     * @param bool $asDropDown
     *
     * @return string
     */
    public static function clangSwitchAsButtons(rex_context $context, $asDropDown = true)
    {
        if (1 == rex_clang::count()) {
            return '';
        }

        if ($asDropDown && rex_clang::count() >= 4) {
            return self::clangSwitchAsDropdown($context);
        }

        $items = [];
        foreach (rex_clang::getAll() as $id => $clang) {
            if (rex::requireUser()->getComplexPerm('clang')->hasPerm($id)) {
                $icon = $clang->isOnline() ? '<i class="rex-icon rex-icon-online"></i> ' : '<i class="rex-icon rex-icon-offline"></i> ';
                $item = [];
                $item['label'] = $icon . rex_i18n::translate($clang->getName());
                $item['url'] = $context->getUrl(['clang' => $id]);
                $item['attributes']['class'][] = 'btn-clang';
                $item['attributes']['title'] = rex_i18n::translate($clang->getName());
                if ($id == $context->getParam('clang')) {
                    $item['attributes']['class'][] = 'active';
                }
                $items[] = $item;
            }
        }

        $fragment = new rex_fragment();
        $fragment->setVar('buttons', $items, false);
        return '<div class="rex-nav-btn rex-nav-language"><div class="btn-toolbar">' . $fragment->parse('core/buttons/button_group.php') . '</div></div>';
    }

    /**
     * Returns a clang switch.
     *
     * @return string
     */
    public static function clangSwitchAsDropdown(rex_context $context)
    {
        if (1 == rex_clang::count()) {
            return '';
        }

        $user = rex::requireUser();

        $buttonLabel = '';
        $items = [];
        foreach (rex_clang::getAll() as $id => $clang) {
            if ($user->getComplexPerm('clang')->hasPerm($id)) {
                $item = [];
                $item['title'] = rex_i18n::translate($clang->getName());
                $item['href'] = $context->getUrl(['clang' => $id]);
                if ($id == $context->getParam('clang')) {
                    $item['active'] = true;
                    $buttonLabel = rex_i18n::translate($clang->getName());
                }
                $items[] = $item;
            }
        }

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'rex-language');
        $fragment->setVar('button_prefix', rex_i18n::msg('language'));
        $fragment->setVar('button_label', $buttonLabel);
        $fragment->setVar('header', rex_i18n::msg('clang_select'));
        $fragment->setVar('items', $items, false);

        if ($user->isAdmin()) {
            $fragment->setVar('footer', '<a href="' . rex_url::backendPage('system/lang') . '"><i class="fa fa-flag"></i> ' . rex_i18n::msg('languages_edit') . '</a>', false);
        }

        return $fragment->parse('core/dropdowns/dropdown.php');
    }
}
