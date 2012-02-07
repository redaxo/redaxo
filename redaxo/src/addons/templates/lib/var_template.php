<?php

/**
 * REX_TEMPLATE[2]
 *
 * @package redaxo5
 */

class rex_var_template extends rex_var
{
  protected function getOutput()
  {
    $template_id = $this->getArg('id', 'int', 0, true);

    if($template_id > 0)
    {
      return __CLASS__ .'::getTemplateOutput(require '. __CLASS__ .'::getTemplateStream('. $template_id .', $this))';
    }

    return false;
  }

  static public function getTemplateStream($id, $article = null, $args = '')
  {
    ob_start();
    $tmpl = new rex_template($id);
    $tmpl = $tmpl->getTemplate();
    if($article)
    {
      $tmpl = $article->replaceCommonVars($tmpl, $id);
    }
    return rex_stream::factory('template/'. $id, $tmpl);
  }

  static public function getTemplateOutput()
  {
    return ob_get_clean();
  }
}
