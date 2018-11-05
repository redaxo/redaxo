<?php

rex_extension::register('MINIBAR_ARTICLE', function (rex_extension_point $ep) {
    $sqlFields = rex_sql::factory();
    // $sqlFields->setDebug();
    $fields = $sqlFields->getArray('
        SELECT  `t`.`label`, 
                `f`.`title`, 
                `f`.`name` 
        FROM    '.rex::getTable('metainfo_field').' AS f
            LEFT JOIN '.rex::getTable('metainfo_type').' AS t
                ON  `f`.`type_id` = `t`.`id`
        WHERE `f`.`name` LIKE :art OR `f`.`name` LIKE :cat 
        ORDER BY LEFT(`f`.`name`, 4), priority', ['art' => 'art_%', 'cat' => 'cat_%']);

    if (!count($fields)) {
        return null;
    }

    $article = $ep->getParam('article');

    $items = [];
    foreach ($fields as $field) {
        // Durch das unterschiedliche Erstellen der Optionen (Pipe, Sql) können die dazugehörigen Labels nicht ganz so einfach aufgelöst werden
        // Ein Admin sieht daher die gespeicherten Werte, ein Redakteur kann damit weniger anfangen
        if (!rex::getUser()->isAdmin() && in_array($field['label'], ['checkbox', 'radio', 'select'])) {
            continue;
        }
        if (in_array($field['label'], ['legend'])) {
            continue;
        }

        $value = $article->getValue($field['name']);
        if (trim($value) != '') {
            switch ($field['label']) {
                case 'REX_MEDIA_WIDGET':
                    $value = sprintf('<a href="%s" target="_blank">%s</a>', rex_url::media($value), $value);
                    break;
                case 'REX_MEDIALIST_WIDGET':
                    $values = explode(',', $value);
                    $value = [];
                    foreach ($values as $fileName) {
                        $value[] = sprintf('<a href="%s" target="_blank">%s</a>', rex_url::media($fileName), $fileName);
                    }
                    $value = implode(' | ', $value);
                    break;
                case 'REX_LINK_WIDGET':
                    $linkedArticle = rex_article::get($value);
                    if (!$linkedArticle) {
                        break;
                    }
                    $value = sprintf('<a href="%s" target="_blank">%s</a>', $linkedArticle->getUrl(), $linkedArticle->getName());
                    break;
                case 'REX_LINKLIST_WIDGET':
                    $values = explode(',', $value);
                    $value = [];
                    foreach ($values as $articleId) {
                        $linkedArticle = rex_article::get($articleId);
                        if (!$linkedArticle) {
                            continue;
                        }
                        $value[] = sprintf('<a href="%s">%s</a>', $linkedArticle->getUrl(), $linkedArticle->getName());
                    }
                    $value = implode(' | ', $value);
                    break;
                case 'date':
                    $value = rex_formatter::strftime($value);
                    break;
                case 'datetime':
                    $value = rex_formatter::strftime($value, 'datetime');
                    break;
                case 'time':
                    $value = rex_formatter::strftime($value, 'time');
                    break;
            }
        }
        $item = '
            <div class="rex-minibar-info-piece">
                <b>'.rex_i18n::translate($field['title']).'</b>
                <span>'.$value.'</span>                    
            </div>';

        $items[] = $item;
    }

    return
        '<div class="rex-minibar-info-group">
            <div class="rex-minibar-info-group-title">'.rex_i18n::msg('metainfo_minibar_article_title').'</div>
            '.implode('', $items).'
        </div>';
});

rex_extension::register('MINIBAR_CLANG', function (rex_extension_point $ep) {
    if (!rex::getUser()->isAdmin()) {
        return null;
    }

    $sqlFields = rex_sql::factory();
    // $sqlFields->setDebug();
    $fields = $sqlFields->getArray('SELECT `title`, `name` FROM '.rex::getTable('metainfo_field').' WHERE `name` LIKE :prefix ORDER BY priority', ['prefix' => 'clang_%']);

    if (!count($fields)) {
        return null;
    }

    $clang = $ep->getParam('clang');
    $items = [];
    foreach ($fields as $field) {
        $item = '
            <div class="rex-minibar-info-piece">
                <b>'.rex_i18n::translate($field['title']).'</b>
                <span>'.$clang->getValue($field['name']).'</span>                    
            </div>';

        $items[] = $item;
    }

    return
        '<div class="rex-minibar-info-group">
            <div class="rex-minibar-info-group-title">'.rex_i18n::msg('metainfo_minibar_clang_title').'</div>
            '.implode('', $items).'
        </div>';
});
