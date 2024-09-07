<?php

namespace Redaxo\Core\View;

use InvalidArgumentException;
use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Backend\Navigation;
use Redaxo\Core\Backend\Page;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Core;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Http\Context;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Translation\I18n;

use function count;
use function is_array;
use function is_string;

class View
{
    /**
     * Returns a toolbar.
     *
     * @param string $content
     * @param string $brand
     * @param string $cssClass
     * @param bool $inverse
     *
     * @return string
     */
    public static function toolbar($content, $brand = null, $cssClass = null, $inverse = false)
    {
        $fragment = new Fragment();
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
        $fragment = new Fragment();
        $fragment->setVar('title', $title, false);
        $fragment->setVar('body', $content, false);
        return $fragment->parse('core/page/section.php');
    }

    /**
     * Returns the formatted title.
     *
     * @param string $head
     * @param string|array|null $subtitle
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function title($head, $subtitle = null)
    {
        if (null !== $subtitle && !is_string($subtitle) && (!is_array($subtitle) || count($subtitle) > 0 && !reset($subtitle) instanceof Page)) {
            throw new InvalidArgumentException('Expecting $subtitle to be a string or an array of Page!');
        }

        if (null === $subtitle) {
            $subtitle = Controller::getPageObject(Controller::getCurrentPagePart(1))->getSubpages();
        }

        if (is_array($subtitle) && count($subtitle) && reset($subtitle) instanceof Page) {
            $nav = Navigation::factory();
            $nav->setHeadline('default', I18n::msg('subnavigation', $head));
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
                $fragment = new Fragment();
                $fragment->setVar('left', $navigation, false);
                $subtitle = $fragment->parse('core/navigations/content.php');
            } else {
                $subtitle = '';
            }
        } elseif (!is_string($subtitle)) {
            $subtitle = '';
        }

        $title = Extension::registerPoint(new ExtensionPoint('PAGE_TITLE', $head));

        $fragment = new Fragment();
        $fragment->setVar('heading', $title, false);
        $fragment->setVar('subtitle', $subtitle, false);
        $return = $fragment->parse('core/page/header.php');

        return $return . Extension::registerPoint(new ExtensionPoint('PAGE_TITLE_SHOWN', ''));
    }

    /**
     * Returns a clang switch.
     *
     * @param bool $asDropDown
     *
     * @return string
     */
    public static function clangSwitch(Context $context, $asDropDown = true)
    {
        if (1 == Language::count()) {
            return '';
        }

        if ($asDropDown && Language::count() >= 4) {
            return self::clangSwitchAsDropdown($context);
        }

        $items = [];
        foreach (Language::getAll() as $id => $clang) {
            if (Core::requireUser()->getComplexPerm('clang')->hasPerm($id)) {
                $icon = $id === $context->getParam('clang') ? '<i class="rex-icon rex-icon-language-active"></i> ' : '<i class="rex-icon rex-icon-language"></i> ';
                $item = [];
                $item['href'] = $context->getUrl(['clang' => $id]);
                $item['title'] = $icon . I18n::translate($clang->getName());
                if ($id === $context->getParam('clang')) {
                    $item['active'] = true;
                }
                $items[] = $item;
            }
        }
        $fragment = new Fragment();
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
    public static function clangSwitchAsButtons(Context $context, $asDropDown = true)
    {
        if (1 == Language::count()) {
            return '';
        }

        if ($asDropDown && Language::count() >= 4) {
            return self::clangSwitchAsDropdown($context);
        }

        $items = [];
        foreach (Language::getAll() as $id => $clang) {
            if (Core::requireUser()->getComplexPerm('clang')->hasPerm($id)) {
                $icon = $clang->isOnline() ? '<i class="rex-icon rex-icon-online"></i> ' : '<i class="rex-icon rex-icon-offline"></i> ';
                $item = [];
                $item['label'] = $icon . I18n::translate($clang->getName());
                $item['url'] = $context->getUrl(['clang' => $id]);
                $item['attributes']['class'][] = 'btn-clang';
                $item['attributes']['title'] = I18n::translate($clang->getName());
                if ($id === $context->getParam('clang')) {
                    $item['attributes']['class'][] = 'active';
                }
                $items[] = $item;
            }
        }

        $fragment = new Fragment();
        $fragment->setVar('buttons', $items, false);
        return '<div class="rex-nav-btn rex-nav-language"><div class="btn-toolbar">' . $fragment->parse('core/buttons/button_group.php') . '</div></div>';
    }

    /**
     * Returns a clang switch.
     *
     * @return string
     */
    public static function clangSwitchAsDropdown(Context $context)
    {
        if (1 == Language::count()) {
            return '';
        }

        $user = Core::requireUser();

        $buttonLabel = '';
        $items = [];
        foreach (Language::getAll() as $id => $clang) {
            if ($user->getComplexPerm('clang')->hasPerm($id)) {
                $item = [];
                $item['title'] = I18n::translate($clang->getName());
                $item['href'] = $context->getUrl(['clang' => $id]);
                if ($id === $context->getParam('clang')) {
                    $item['active'] = true;
                    $buttonLabel = I18n::translate($clang->getName());
                }
                $items[] = $item;
            }
        }

        $fragment = new Fragment();
        $fragment->setVar('class', 'rex-language');
        $fragment->setVar('button_prefix', I18n::msg('language'));
        $fragment->setVar('button_label', $buttonLabel);
        $fragment->setVar('header', I18n::msg('clang_select'));
        $fragment->setVar('items', $items, false);

        if ($user->isAdmin()) {
            $fragment->setVar('footer', '<a href="' . Url::backendPage('system/lang') . '"><i class="fa fa-flag"></i> ' . I18n::msg('languages_edit') . '</a>', false);
        }

        return $fragment->parse('core/dropdowns/dropdown.php');
    }

    /**
     * @internal
     */
    public static function structureBreadcrumb(int $categoryId, int $articleId, int $clang): string
    {
        $navigation = [];

        /** @psalm-suppress RedundantCondition */
        $objectId = $articleId > 0 ? $articleId : $categoryId;
        $object = Article::get($objectId, $clang);
        if ($object) {
            $tree = $object->getParentTree();
            if (!$object->isStartArticle()) {
                $tree[] = $object;
            }
            foreach ($tree as $parent) {
                $id = $parent->getId();
                if (Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($id)) {
                    $n = [];
                    $n['title'] = str_replace(' ', '&nbsp;', escape($parent->getName()));
                    if ($parent->isStartArticle()) {
                        $n['href'] = Url::backendPage('structure', ['category_id' => $id, 'clang' => $clang]);
                    }
                    $navigation[] = $n;
                }
            }
        }

        $title = '<a class="rex-link-expanded" href="' . Url::backendPage('structure', ['category_id' => 0, 'clang' => $clang]) . '"><i class="rex-icon rex-icon-structure-root-level"></i> ' . I18n::msg('root_level') . '</a>';

        $fragment = new Fragment();
        $fragment->setVar('id', 'rex-js-structure-breadcrumb', false);
        $fragment->setVar('title', $title, false);
        $fragment->setVar('items', $navigation, false);
        return $fragment->parse('core/navigations/breadcrumb.php');
    }
}
