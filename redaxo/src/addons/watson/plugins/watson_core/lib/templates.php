<?php


class watson_core_templates extends watson_searcher
{

    public function keywords()
    {
        return array('t');
    }

    public function legend()
    {
        global $I18N;

        $legend = new watson_legend();
        $legend->setName($I18N->msg('b_watson_legend_templates'));
        $legend->addKeyword('t', true, true);

        return $legend;
    }

    public function search(watson_search_term $watson_search_term)
    {
        global $REX, $I18N;

        $watson_search_result = new watson_search_result();

        if ($watson_search_term->getTerms()) {

            if ($watson_search_term->isAddMode()) {
                $name = $watson_search_term->getTermsAsString();

                $entry = new watson_search_entry();
                $entry->setValue($name);
                $entry->setDescription($I18N->msg('b_create_template', $name));
                $entry->setIcon('../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_template.png');
                $entry->setUrl(watson::url(array('page' => 'template', 'function' => 'add', 'watson_id' => 'ltemplatename', 'watson_text' => $name)));

                $watson_search_result->addEntry($entry);

            } else {
                $fields = array(
                    'name',
                    'content',
                );

                $sql_query  = ' SELECT      id,
                                            name
                                FROM        ' . watson::getTable('template') . '
                                WHERE       ' . $watson_search_term->getSqlWhere($fields) . '
                                ORDER BY    name
                                LIMIT       ' . watson::getResultLimit();

                $s = rex_sql::factory();
                $s->debugsql = true;
                $s->setQuery($sql_query);
                $results = $s->getArray();

                if ($s->getRows() >= 1) {

                    foreach ($results as $result) {
                        $url = watson::url(array('page' => 'template', 'template_id' => $result['id'], 'function' => 'edit'));

                        $entry = new watson_search_entry();
                        $entry->setValue($result['name']);
                        $entry->setDescription($I18N->msg('b_open_template'));
                        $entry->setIcon('../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_template.png');
                        $entry->setUrl($url);
                        $entry->setQuickLookUrl($url);

                        $watson_search_result->addEntry($entry);

                    }
                }
            }
        }

        return $watson_search_result;
    }
}
