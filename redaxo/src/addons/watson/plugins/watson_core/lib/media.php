<?php


class watson_core_media extends watson_searcher
{

    public function keywords()
    {
        return array('m', 'f');
    }

    public function legend()
    {
        global $I18N;

        $legend = new watson_legend();
        $legend->setName($I18N->msg('b_watson_legend_media'));
        $legend->addKeyword('m', true, true);
        $legend->addKeyword('f', true, true, $I18N->msg('b_watson_legend_synonym_for', 'm'));

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
                $entry->setDescription($I18N->msg('b_create_media', $name));
                $entry->setIcon('../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_media.png');
                $entry->setUrl('javascript:newPoolWindow(\'' . watson::url(array('page' => 'mediapool', 'subpage' => 'add_file', 'watson_id' => 'ftitle', 'watson_text' => $name)) . '\')');

                $watson_search_result->addEntry($entry);

            } else {

                $fields = array(
                    'filename',
                    'title',
                );

                $s = rex_sql::factory();
                $s->setQuery('SELECT * FROM ' . watson::getTable('file') .' LIMIT 0');
                $fieldnames = $s->getFieldnames();

                foreach ($fieldnames as $fieldname) {
                    if (substr($fieldname, 0, 4) == 'med_') {
                        $fields[] = $fieldname;
                    }
                }

                $sql_query  = ' SELECT      filename,
                                            title
                                FROM        ' . watson::getTable('file') . '
                                WHERE       ' . $watson_search_term->getSqlWhere($fields) . '
                                ORDER BY    filename
                                LIMIT       ' . watson::getResultLimit();

                $s = rex_sql::factory();
                $s->debugsql = true;
                $s->setQuery($sql_query);
                $results = $s->getArray();

                if ($s->getRows() >= 1) {

                    foreach ($results as $result) {

                        $title = ($result['title'] != '') ? ' (' . $I18N->msg('b_title') . ': ' . $result['title'] . ')' : '';

                        $entry = new watson_search_entry();
                        $entry->setValue($result['filename']);
                        $entry->setDescription($I18N->msg('b_open_media') . $title);
                        $entry->setIcon('../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_media.png');
                        $entry->setUrl('javascript:newPoolWindow(\'' . watson::url(array('page' => 'mediapool', 'subpage' => 'detail', 'file_name' => $result['filename'])) . '\')');

                        $m = OOMedia::getMediaByFileName($result['filename']);
                        if ($m instanceof OOMedia) {
                            if ($m->isImage()) {
                                $entry->setQuickLookUrl(watson::url(array('rex_img_type' => 'rex_mediapool_maximized', 'rex_img_file' => $result['filename'])));
                            }
                        }

                        $watson_search_result->addEntry($entry);

                    }
                }
            }
        }

        return $watson_search_result;
    }
}
