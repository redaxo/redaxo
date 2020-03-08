<?php

/**
 * Klasse regelt den Zugriff auf Artikelinhalte.
 * Alle benötigten Daten werden von der DB bezogen.
 *
 * @package redaxo\structure\content
 */
class rex_article_content_base
{
    public $warning;
    public $info;
    public $debug;

    public $template_id;
    public $template_attributes;

    protected $category_id;
    protected $article_id;
    protected $slice_id;
    protected $getSlice;
    protected $mode;
    protected $function;

    protected $ctype;
    protected $clang;

    protected $eval;

    protected $slice_revision;

    protected $ARTICLE;

    public function __construct($article_id = null, $clang = null)
    {
        $this->article_id = 0;
        $this->template_id = 0;
        $this->ctype = -1; // zeigt alles an
        $this->slice_id = 0;
        $this->getSlice = 0;

        $this->mode = 'view';
        $this->eval = false;

        $this->slice_revision = 0;

        $this->debug = false;

        if (null !== $clang) {
            $this->setCLang($clang);
        } else {
            $this->setClang(rex_clang::getCurrentId());
        }

        // ----- EXTENSION POINT
        rex_extension::registerPoint(new rex_extension_point('ART_INIT', '', [
            'article' => $this,
            'article_id' => $article_id,
            'clang' => $this->clang,
        ]));

        if (null !== $article_id) {
            $this->setArticleId($article_id);
        }
    }

    /**
     * @return object
     */
    protected function getSqlInstance()
    {
        if (!is_object($this->ARTICLE)) {
            $this->ARTICLE = rex_sql::factory();
            if ($this->debug) {
                $this->ARTICLE->setDebug();
            }
        }
        return $this->ARTICLE;
    }

    public function setSliceRevision($sr)
    {
        $this->slice_revision = (int) $sr;
    }

    // ----- Slice Id setzen für Editiermodus

    public function setSliceId($value)
    {
        $this->slice_id = $value;
    }

    public function setClang($value)
    {
        if (!rex_clang::exists($value)) {
            $value = rex_clang::getCurrentId();
        }
        $this->clang = $value;
    }

    public function getArticleId()
    {
        return $this->article_id;
    }

    public function getClangId()
    {
        return $this->clang;
    }

    /**
     * @deprecated since redaxo 5.6, use getClangId() instead
     */
    public function getClang()
    {
        return $this->clang;
    }

    /**
     * @return bool
     */
    public function setArticleId($article_id)
    {
        $article_id = (int) $article_id;
        $this->article_id = $article_id;

        // ---------- select article
        $sql = $this->getSqlInstance();
        $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE ' . rex::getTablePrefix() . 'article.id=? AND clang_id=?', [$article_id, $this->clang]);

        if (1 == $sql->getRows()) {
            $this->template_id = $this->getValue('template_id');
            $this->category_id = $this->getValue('category_id');
            return true;
        }

        $this->article_id = 0;
        $this->template_id = 0;
        $this->category_id = 0;
        return false;
    }

    public function setTemplateId($template_id)
    {
        $this->template_id = $template_id;
    }

