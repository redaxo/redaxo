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
    $content = rex_var::parse($content, rex_var::ENV_FRONTEND, 'template');
    if (rex_file::put($templateFile, $content) !== false) {
      return true;
    } else {
      throw new rex_exception('Unable to generate template ' . $template_id . '!');

      if (!rex_dir::isWritable($templatesDir))
        throw new rex_exception('directory "' . $templatesDir . '" is not writable!');
    }
  } else {
    trigger_error('Template with id "' . $template_id . '" does not exist!', E_USER_ERROR);
  }

  return false;
}
