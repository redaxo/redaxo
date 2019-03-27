<?php

/**
 * Cronjob Addon - Plugin article_status.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob\article-status
 */

class rex_cronjob_article_status extends rex_cronjob
{
    public function execute()
    {
        $config = rex_plugin::get('cronjob', 'article_status')->getProperty('config');
        $from = $config['from'];
        $to = $config['to'];
        $from['before'] = (array) $from['before'];
        $to['before'] = (array) $to['before'];

        $sql = rex_sql::factory();
        // $sql->setDebug();
        $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTablePrefix() . 'metainfo_field
            WHERE   name="' . $from['field'] . '" OR name="' . $to['field'] . '"
        ');
        $rows = $sql->getRows();
        if ($rows < 2) {
            if ($rows == 0) {
                $msg = 'Metainfo fields "' . $from['field'] . '" and "' . $to['field'] . '" not found';
            } else {
                $field = $sql->getValue('name') == $from['field'] ? $to['field'] : $from['field'];
                $msg = 'Metainfo field "' . $field . '" not found';
            }
            $this->setMessage($msg);
            return false;
        }

        $time = time();
        $sql->setQuery('
            SELECT  id, clang_id, status
            FROM    ' . rex::getTablePrefix() . 'article
            WHERE
                (     ' . $from['field'] . ' > 0
                AND   ' . $from['field'] . ' < ' . $time . '
                AND   status IN (' . implode(',', $from['before']) . ')
                AND   (' . $to['field'] . ' > ' . $time . ' OR ' . $to['field'] . ' = 0 OR ' . $to['field'] . ' = "")
                )
            OR
                (     ' . $to['field'] . ' > 0
                AND   ' . $to['field'] . ' < ' . $time . '
                AND   status IN (' . implode(',', $to['before']) . ')
                )
        ');
        $rows = $sql->getRows();

        for ($i = 0; $i < $rows; ++$i) {
            if (in_array($sql->getValue('status'), $from['before'])) {
                $status = $from['after'];
            } else {
                $status = $to['after'];
            }

            rex_article_service::articleStatus($sql->getValue('id'), $sql->getValue('clang_id'), $status);
            $sql->next();
        }
        $this->setMessage('Updated articles: ' . $rows);
        return true;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('cronjob_article_status');
    }
}