    public function getTemplateId()
    {
        return $this->template_id;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function setFunction($function)
    {
        $this->function = $function;
    }

    public function setEval($value)
    {
        if ($value) {
            $this->eval = true;
        } else {
            $this->eval = false;
        }
    }

    protected function correctValue($value)
    {
        if ('category_id' == $value) {
            if (1 != $this->getValue('startarticle')) {
                $value = 'parent_id';
            } else {
                $value = 'id';
            }
        } elseif ('article_id' == $value) {
            $value = 'id';
        }

        return $value;
    }

    protected function _getValue($value)
    {
        $value = $this->correctValue($value);

        // use same timestamp format like in frontend via `rex_article`
        if (in_array($value, ['createdate', 'updatedate'], true)) {
            return $this->getSqlInstance()->getDateTimeValue($value);
        }

        return $this->getSqlInstance()->getValue($value);
    }

    public function getValue($value)
    {
        // damit alte rex_article felder wie teaser, online_from etc
        // noch funktionieren
        // gleicher BC code nochmals in rex_structure_element::getValue
        foreach (['', 'art_', 'cat_'] as $prefix) {
            $val = $prefix . $value;
            if ($this->_hasValue($val)) {
                return $this->_getValue($val);
            }
        }
        return '[' . $value . ' not found]';
    }

    public function hasValue($value)
    {
        foreach (['', 'art_', 'cat_'] as $prefix) {
            $val = $prefix . $value;
            if ($this->_hasValue($val)) {
                return true;
            }
        }
        return false;
    }

    private function _hasValue($value)
    {
        return $this->getSqlInstance()->hasValue($this->correctValue($value));
    }

    /**
     * Outputs a slice.
     *
     * @param rex_sql $artDataSql    A rex_sql instance containing all slice and module data
     * @param int     $moduleIdToAdd The id of the module, which was selected using the ModuleSelect
     *
     * @return string
     */
    protected function outputSlice(rex_sql $artDataSql, $moduleIdToAdd)
    {
        $output = rex_extension::registerPoint(new rex_extension_point(
            'SLICE_OUTPUT',
            $artDataSql->getValue(rex::getTablePrefix() . 'module.output'),
            [
                'article_id' => $this->article_id,
                'clang' => $this->clang,
                'slice_data' => $artDataSql,
            ]
        ));
        $output = $this->replaceVars($artDataSql, $output);

        return $this->getStreamOutput('module/' . $artDataSql->getValue(rex::getTablePrefix() . 'module.id') . '/output', $output);
    }

    /**
     * Returns the content of the given slice-id.
     *
     * @param int $sliceId A article-slice id
     *
     * @return string
     */
    public function getSlice($sliceId)
    {
        $oldEval = $this->eval;
        $this->setEval(true);

        $this->getSlice = $sliceId;
        $sliceContent = $this->getArticle();
        $this->getSlice = 0;

        $this->setEval($oldEval);
        return $this->replaceLinks($sliceContent);
    }

    /**
     * Returns the content of the article of the given ctype. If no ctype is given, content of all ctypes is returned.
     *
     * @param int $curctype The ctype to fetch, or -1 for all ctypes
     *
     * @return string
     */
    public function getArticle($curctype = -1)
    {
        $this->ctype = $curctype;

        if (0 == $this->article_id && 0 == $this->getSlice) {
            return rex_i18n::msg('no_article_available');
        }

        $articleLimit = '';
        if (0 != $this->article_id) {
            $articleLimit = ' AND ' . rex::getTablePrefix() . 'article_slice.article_id=' . (int) $this->article_id;
        }

        $sliceLimit = '';
        if (0 != $this->getSlice) {
            $sliceLimit = ' AND ' . rex::getTablePrefix() . "article_slice.id = '" . ((int) $this->getSlice) . "' ";
        }
        if ('edit' !== $this->mode) {
            $sliceLimit .= ' AND ' . rex::getTablePrefix() . 'article_slice.status = 1';
        }

        // ----- start: article caching
        ob_start();
        ob_implicit_flush(0);
        $module_id = rex_request('module_id', 'int');

        // ---------- alle teile/slices eines artikels auswaehlen
        $query = 'SELECT ' . rex::getTablePrefix() . 'module.id, ' . rex::getTablePrefix() . 'module.key, ' . rex::getTablePrefix() . 'module.name, ' . rex::getTablePrefix() . 'module.output, ' . rex::getTablePrefix() . 'module.input, ' . rex::getTablePrefix() . 'article_slice.*, ' . rex::getTablePrefix() . 'article.parent_id
                        FROM
                            ' . rex::getTablePrefix() . 'article_slice
                        LEFT JOIN ' . rex::getTablePrefix() . 'module ON ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id
                        LEFT JOIN ' . rex::getTablePrefix() . 'article ON ' . rex::getTablePrefix() . 'article_slice.article_id=' . rex::getTablePrefix() . 'article.id
                        WHERE
                            ' . rex::getTablePrefix() . "article_slice.clang_id='" . $this->clang . "' AND
                            " . rex::getTablePrefix() . "article.clang_id='" . $this->clang . "' AND
                            " . rex::getTablePrefix() . "article_slice.revision='" . $this->slice_revision . "'
                            " . $articleLimit . '
                            ' . $sliceLimit . '
                            ORDER BY ' . rex::getTablePrefix() . 'article_slice.priority';

        $query = rex_extension::registerPoint(new rex_extension_point(
            'ART_SLICES_QUERY',
            $query,
            ['article' => $this]
        ));

        $artDataSql = rex_sql::factory();
        $artDataSql->setDebug($this->debug);
        $artDataSql->setQuery($query);

        // pre hook
        $articleContent = '';
        $articleContent = $this->preArticle($articleContent, $module_id);

        // ---------- SLICES AUSGEBEN

        $prevCtype = null;
        $artDataSql->reset();
        $rows = $artDataSql->getRows();
        for ($i = 0; $i < $rows; ++$i) {
            $sliceId = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.id');
            $sliceCtypeId = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype_id');
            $sliceModuleId = $artDataSql->getValue(rex::getTablePrefix() . 'module.id');

            // ----- ctype unterscheidung
            if ('edit' != $this->mode && !$this->eval) {
                if (0 == $i) {
                    $articleContent = "<?php if (\$this->ctype == '" . $sliceCtypeId . "' || (\$this->ctype == '-1')) { \n";
                } elseif (isset($prevCtype) && $sliceCtypeId != $prevCtype) {
                    // ----- zwischenstand: ctype .. wenn ctype neu dann if
                    $articleContent .= "\n } if(\$this->ctype == '" . $sliceCtypeId . "' || \$this->ctype == '-1'){ \n";
                }
            }

            // ------------- EINZELNER SLICE - AUSGABE
            $slice_content = $this->outputSlice(
                $artDataSql,
                $module_id
            );
            // --------------- ENDE EINZELNER SLICE

            // --------------- EP: SLICE_SHOW
            $slice_content = rex_extension::registerPoint(new rex_extension_point(
                'SLICE_SHOW',
                $slice_content,
                [
                    'article_id' => $this->article_id,
                    'clang' => $this->clang,
                    'ctype' => $sliceCtypeId,
                    'module_id' => $sliceModuleId,
                    'slice_id' => $sliceId,
                    'function' => $this->function,
                    'function_slice_id' => $this->slice_id,
                    'sql' => $artDataSql,
                ]
            ));

            // ---------- slice in ausgabe speichern wenn ctype richtig
            if (-1 == $this->ctype || $this->ctype == $sliceCtypeId) {
                $articleContent .= $slice_content;
            }

            $prevCtype = $sliceCtypeId;

            $artDataSql->flushValues();
            $artDataSql->next();
        }

        // ----- end: ctype unterscheidung
        if ('edit' != $this->mode && !$this->eval && $i > 0) {
            $articleContent .= "\n } ?>";
        }

        // ----- post hook
        $articleContent = $this->postArticle($articleContent, $module_id);

        // -------------------------- schreibe content
        echo $articleContent;

        // ----- end: article caching
        $CONTENT = ob_get_clean();

        return $CONTENT;
    }

    /**
     * Method which gets called, before the slices of the article are processed.
     *
     * @param string $articleContent The content of the article
     * @param int    $module_id      A module id
     *
     * @return string
     */
    protected function preArticle($articleContent, $module_id)
    {
        // nichts tun
        return $articleContent;
    }

    /**
     * Method which gets called, after all slices have been processed.
     *
     * @param string $articleContent The content of the article
     * @param int    $module_id      A module id
     *
     * @return string
     */
    protected function postArticle($articleContent, $module_id)
    {
        // nichts tun
        return $articleContent;
    }

    // ----- Template inklusive Artikel zurückgeben
    public function getArticleTemplate()
    {
        if (0 != $this->template_id && 0 != $this->article_id) {
            ob_start();
            ob_implicit_flush(0);

            $TEMPLATE = new rex_template($this->template_id);
            $tplContent = $this->replaceCommonVars($TEMPLATE->getTemplate());
            require rex_stream::factory('template/' . $this->template_id, $tplContent);

            $CONTENT = ob_get_clean();

            $CONTENT = $this->replaceLinks($CONTENT);
        } else {
            $CONTENT = 'no template';
        }

        return $CONTENT;
    }

    /**
     * @return string
     */
    protected function getStreamOutput($path, $content)
    {
        if (!$this->eval) {
            $key = 'EOD_' . strtoupper(sha1(time()));
            return "require rex_stream::factory('$path', \n<<<'$key'\n$content\n$key\n);\n";
        }

        ob_start();
        ob_implicit_flush(0);

        $__stream = rex_stream::factory($path, $content);

        $sandbox = function () use ($__stream) {
            require $__stream;
        };
        $sandbox();

        $CONTENT = ob_get_clean();

        return $CONTENT;
    }

    // ----- Modulvariablen werden ersetzt

    /**
     * @return string
     */
    protected function replaceVars(rex_sql $sql, $content)
    {
        $content = $this->replaceCommonVars($content);
        $content = str_replace(
            [
                'REX_MODULE_ID',
                'REX_MODULE_KEY',
                'REX_SLICE_ID',
                'REX_CTYPE_ID',
            ],
            [
                (int) $sql->getValue('module_id'),
                $sql->getValue(rex::getTable('module') . '.key'),
                (int) $sql->getValue(rex::getTable('article_slice') . '.id'),
                (int) $sql->getValue('ctype_id'),
            ],
            $content
        );

        $content = $this->replaceObjectVars($sql, $content);

        return $content;
    }

    // ----- REX_VAR Ersetzungen

    /**
     * @return string
     */
    protected function replaceObjectVars(rex_sql $sql, $content)
    {
        $sliceId = $sql->getValue(rex::getTablePrefix() . 'article_slice.id');

        if ('edit' == $this->mode) {
            $env = rex_var::ENV_BACKEND;
            if (('add' == $this->function && null == $sliceId) || ('edit' == $this->function && $sliceId == $this->slice_id)) {
                $env = $env | rex_var::ENV_INPUT;
            }
        } else {
            $env = rex_var::ENV_FRONTEND;
        }
        $content = rex_var::parse($content, $env, 'module', $sql);

        return $content;
    }

    // ---- Artikelweite globale variablen werden ersetzt

    /**
     * @return string
     */
    public function replaceCommonVars($content, $template_id = null)
    {
        static $user_id = null;
        static $user_login = null;

        // UserId gibts nur im Backend
        if (null === $user_id) {
            if (rex::getUser()) {
                $user_id = rex::getUser()->getId();
                $user_login = rex::getUser()->getLogin();
            } else {
                $user_id = '';
                $user_login = '';
            }
        }

        if (!$template_id) {
            $template_id = $this->getTemplateId();
        }

        static $search = [
            'REX_ARTICLE_ID',
            'REX_CATEGORY_ID',
            'REX_CLANG_ID',
            'REX_TEMPLATE_ID',
            'REX_USER_ID',
            'REX_USER_LOGIN',
        ];

        $replace = [
            $this->article_id,
            $this->category_id,
            $this->clang,
            $template_id,
            $user_id,
            $user_login,
        ];

        // calculating the key takes an additional sql query... execute the query only when we are sure the var is used
        if (false !== strpos($content, 'REX_TEMPLATE_KEY')) {
            $template = new rex_template($template_id);
            $content = str_replace('REX_TEMPLATE_KEY', $template->getKey(), $content);
        }

        return str_replace($search, $replace, $content);
    }

    /**
     * @return string
     */
    protected function replaceLinks($content)
    {
        $result = preg_replace_callback(
            '@redaxo://(\d+)(?:-(\d+))?/?@i',
            function ($matches) {
                return rex_getUrl($matches[1], $matches[2] ?? (int) $this->clang);
            },
            $content
        );

        if (null === $result) {
            throw new LogicException('Error while replacing links.');
        }

        return $result;
    }
}
