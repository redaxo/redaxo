<?php


class watson_core_users extends watson_searcher
{

    public function keywords()
    {
        return array('u');
    }

    public function legend()
    {
        global $I18N;

        $legend = new watson_legend();
        $legend->setName($I18N->msg('b_watson_legend_users'));
        $legend->addKeyword('u', false, true);

        return $legend;
    }

    public function search(watson_search_term $watson_search_term)
    {
        global $REX, $I18N;

        $watson_search_result = new watson_search_result();

        if ($watson_search_term->getTerms()) {

            if ($watson_search_term->isAddMode()) {
                $name = implode(' ', $watson_search_term->getTerms());

                $entry = new watson_search_entry();
                $entry->setValue($name);
                $entry->setDescription($I18N->msg('b_create_user', $name));
                $entry->setIcon('../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_user.png');
                $entry->setUrl(watson::url(array('page' => 'user', 'FUNC_ADD' => '1', 'watson_id' => 'userlogin', 'watson_text' => $name)));

                $watson_search_result->addEntry($entry);

            }
        }

        return $watson_search_result;
    }
}
