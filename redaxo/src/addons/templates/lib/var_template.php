<?php

/**
 * REX_TEMPLATE[2]
 *
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_var_template extends rex_var
{
  // --------------------------------- Output

  public function getBEOutput(rex_sql $sql, $content)
  {
    return $this->matchTemplate($content);
  }

  public function getTemplate($content)
  {
    return $this->matchTemplate($content);
  }

  /**
   * Wert für die Ausgabe
   */
  private function matchTemplate($content)
  {
    $var = 'REX_TEMPLATE';
    $matches = $this->getVarParams($content, $var);

    foreach ($matches as $match)
    {
      list ($param_str, $args) = $match;
      $template_id = $this->getArg('id', $args, 0);

      if($template_id > 0)
      {
        $tpl = '<?php require '. __CLASS__ .'::getTemplateStream('. $template_id .', $this, \''. json_encode($args) ."'); ?>";
	      $content = str_replace($var . '[' . $param_str . ']', $tpl, $content);
      }
    }

    return $content;
  }

  static public function getTemplateStream($id, $article = null, $args = '')
  {
    $tmpl = new rex_template($id);
    $tmpl = $tmpl->getTemplate();
    if($article)
    {
      $tmpl = $article->replaceCommonVars($tmpl, $id);
    }
    $tmpl = self::handleGlobalVarParams('REX_TEMPLATE', json_decode($args, true), $tmpl);
    return rex_stream::factory('template/'. $id, $tmpl);
  }
}