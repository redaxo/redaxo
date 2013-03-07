<?php

/**
 * @package redaxo\core
 */
class rex_view
{
    private static
        $cssFiles = [],
        $jsFiles = [],
        $jsProperties = [],
        $favicon;

    public static function addCssFile($file, $media = 'all')
    {
        self::$cssFiles[$media][] = $file;
    }

    public static function getCssFiles()
    {
        return self::$cssFiles;
    }

    public static function addJsFile($file)
    {
        self::$jsFiles[] = $file;
    }

    public static function getJsFiles()
    {
        return self::$jsFiles;
    }

    public static function setJsProperty($key, $value)
    {
        self::$jsProperties[$key] = $value;
    }

    public static function getJsProperties()
    {
        return self::$jsProperties;
    }

    public static function setFavicon($file)
    {
        self::$favicon = $file;
    }

    public static function getFavicon()
    {
        return self::$favicon;
    }

    public static function info($message, $cssClass = '')
    {
        $cssClassMessage = 'rex-info';
        if ($cssClass != '')
            $cssClassMessage .= ' ' . $cssClass;

        return self::message($message, $cssClassMessage);
    }

    public static function success($message, $cssClass = '')
    {
        $cssClassMessage = 'rex-success';
        if ($cssClass != '')
            $cssClassMessage .= ' ' . $cssClass;

        return self::message($message, $cssClassMessage);
    }

    public static function warning($message, $cssClass = '')
    {
        $cssClassMessage = 'rex-warning';
        if ($cssClass != '')
            $cssClassMessage .= ' ' . $cssClass;

        return self::message($message, $cssClassMessage);
    }

    public static function error($message, $cssClass = '')
    {
        $cssClassMessage = 'rex-error';
        if ($cssClass != '')
            $cssClassMessage .= ' ' . $cssClass;

        return self::message($message, $cssClassMessage);
    }

    private static function message($message, $cssClass)
    {
        $return = '';

        $cssClassMessage = 'rex-message';
        if ($cssClass != '')
            $cssClassMessage .= ' ' . $cssClass;

        $return = '<div class="' . $cssClassMessage . '"><div class="rex-message-inner">' . $message . '</div></div>';

        /*
        $fragment = new rex_fragment();
        $fragment->setVar('class', $cssClass);
        $fragment->setVar('message', $content, false);
        $return = $fragment->parse('message.tpl');
        */
        return $return;
    }

    public static function toolbar($content, $cssClass = null)
    {
        $return = '';
        $fragment = new rex_fragment();
        $fragment->setVar('class', $cssClass);
        $fragment->setVar('content', $content, false);
        $return = $fragment->parse('toolbar.tpl');

        return $return;
    }

    public static function contentBlock($content_1, $content_2 = '', $flush = true, $bucket = true, $title = '')
    {
        $return = '';

        $class_1 = '';
        $class_2 = '';
        if ($bucket) {
            $class_1 .= ' rex-bucket';
            $class_2 .= ' rex-bucket-inner';
        }
        if ($flush) {
            $class_1 .= ' rex-flush';
        }

        $return .= '<section class="rex-content' . $class_1 . '">';
        $return .= '<div class="rex-content-inner' . $class_2 . '">';

        if ($title != '')
             $return .= '<h2>' . $title . '</h2>';

        if ($content_2 != '') {
            $return .= '
                <div class="rex-grid2col">
                    <div class="rex-column rex-first">' . $content_1 . '</div>
                    <div class="rex-column rex-last">' . $content_2 . '</div>
                </div>';

        } else {
            $return .= $content_1;
        }
        $return .= '</div>';
        $return .= '</section>';

        return $return;
    }



    /**
     * Ausgabe des Seitentitels
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
                $subtitle = $fragment->parse('core/navigations/content.tpl');
            } else {
                $subtitle = '';
            }

        } elseif (!is_string($subtitle)) {
            $subtitle = '';
        }

        $title = rex_extension::registerPoint('PAGE_TITLE', $head, ['category_id' => $category_id, 'article_id' => $article_id, 'page' => $page]);

        $return = '<h1>' . $title . '</h1>' . $subtitle;

        echo rex_extension::registerPoint('PAGE_TITLE_SHOWN', '',
            [
                'category_id' => $category_id,
                'article_id' => $article_id,
                'page' => $page
            ]
        );

        return $return;
    }

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
                if ($id == rex_request('clang', 'int')) {
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

        if (rex::getUser()->isAdmin())
            $fragment->setVar('footer', '<a href="' . rex_url::backendPage('system/lang') . '"><span class="rex-icon rex-icon-language"></span>' . rex_i18n::msg('languages_edit') . '</a>', false);

        return $fragment->parse('core/navigations/drop.tpl');
    }
}
