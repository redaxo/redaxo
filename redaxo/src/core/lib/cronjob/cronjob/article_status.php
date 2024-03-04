<?php

use Redaxo\Core\Core;

class rex_cronjob_article_status extends rex_cronjob
{
    public function execute()
    {
        $from = [
            'field' => 'art_online_from',
            'before' => 0,
            'after' => 1,
        ];
        $to = [
            'field' => 'art_online_to',
            'before' => 1,
            'after' => 0,
        ];

        $sql = rex_sql::factory();
        $sql->setQuery(
            '
            SELECT  name
            FROM    ' . Core::getTablePrefix() . 'metainfo_field
            WHERE   name=? OR name=?',
            [$from['field'], $to['field']],
        );
        $rows = $sql->getRows();
        if ($rows < 2) {
            if (0 == $rows) {
                $msg = 'Metainfo fields `' . $from['field'] . '` and `' . $to['field'] . '` not found. Please add them to the metainfo fields.';
            } else {
                $field = $sql->getValue('name') == $from['field'] ? $to['field'] : $from['field'];
                $msg = 'Metainfo field `' . $field . '` not found. Please add it to the metainfo fields.';
            }
            $this->setMessage($msg);
            return false;
        }

        $time = time();
        $sql->setQuery(
            '
            SELECT  id, clang_id, status
            FROM    ' . Core::getTablePrefix() . 'article
            WHERE
                (     ' . $sql->escapeIdentifier($from['field']) . ' > 0
                AND   ' . $sql->escapeIdentifier($from['field']) . ' < :time
                AND   status IN (' . $sql->in([$from['before']]) . ')
                AND   (' . $sql->escapeIdentifier($to['field']) . ' > :time OR ' . $sql->escapeIdentifier($to['field']) . ' = 0 OR ' . $sql->escapeIdentifier($to['field']) . ' = "")
                )
            OR
                (     ' . $sql->escapeIdentifier($to['field']) . ' > 0
                AND   ' . $sql->escapeIdentifier($to['field']) . ' < :time
                AND   status IN (' . $sql->in([$to['before']]) . ')
                )',
            ['time' => $time],
        );
        $rows = $sql->getRows();

        for ($i = 0; $i < $rows; ++$i) {
            if ($sql->getValue('status') == $from['before']) {
                $status = $from['after'];
            } else {
                $status = $to['after'];
            }

            rex_article_service::articleStatus((int) $sql->getValue('id'), (int) $sql->getValue('clang_id'), $status);
            $sql->next();
        }
        $this->setMessage('Updated articles: ' . $rows);

        if ($this->getParam('reset_date')) {
            $sql->setQuery(
                '
                UPDATE ' . Core::getTablePrefix() . 'article
                SET ' . $sql->escapeIdentifier($from['field']) . ' = ""
                WHERE     ' . $sql->escapeIdentifier($from['field']) . ' > 0
                    AND   ' . $sql->escapeIdentifier($from['field']) . ' < :time',
                ['time' => $time],
            );
            $sql->setQuery(
                '
                UPDATE ' . Core::getTablePrefix() . 'article
                SET ' . $sql->escapeIdentifier($to['field']) . ' = ""
                WHERE ' . $sql->escapeIdentifier($to['field']) . ' > 0
                AND   ' . $sql->escapeIdentifier($to['field']) . ' < :time',
                ['time' => $time],
            );
        }
        return true;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('cronjob_article_status');
    }

    public function getParamFields()
    {
        return [
            [
                'name' => 'reset_date',
                'type' => 'checkbox',
                'options' => [1 => rex_i18n::rawMsg('cronjob_article_reset_date')],
                'notice' => rex_i18n::msg('cronjob_article_reset_date_info'),
            ],
        ];
    }
}
