<?php

/**
 * @package redaxo\structure\content
 */
class rex_extension_point_slice_menu extends rex_extension_point
{
    public const NAME = 'SLICE_MENU';

    /**
     * @var array{label: string, url: string, attributes: array{class: string[], title: string}}
     */
    private $menu_edit_action = [];
    /**
     * @var array{label: string, url: string, attributes: array{class: string[], title: string, data-confirm: string}}
     */
    private $menu_delete_action = [];
    /**
     * @var array{label: string, url: string, attributes: array{class: string[]}}
     */
    private $menu_status_action = [];
    /**
     * @var array{hidden_label: string, url: string, attributes: array{class: string[], title: string>, icon: string}}
     */
    private $menu_moveup_action = [];
    /**
     * @var array{hidden_label: string, url: string, attributes: array{class: string[], title: string>, icon: string}}
     */
    private $menu_movedown_action = [];

    /** @var rex_context */
    private $context;
    /** @var string */
    private $fragment;

    /** @var int */
    private $article_id;
    /** @var int */
    private $clang;
    /** @var int */
    private $ctype;
    /** @var int */
    private $module_id;
    /** @var int */
    private $slice_id;

    /** @var bool */
    private $has_perm;

    /**
     * @param array{label: string, url: string, attributes: array{class: string[], title: string}}       $menu_edit_action
     * @param array{label: string, url: string, attributes: array{class: string[], title: string, data-confirm: string}}       $menu_delete_action
     * @param array{label: string, url: string, attributes: array{class: string[]}}       $menu_status_action
     * @param array{hidden_label: string, url: string, attributes: array{class: string[], title: string>, icon: string}}       $menu_moveup_action
     * @param array{hidden_label: string, url: string, attributes: array{class: string[], title: string>, icon: string}}       $menu_movedown_action
     */
    public function __construct(
        array $menu_edit_action,
        array $menu_delete_action,
        array $menu_status_action,
        array $menu_moveup_action,
        array $menu_movedown_action,
        rex_context $context,
        string $fragment,
        int $article_id,
        int $clang,
        int $ctype,
        int $module_id,
        int $slice_id,
        bool $has_perm,
        $subject = null,
        array $params = [],
        $readonly = false
    ) {
        // for BC 'simple' attach params
        $params['article_id'] = $article_id;
        $params['clang'] = $clang;
        $params['ctype'] = $ctype;
        $params['module_id'] = $module_id;
        $params['slice_id'] = $slice_id;
        $params['perm'] = $has_perm;

        parent::__construct(self::NAME, $subject, $params, $readonly);

        $this->menu_edit_action = $menu_edit_action;
        $this->menu_delete_action = $menu_delete_action;
        $this->menu_status_action = $menu_status_action;
        $this->menu_moveup_action = $menu_moveup_action;
        $this->menu_movedown_action = $menu_movedown_action;

        $this->context = $context;
        $this->fragment = $fragment;

        $this->article_id = $article_id;
        $this->clang = $clang;
        $this->ctype = $ctype;
        $this->module_id = $module_id;
        $this->slice_id = $slice_id;

        $this->has_perm = $has_perm;
    }

    /**
     * @return array{label: string, url: string, attributes: array{class: string[], title: string}}
     */
    public function getMenuEditAction(): array
    {
        return $this->menu_edit_action;
    }

    /**
     * @param array{label: string, url: string, attributes: array{class: string[], title: string}}       $menu_edit_action
     */
    public function setMenuEditAction(array $menu_edit_action): void
    {
        $this->menu_edit_action = $menu_edit_action;
    }

    /**
     * @return array{label: string, url: string, attributes: array{class: string[], title: string, data-confirm: string}}
     */
    public function getMenuDeleteAction(): array
    {
        return $this->menu_delete_action;
    }

    /**
     * @param array{label: string, url: string, attributes: array{class: string[], title: string, data-confirm: string}}       $menu_delete_action
     */
    public function setMenuDeleteAction(array $menu_delete_action): void
    {
        $this->menu_delete_action = $menu_delete_action;
    }

    /**
     * @return array{label: string, url: string, attributes: array{class: string[]}}
     */
    public function getMenuStatusAction(): array
    {
        return $this->menu_status_action;
    }

    /**
     * @param array{label: string, url: string, attributes: array{class: string[]}}       $menu_status_action
     */
    public function setMenuStatusAction(array $menu_status_action): void
    {
        $this->menu_status_action = $menu_status_action;
    }

    /**
     * @return array{hidden_label: string, url: string, attributes: array{class: string[], title: string>, icon: string}}
     */
    public function getMenuMoveupAction(): array
    {
        return $this->menu_moveup_action;
    }

    /**
     * @param array{hidden_label: string, url: string, attributes: array{class: string[], title: string>, icon: string}}       $menu_moveup_action
     */
    public function setMenuMoveupAction(array $menu_moveup_action): void
    {
        $this->menu_moveup_action = $menu_moveup_action;
    }

    /**
     * @return array{hidden_label: string, url: string, attributes: array{class: string[], title: string>, icon: string}}
     */
    public function getMenuMovedownAction(): array
    {
        return $this->menu_movedown_action;
    }

    /**
     * @param array{hidden_label: string, url: string, attributes: array{class: string[], title: string>, icon: string}}       $menu_movedown_action
     */
    public function setMenuMovedownAction(array $menu_movedown_action): void
    {
        $this->menu_movedown_action = $menu_movedown_action;
    }

    /**
     * @return array
     */
    public function getAdditionalActions()
    {
        // ----- EXTENSION POINT / for BC reasons we wrap the old and pre-existing EP here
        $menu_items_ep = [];

        return rex_extension::registerPoint(new rex_extension_point(
            'STRUCTURE_CONTENT_SLICE_MENU',
            $menu_items_ep,
            [
                'article_id' => $this->article_id,
                'clang' => $this->clang,
                'ctype' => $this->ctype,
                'module_id' => $this->module_id,
                'slice_id' => $this->slice_id,
                'perm' => $this->has_perm,
            ]
        ));
    }

    public function getContext(): rex_context
    {
        return $this->context;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getArticleId(): int
    {
        return $this->article_id;
    }

    public function getClang(): int
    {
        return $this->clang;
    }

    public function getCtype(): int
    {
        return $this->ctype;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function getSliceId(): int
    {
        return $this->slice_id;
    }

    public function hasPerm(): bool
    {
        return $this->has_perm;
    }
}
