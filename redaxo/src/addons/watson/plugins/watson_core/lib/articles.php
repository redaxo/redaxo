<?php


class watson_core_articles extends watson_searcher
{

    public function keywords()
    {
        return array('a', 'c', 'on', 'off');
    }

    public function legend()
    {
        global $I18N;

        $legend = new watson_legend();
        $legend->setName($I18N->msg('b_watson_legend_articles'));
        $legend->addKeyword('a', false, true, $I18N->msg('b_watson_legend_create_article_in_structure'));
        $legend->addKeyword('c', false, true, $I18N->msg('b_watson_legend_create_category_in_structure'));
        $legend->addKeyword('on', true, false, $I18N->msg('b_watson_legend_article_online'));
        $legend->addKeyword('off', true, false, $I18N->msg('b_watson_legend_article_offline'));

        return $legend;
    }

    public function search(watson_search_term $watson_search_term)
    {
        global $REX, $I18N;

        $watson_search_result = new watson_search_result();

        if (watson::getPageParam('page') == 'structure' && $watson_search_term->isAddMode()) {

            // Artikel oder Kategorie anlegen

            $name = $watson_search_term->getTermsAsString();

            switch ($watson_search_term->getKeyword()) {

                case 'a':
                    $icon           = '../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_article.png';
                    $description    = $I18N->msg('b_create_article', $name);
                    $function       = 'add_art';
                    break;

                case 'c':
                    $icon           = '../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_article.png';
                    $description    = $I18N->msg('b_create_category', $name);
                    $function       = 'add_cat';
                    break;

                default:
                    $icon           = '';
                    $description    = '';
                    $function       = '';
                    break;

            }

            $entry = new watson_search_entry();
            $entry->setValue($name);
            $entry->setDescription($description);
            $entry->setIcon($icon);
            $entry->setUrl(watson::url(array('page' => 'structure', 'function' => $function, 'category_id' => watson::getPageParam('category_id'), 'watson_id' => 'rex-form-field-name', 'watson_text' => $name)));

            $watson_search_result->addEntry($entry);

        } elseif ($watson_search_term->getTerms()) {

            $terms = $watson_search_term->getTerms();
            $results = array();

            $status = '';
            if ($watson_search_term->getKeyword() == 'on') {
                $status = 'a.status = "1" AND ';
            } elseif ($watson_search_term->getKeyword() == 'off') {
                $status = 'a.status = "0" AND ';
            }

            // Artikelnamen in der Struktur durchsuchen
            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            $fields = array(
                'a.name',
            );

            $where = $watson_search_term->getSqlWhere($fields);
            if (count($terms) == 1 && (int)$terms[0] >= 1) {
                $where = 'a.id = "' . (int)$terms[0] .'"';
            }

            $sql_query  = ' SELECT      a.id,
                                        a.clang,
                                        CONCAT(a.id, "|", a.clang) as bulldog
                            FROM        ' . watson::getTable('article') . ' AS a
                            WHERE       ' . $status . '(' . $where .')
                            GROUP BY    bulldog
                            LIMIT       ' . watson::getResultLimit() . '
                            ';
            $s = rex_sql::factory();
            $s->debugsql = true;
            $s->setQuery($sql_query);
            $rows = $s->getArray();
            if ($s->getRows() >= 1) {
                foreach ($rows as $row) {
                    $results[ $row['bulldog'] ] = $row;
                }
            }


            // Slices der Artikel durchsuchen
            // Werden Slices gefunden, dann die Strukturartikel überschreiben
            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            $fields = array(
                's.value'     => range('1', '20'),
                's.file'      => range('1', '10'),
                's.filelist'  => range('1', '10'),
            );

            $search_fields = array();
            foreach ($fields as $field => $numbers) {
                foreach ($numbers as $number) {
                    $search_fields[] = $field . $number;
                }
            }
            $fields = $search_fields;

            $sql_query  = ' SELECT      s.article_id AS id,
                                        s.clang,
                                        s.ctype,
                                        CONCAT(s.article_id, "|", s.clang) as bulldog
                            FROM        ' . watson::getTable('article_slice') . ' AS s
                                LEFT JOIN
                                        ' . watson::getTable('article') . ' AS a
                                    ON  (s.article_id = a.id AND s.clang = a.clang)
                            WHERE       ' . $status . '(' . $watson_search_term->getSqlWhere($fields) . ')
                            GROUP BY    bulldog
                            LIMIT       ' . watson::getResultLimit() . '
                            ';
            $s = rex_sql::factory();
            $s->debugsql = true;
            $s->setQuery($sql_query);
            $rows = $s->getArray();
            if ($s->getRows() >= 1) {
                foreach ($rows as $row) {
                    $results[ $row['bulldog'] ] = $row;
                }
            }

            // Ergebnisse auf Rechte prüfen und bereitstellen
            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            if (count($results) >= 1) {

                foreach ($results as $result) {

                    $clang       = $result['clang'];
                    $article     = OOArticle::getArticleById($result['id'], $clang);
                    $category_id = $article->getCategoryId();


                    // Rechte prüfen
                    if (in_array($clang, $REX['USER']->getClangPerm()) && $REX['USER']->hasCategoryPerm($category_id)) {

                        $path = array();

                        $tree        = $article->getParentTree();
                        foreach ($tree as $o) {
                            $path[] = $o->getName();
                        }
                        if (!$article->isStartArticle()) {
                            $path[] = $article->getName();
                        }

                        $path = '/' . implode('/', $path);

                        $url = watson::url(array('page' => 'structure', 'category_id' => $article->getCategoryId(), 'clang' => $clang));
                        if (isset($result['ctype'])) {
                            $url = watson::url(array('page' => 'content', 'article_id' => $article->getId(), 'mode' => 'edit', 'clang' => $clang, 'ctype' => $result['ctype']));
                        }


                        $suffix = array();
                        if ($REX['USER']->hasPerm('advancedMode[]')) {
                            $suffix[] = $article->getId();
                        }
                        if (count($REX['CLANG']) > 1) {
                            $suffix[] = $REX['CLANG'][$clang];
                        }
                        $suffix = implode(', ', $suffix);
                        $suffix = $suffix != '' ? '(' . $suffix . ')' : '';


                        $entry = new watson_search_entry();
                        $entry->setValue($article->getName(), $suffix);
                        $entry->setDescription($path);
                        $entry->setIcon('../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_article.png');
                        $entry->setUrl($url);
                        $entry->setQuickLookUrl('../index.php?article_id=' . $article->getId() . '&clang=' . $clang);

                        $watson_search_result->addEntry($entry);
                    }

                }
            }
        }

        return $watson_search_result;
    }
}
