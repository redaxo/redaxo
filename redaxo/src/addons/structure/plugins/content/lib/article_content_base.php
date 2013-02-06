<?php

/**
 * Klasse regelt den Zugriff auf Artikelinhalte.
 * Alle benötigten Daten werden von der DB bezogen.
 *
 * @package redaxo5
 */

class rex_article_content_base
{
  public
    $warning,
    $info,
    $debug,

    $template_id,
    $template_attributes;

  protected
    $category_id,
    $article_id,
    $slice_id,
    $getSlice,
    $mode,
    $function,

    $ctype,
    $clang,

    $eval,

    $slice_revision,

    $ARTICLE;

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

    if ($clang !== null)
      $this->setCLang($clang);
    else
      $this->setClang(rex_clang::getCurrentId());

    // ----- EXTENSION POINT
    rex_extension::registerPoint('ART_INIT', '',
      array(
        'article' => &$this,
        'article_id' => $article_id,
        'clang' => $this->clang
      )
    );

    if ($article_id !== null)
      $this->setArticleId($article_id);
  }

  protected function getSqlInstance()
  {
    if (!is_object($this->ARTICLE)) {
      $this->ARTICLE = rex_sql::factory();
      if ($this->debug)
        $this->ARTICLE->debugsql = 1;
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
    if (!rex_clang::exists($value))
      $value = rex_clang::getCurrentId();
    $this->clang = $value;
  }

  public function getArticleId()
  {
    return $this->article_id;
  }

  public function getClang()
  {
    return $this->clang;
  }

  public function setArticleId($article_id)
  {
    $article_id = (int) $article_id;
    $this->article_id = $article_id;

    // ---------- select article
    $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE ' . rex::getTablePrefix() . "article.id='$article_id' AND clang='" . $this->clang . "'";
    $sql = $this->getSqlInstance();
    $sql->setQuery($qry);

    if ($sql->getRows() == 1) {
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
    if ($value) $this->eval = true;
    else $this->eval = false;
  }

  protected function correctValue($value)
  {
    if ($value == 'category_id') {
      if ($this->getValue('startpage') != 1)
        $value = 're_id';
      else
        $value = 'id';
    } elseif ($value == 'article_id') {
      $value = 'id';
    }

    return $value;
  }

  protected function _getValue($value)
  {
    $value = $this->correctValue($value);

    return $this->getSqlInstance()->getValue($value);
  }

  public function getValue($value)
  {
    // damit alte rex_article felder wie teaser, online_from etc
    // noch funktionieren
    // gleicher BC code nochmals in rex_structure_element::getValue
    foreach (array('', 'art_', 'cat_') as $prefix) {
      $val = $prefix . $value;
      if ($this->hasValue($val)) {
        return $this->_getValue($val);
      }
    }
    return '[' . $value . ' not found]';
  }

  public function hasValue($value)
  {
    return $this->getSqlInstance()->hasValue($this->correctValue($value));
  }

  /**
   * Outputs a slice
   *
   * @param rex_sql $artDataSql    A rex_sql instance containing all slice and module data
   * @param integer $moduleIdToAdd The id of the module, which was selected using the ModuleSelect
   * @return string
   */
  protected function outputSlice(rex_sql $artDataSql, $moduleIdToAdd)
  {
    $output = $this->replaceVars($artDataSql, $artDataSql->getValue(rex::getTablePrefix() . 'module.output'));

    return $this->getStreamOutput('module/' . $artDataSql->getValue(rex::getTablePrefix() . 'module.id') . '/output', $output);
  }


  /**
   * Returns the content of the given slice-id.
   *
   * @param integer $sliceId A article-slice id
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
   * @param integer $curctype The ctype to fetch, or -1 for all ctypes
   * @return string
   */
  public function getArticle($curctype = -1)
  {
    global $REX;

    $this->ctype = $curctype;

    if ($this->article_id == 0 && $this->getSlice == 0) {
      return rex_i18n::msg('no_article_available');
    }

    $articleLimit = '';
    if ($this->article_id != 0) {
      $articleLimit = ' AND ' . rex::getTablePrefix() . 'article_slice.article_id=' . $this->article_id;
    }

    $sliceLimit = '';
    if ($this->getSlice != 0) {
      $sliceLimit = ' AND ' . rex::getTablePrefix() . "article_slice.id = '" . ((int) $this->getSlice) . "' ";
    }

    // ----- start: article caching
    ob_start();
    ob_implicit_flush(0);
    $module_id = rex_request('module_id', 'int');

    // ---------- alle teile/slices eines artikels auswaehlen
    $sql = 'SELECT ' . rex::getTablePrefix() . 'module.id, ' . rex::getTablePrefix() . 'module.name, ' . rex::getTablePrefix() . 'module.output, ' . rex::getTablePrefix() . 'module.input, ' . rex::getTablePrefix() . 'article_slice.*, ' . rex::getTablePrefix() . 'article.re_id
            FROM
              ' . rex::getTablePrefix() . 'article_slice
            LEFT JOIN ' . rex::getTablePrefix() . 'module ON ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id
            LEFT JOIN ' . rex::getTablePrefix() . 'article ON ' . rex::getTablePrefix() . 'article_slice.article_id=' . rex::getTablePrefix() . 'article.id
            WHERE
              ' . rex::getTablePrefix() . "article_slice.clang='" . $this->clang . "' AND
              " . rex::getTablePrefix() . "article.clang='" . $this->clang . "' AND
              " . rex::getTablePrefix() . "article_slice.revision='" . $this->slice_revision . "'
              " . $articleLimit . '
              ' . $sliceLimit . '
              ORDER BY ' . rex::getTablePrefix() . 'article_slice.prior';

    $artDataSql = rex_sql::factory();
    if ($this->debug)
      $artDataSql->debugsql = 1;

    $artDataSql->setQuery($sql);

    // pre hook
    $articleContent = '';
    $articleContent = $this->preArticle($articleContent, $module_id);

    // ---------- SLICES AUSGEBEN

    $prevCtype = null;
    $artDataSql->reset();
    $rows = $artDataSql->getRows();
    for ($i = 0; $i < $rows; ++$i) {
      $sliceId       = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.id');
      $sliceCtypeId  = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype');
      $sliceModuleId = $artDataSql->getValue(rex::getTablePrefix() . 'module.id');

      // ----- ctype unterscheidung
      if ($this->mode != 'edit' && !$this->eval && $i == 0)
        $articleContent = "<?php if (\$this->ctype == '" . $sliceCtypeId . "' || (\$this->ctype == '-1')) { \n";

      // ------------- EINZELNER SLICE - AUSGABE
      $slice_content = $this->outputSlice(
        $artDataSql,
        $module_id
      );
      // --------------- ENDE EINZELNER SLICE

      // --------------- EP: SLICE_SHOW
      $slice_content = rex_extension::registerPoint(
        'SLICE_SHOW',
        $slice_content,
        array(
          'article_id' => $this->article_id,
          'clang' => $this->clang,
          'ctype' => $sliceCtypeId,
          'module_id' => $sliceModuleId,
          'slice_id' => $sliceId,
          'function' => $this->function,
          'function_slice_id' => $this->slice_id
        )
      );

      // ---------- slice in ausgabe speichern wenn ctype richtig
      if ($this->ctype == -1 || $this->ctype == $sliceCtypeId) {
        $articleContent .= $slice_content;
      }

      // ----- zwischenstand: ctype .. wenn ctype neu dann if
      if ($this->mode != 'edit' && !$this->eval && isset($prevCtype) && $sliceCtypeId != $prevCtype) {
        $articleContent .= "\n } if(\$this->ctype == '" . $sliceCtypeId . "' || \$this->ctype == '-1'){ \n";
      }

      $prevCtype = $sliceCtypeId;

      $artDataSql->flushValues();
      $artDataSql->next();
    }

    // ----- end: ctype unterscheidung
    if ($this->mode != 'edit' && !$this->eval && $i > 0) $articleContent .= "\n } ?>";


    // ----- post hook
    $articleContent = $this->postArticle($articleContent, $module_id);

    // -------------------------- schreibe content
    echo $this->replaceLinks($articleContent);

    // ----- end: article caching
    $CONTENT = ob_get_contents();
    ob_end_clean();

    return $CONTENT;
  }

  /**
   * Method which gets called, before the slices of the article are processed
   *
   * @param string  $articleContent The content of the article
   * @param integer $module_id      A module id
   * @return string
   */
  protected function preArticle($articleContent, $module_id)
  {
    // nichts tun
    return $articleContent;
  }

  /**
   * Method which gets called, after all slices have been processed
   *
   * @param string  $articleContent The content of the article
   * @param integer $module_id      A module id
   * @®eturn string
   */
  protected function postArticle($articleContent, $module_id)
  {
    // nichts tun
    return $articleContent;
  }

  // ----- Template inklusive Artikel zurückgeben
  public function getArticleTemplate()
  {
    // global $REX hier wichtig, damit in den Artikeln die Variable vorhanden ist!
    global $REX;

    if ($this->template_id != 0 && $this->article_id != 0) {
      ob_start();
      ob_implicit_flush(0);

      $TEMPLATE = new rex_template($this->template_id);
      $tplContent = $this->replaceCommonVars($TEMPLATE->getTemplate());
      require rex_stream::factory('template/' . $this->template_id, $tplContent);

      $CONTENT = ob_get_contents();
      ob_end_clean();
    } else {
      $CONTENT = 'no template';
    }

    return $CONTENT;
  }

  protected function getStreamOutput($path, $content)
  {
    global $REX;

    if (!$this->eval) {
      return "require rex_stream::factory('$path', \n<<<'STREAM_CONTENT'\n" . $content . "\nSTREAM_CONTENT\n);\n";
    }

    ob_start();
    ob_implicit_flush(0);
    require rex_stream::factory($path, $content);
    $CONTENT = ob_get_contents();
    ob_end_clean();

    return $CONTENT;
  }

  // ----- Modulvariablen werden ersetzt
  protected function replaceVars(rex_sql $sql, $content)
  {
    $content = $this->replaceObjectVars($sql, $content);
    $content = $this->replaceCommonVars($content);
    $content = str_replace(
      array(
        'REX_MODULE_ID',
        'REX_SLICE_ID',
        'REX_CTYPE_ID'
      ),
      array(
        (int) $sql->getValue('module_id'),
        (int) $sql->getValue(rex::getTable('article_slice') . '.id'),
        (int) $sql->getValue('ctype')
      ),
      $content
    );
    return $content;
  }

  // ----- REX_VAR Ersetzungen
  protected function replaceObjectVars(rex_sql $sql, $content)
  {
    $tmp = '';
    $sliceId = $sql->getValue(rex::getTablePrefix() . 'article_slice.id');

    if ($this->mode == 'edit') {
      $env = rex_var::ENV_BACKEND;
      if (($this->function == 'add' && $sliceId == null) || ($this->function == 'edit' && $sliceId == $this->slice_id)) {
        $env = $env | rex_var::ENV_INPUT;
      }
    } else {
      $env = rex_var::ENV_FRONTEND;
    }
    $content = rex_var::parse($content, $env, 'module', $sql);

    return $content;
  }

  // ---- Artikelweite globale variablen werden ersetzt
  public function replaceCommonVars($content, $template_id = null)
  {
    static $user_id = null;
    static $user_login = null;

    // UserId gibts nur im Backend
    if ($user_id === null) {
      if (rex::getUser()) {
        $user_id = rex::getUser()->getValue('user_id');
        $user_login = rex::getUser()->getValue('login');
      } else {
        $user_id = '';
        $user_login = '';
      }
    }

    if (!$template_id)
      $template_id = $this->getTemplateId();

    static $search = array(
      'REX_ARTICLE_ID',
      'REX_CATEGORY_ID',
      'REX_CLANG_ID',
      'REX_TEMPLATE_ID',
      'REX_USER_ID',
      'REX_USER_LOGIN'
    );

    $replace = array(
      $this->article_id,
      $this->category_id,
      $this->clang,
      $template_id,
      $user_id,
      $user_login
    );

    return str_replace($search, $replace, $content);
  }

  protected function replaceLinks($content)
  {
    // Hier beachten, dass man auch ein Zeichen nach dem jeweiligen Link mitmatched,
    // damit beim ersetzen von z.b. redaxo://11 nicht auch innerhalb von redaxo://112
    // ersetzt wird
    // siehe dazu: http://forum.redaxo.de/ftopic7563.html

    // -- preg match redaxo://[ARTICLEID]-[CLANG] --
    preg_match_all('@redaxo://([0-9]*)\-([0-9]*)(.){1}/?@im', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      if (empty($match)) continue;

      $url = rex_getURL($match[1], $match[2]);
      $content = str_replace($match[0], $url . $match[3], $content);
    }

    // -- preg match redaxo://[ARTICLEID] --
    preg_match_all('@redaxo://([0-9]*)(.){1}/?@im', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      if (empty($match)) continue;

      $url = rex_getURL($match[1], $this->clang);
      $content = str_replace($match[0], $url . $match[2], $content);
    }

    return $content;
  }
}
