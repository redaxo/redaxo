<?php

/**
 * @package redaxo\core
 */
class rex_view
{
    private static $cssFiles = [];
    private static $jsFiles = [];
    private static $jsProperties = [];
    private static $favicon;

    /**
     * Adds a CSS file
     *
     * @param string $file
     * @param string $media
     */
    public static function addCssFile($file, $media = 'all')
    {
        self::$cssFiles[$media][] = $file;
    }

    /**
     * Returns the CSS files
     *
     * @return string[]
     */
    public static function getCssFiles()
    {
        return self::$cssFiles;
    }

    /**
     * Adds a JS file
     *
     * @param string $file
     */
    public static function addJsFile($file)
    {
        self::$jsFiles[] = $file;
    }

    /**
     * Returns the JS files
     *
     * @return string[]
     */
    public static function getJsFiles()
    {
        return self::$jsFiles;
    }

    /**
     * Sets a JS property
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function setJsProperty($key, $value)
    {
        self::$jsProperties[$key] = $value;
    }

    /**
     * Returns the JS properties
     *
     * @return array
     */
    public static function getJsProperties()
    {
        return self::$jsProperties;
    }

    /**
     * Sets the favicon path
     *
     * @param string $file
     */
    public static function setFavicon($file)
    {
        self::$favicon = $file;
    }

    /**
     * Returns the favicon
     *
     * @return string
     */
    public static function getFavicon()
    {
        return self::$favicon;
    }

    /**
     * Returns an info message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    public static function info($message, $cssClass = '')
    {
        $cssClassMessage = 'rex-info';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns a success message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    public static function success($message, $cssClass = '')
    {
        $cssClassMessage = 'rex-success';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns an warning message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    public static function warning($message, $cssClass = '')
    {
        $cssClassMessage = 'rex-warning';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns an error message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    public static function error($message, $cssClass = '')
    {
        $cssClassMessage = 'rex-error';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        return self::message($message, $cssClassMessage);
    }

    /**
     * Returns a message
     *
     * @param string $message
     * @param string $cssClass
     * @return string
     */
    private static function message($message, $cssClass)
    {
        $return = '';

        $cssClassMessage = 'rex-message';
        if ($cssClass != '') {
            $cssClassMessage .= ' ' . $cssClass;
        }

        $return = '<div class="' . $cssClassMessage . '"><div class="rex-message-inner">' . $message . '</div></div>';

        /*
        $fragment = new rex_fragment();
        $fragment->setVar('class', $cssClass);
        $fragment->setVar('message', $content, false);
        $return = $fragment->parse('message.php');
        */
        return $return;
    }

    /**
     * Returns a toolbar
     *
     * @param string $content
     * @param string $cssClass
     * @return string
     */
    public static function toolbar($content, $cssClass = null)
    {
        $return = '';
        $fragment = new rex_fragment();
        $fragment->setVar('class', $cssClass);
        $fragment->setVar('content', $content, false);
        $return = $fragment->parse('toolbar.php');

        return $return;
    }

    /**
     * Returns a content block
     *
     * @param string $content_1
     * @param string $content_2
     * @param bool   $flush
     * @param bool   $bucket
     * @param string $title
     * @return string
     */
    public static function content($key = 'block', $content, $title = '', $params = array())
    {
    
        if(!is_array($content)) {
          $content = array($content);
        }

        $fragment = new rex_fragment();
        $fragment->setVar('content', $content, false);
        $fragment->setVar('title', $title, false);
        $fragment->setVar('params', $params, false);
        return $fragment->parse('core/content/'.$key.'.php');
    }
    

    /**
     * Returns the formatted title
     *
     * @param string            $head
     * @param null|string|array $subtitle
     * @throws InvalidArgumentException
     * @return string
     */
    public static function title($head, $subtitle = null)
    {
        global $article_id, $category_id, $page;

        if ($subtitle !== null && !is_string($subtitle) && (!is_array($subtitle) || count($subtitle) > 0 && !reset($subtitle) instanceof rex_be_page)) {
            throw new InvalidArgumentException('Expecting $subtitle to be a string or an array of rex_be_page!');
        }

        if ($subtitle === null) {
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
            if (count($blocks) == 1) {
                $navigation = current($blocks);
                $navigation = $navigation['navigation'];
            }

            if (!empty($navigation)) {
                $fragment = new rex_fragment();
                $fragment->setVar('navigation_left', $navigation, false);
                $subtitle = $fragment->parse('core/navigations/content.php');
            } else {
                $subtitle = '';
            }

        } elseif (!is_string($subtitle)) {
            $subtitle = '';
        }

        $title = rex_extension::registerPoint(new rex_extension_point('PAGE_TITLE', $head, ['category_id' => $category_id, 'article_id' => $article_id, 'page' => $page]));

        $return = '<h1>' . $title . '</h1>' . $subtitle;

        echo rex_extension::registerPoint(new rex_extension_point('PAGE_TITLE_SHOWN', '', [
            'category_id' => $category_id,
            'article_id' => $article_id,
            'page' => $page
        ]));

        return $return;
    }

    /**
     * Returns a clang switch
     *
     * @param rex_context $context
     * @return string
     */
    public static function clangSwitch(rex_context $context)
    {
        if (!rex_clang::count()) {
            return '';
        }

        $button = '';
        $items  = [];
        foreach (rex_clang::getAll() as $id => $clang) {
            if (rex::getUser()->getComplexPerm('clang')->hasPerm($id)) {
                $item = [];
                $item['title'] = rex_i18n::translate($clang->getName());
                $item['href']  = $context->getUrl(['clang' => $id]);
                if ($id == $context->getParam('clang')) {
                    $item['active'] = true;
                    $button = rex_i18n::translate($clang->getName());
                }
                $items[] = $item;
            }
        }

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'rex-language');
        $fragment->setVar('button', $button);
        $fragment->setVar('button_title', rex_i18n::msg('language'));
        $fragment->setVar('header', rex_i18n::msg('clang_select'));
        $fragment->setVar('items', $items, false);
        $fragment->setVar('check', true);

        if (rex::getUser()->isAdmin()) {
            $fragment->setVar('footer', '<a href="' . rex_url::backendPage('system/lang') . '"><span class="rex-icon rex-icon-language"></span>' . rex_i18n::msg('languages_edit') . '</a>', false);
        }

        return $fragment->parse('core/navigations/drop.php');
    }
}
