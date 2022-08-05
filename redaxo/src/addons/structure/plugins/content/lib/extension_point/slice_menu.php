<?php

/**
 * @package redaxo\structure\content
 */
class rex_extension_point_slice_menu extends rex_extension_point
{
    public const NAME = 'SLICE_MENU';

    /** @var array{label?: string, url?: string, attributes?: array{class: string[], title: string}} */
    private $menuEditAction = [];
    /** @var array{label?: string, url?: string, attributes?: array{class: string[], title: string, data-confirm: string}} */
    private $menuDeleteAction = [];
    /** @var array{label?: string, url?: string, attributes?: array{class: string[]}} */
    private $menuStatusAction = [];
    /** @var array{hidden_label?: string, url?: string, icon?: string, attributes?: array{class: string[], title: string}} */
    private $menuMoveupAction = [];
    /** @var array{hidden_label?: string, url?: string, icon?: string, attributes?: array{class: string[], title: string}} */
    private $menuMovedownAction = [];
    /** @var array */
    private $additionalActions = [];

    /** @var rex_context */
    private $context;
    /** @var string */
    private $fragment;

    /** @var int */
    private $articleId;
    /** @var int */
    private $clang;
    /** @var int */
    private $ctype;
    /** @var int */
    private $moduleId;
    /** @var int */
    private $sliceId;

    /** @var bool */
    private $hasPerm;

    /**
     * @param array{label?: string, url?: string, attributes?: array{class: string[], title: string}} $menuEditAction
     * @param array{label?: string, url?: string, attributes?: array{class: string[], title: string, data-confirm: string}} $menuDeleteAction
     * @param array{label?: string, url?: string, attributes?: array{class: string[]}} $menuStatusAction
     * @param array{hidden_label?: string, url?: string, icon?: string, attributes?: array{class: string[], title: string}} $menuMoveupAction
     * @param array{hidden_label?: string, url?: string, icon?: string, attributes?: array{class: string[], title: string}} $menuMovedownAction
     */
    public function __construct(
        array $menuEditAction,
        array $menuDeleteAction,
        array $menuStatusAction,
        array $menuMoveupAction,
        array $menuMovedownAction,
        rex_context $context,
        string $fragment,
        int $articleId,
        int $clang,
        int $ctype,
        int $moduleId,
        int $sliceId,
        bool $hasPerm
    ) {
        parent::__construct(self::NAME);

        $this->menuEditAction = $menuEditAction;
        $this->menuDeleteAction = $menuDeleteAction;
        $this->menuStatusAction = $menuStatusAction;
        $this->menuMoveupAction = $menuMoveupAction;
        $this->menuMovedownAction = $menuMovedownAction;

        $this->context = $context;
        $this->fragment = $fragment;

        $this->articleId = $articleId;
        $this->clang = $clang;
        $this->ctype = $ctype;
        $this->moduleId = $moduleId;
        $this->sliceId = $sliceId;

        $this->hasPerm = $hasPerm;
    }

    /**
     * @return array{label?: string, url?: string, attributes?: array{class: string[], title: string}}
     */
    public function getMenuEditAction(): array
    {
        return $this->menuEditAction;
    }

    /**
     * @param array{label?: string, url?: string, attributes?: array{class: string[], title: string}} $menuEditAction
     */
    public function setMenuEditAction(array $menuEditAction): void
    {
        $this->menuEditAction = $menuEditAction;
    }

    /**
     * @return array{label?: string, url?: string, attributes?: array{class: string[], title: string, data-confirm: string}}
     */
    public function getMenuDeleteAction(): array
    {
        return $this->menuDeleteAction;
    }

    /**
     * @param array{label?: string, url?: string, attributes?: array{class: string[], title: string, data-confirm: string}} $menuDeleteAction
     */
    public function setMenuDeleteAction(array $menuDeleteAction): void
    {
        $this->menuDeleteAction = $menuDeleteAction;
    }

    /**
     * @return array{label?: string, url?: string, attributes?: array{class: string[]}}
     */
    public function getMenuStatusAction(): array
    {
        return $this->menuStatusAction;
    }

    /**
     * @param array{label?: string, url?: string, attributes?: array{class: string[]}} $menuStatusAction
     */
    public function setMenuStatusAction(array $menuStatusAction): void
    {
        $this->menuStatusAction = $menuStatusAction;
    }

    /**
     * @return array{hidden_label?: string, url?: string, icon?: string, attributes?: array{class: string[], title: string}}
     */
    public function getMenuMoveupAction(): array
    {
        return $this->menuMoveupAction;
    }

    /**
     * @param array{hidden_label?: string, url?: string, icon?: string, attributes?: array{class: string[], title: string}} $menuMoveupAction
     */
    public function setMenuMoveupAction(array $menuMoveupAction): void
    {
        $this->menuMoveupAction = $menuMoveupAction;
    }

    /**
     * @return array{hidden_label?: string, url?: string, icon?: string, attributes?: array{class: string[], title: string}}
     */
    public function getMenuMovedownAction(): array
    {
        return $this->menuMovedownAction;
    }

    /**
     * @param array{hidden_label?: string, url?: string, icon?: string, attributes?: array{class: string[], title: string}} $menuMovedownAction
     */
    public function setMenuMovedownAction(array $menuMovedownAction): void
    {
        $this->menuMovedownAction = $menuMovedownAction;
    }

    public function getAdditionalActions(): array
    {
        // ----- EXTENSION POINT / for BC reasons we wrap the old and pre-existing EP here
        $menuItemsEp = [];

        $menuItemsEp = rex_extension::registerPoint(new rex_extension_point(
                'STRUCTURE_CONTENT_SLICE_MENU',
                $menuItemsEp,
                [
                    'article_id' => $this->articleId,
                    'clang' => $this->clang,
                    'ctype' => $this->ctype,
                    'module_id' => $this->moduleId,
                    'slice_id' => $this->sliceId,
                    'perm' => $this->hasPerm,
                ]
            ));

        return array_merge($this->additionalActions, $menuItemsEp);
    }

    public function setAdditionalActions(array $additionalActions): void
    {
        $this->additionalActions = $additionalActions;
    }

    public function addAdditionalActions(array $additionalActions): void
    {
        $this->additionalActions = array_merge($this->additionalActions, $additionalActions);
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
        return $this->articleId;
    }

    public function getClangId(): int
    {
        return $this->clang;
    }

    public function getCtypeId(): int
    {
        return $this->ctype;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function getSliceId(): int
    {
        return $this->sliceId;
    }

    public function hasPerm(): bool
    {
        return $this->hasPerm;
    }
}
