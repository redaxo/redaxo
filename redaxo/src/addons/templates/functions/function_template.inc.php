<?php

/**
 * Generiert den TemplateCache im Filesystem
 *
 * @param $template_id Id des zu generierenden Templates
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateTemplate($template_id)
{
  $sql = rex_sql::factory();
  $qry = 'SELECT * FROM ' . rex::getTablePrefix()  . 'template WHERE id = ' . $template_id;
  $sql->setQuery($qry);

  if ($sql->getRows() == 1) {
    $templatesDir = rex_template::getTemplatesDir();
    $templateFile = rex_template::getFilePath($template_id);

    $content = $sql->getValue('content');
    $content = rex_var::parse($content, rex_var::FRONTEND, 'template');
    if(rex_file::put($templateFile, $content) !== FALSE)
    {
      return TRUE;
    }
    else
    {
      trigger_error('Unable to generate template '. $template_id .'!', E_USER_ERROR);

      if (!is_writable())
        trigger_error('directory "' . $templatesDir . '" is not writable!', E_USER_ERROR);
    }
  } else {
    trigger_error('Template with id "' . $template_id . '" does not exist!', E_USER_ERROR);
  }

  return false;
}
