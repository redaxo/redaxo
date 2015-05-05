<?php


class watson_core_commands extends watson_searcher
{

    public function keywords()
    {
        return array('home', 'logout', 'start');
    }

    public function legend()
    {
        global $I18N;

        $legend = new watson_legend();
        $legend->setName($I18N->msg('b_watson_legend_commands'));
        $legend->addKeyword('home', false, false, $I18N->msg('b_watson_legend_command_home'));
        $legend->addKeyword('logout', false, false, $I18N->msg('b_watson_legend_command_logout'));
        $legend->addKeyword('start', false, false, $I18N->msg('b_watson_legend_command_start'));

        return $legend;
    }

    public function search(watson_search_term $watson_search_term)
    {
        global $REX, $I18N;

        $watson_search_result = new watson_search_result();

        if (!$watson_search_term->getTerms()) {

            switch ($watson_search_term->getKeyword()) {

                case 'home':
                    $entry = new watson_search_entry();
                    $entry->setValue($I18N->msg('b_go_to_frontend'));
                    $entry->setUrl('../' . rex_getUrl($REX['START_ARTICLE_ID']), true);
                    $watson_search_result->addEntry($entry);
                    break;

                case 'logout':
                    $entry = new watson_search_entry();
                    $entry->setValue($I18N->msg('b_logout_from_backend'));
                    $entry->setUrl(watson::url(array('rex_logout' => '1')));
                    $watson_search_result->addEntry($entry);
                    break;

                case 'start':
                    $entry = new watson_search_entry();
                    $entry->setValue($I18N->msg('b_go_to_backend_startarticle'));
                    $entry->setUrl(watson::url(array('page' => 'structure', 'category_id' => $REX['START_ARTICLE_ID'])));
                    $watson_search_result->addEntry($entry);
                    break;
            }

        }

        return $watson_search_result;
    }
}
