<?php

class watson_extensions
{
    public static function agent($params)
    {
        global $REX, $I18N;
        $panel = '
            <div id="watson">
                <form action="">
                    <fieldset>
                        <input class="typeahead" type="text" name="q" value="" />
                    </fieldset>
                </form>
                <span class="watson-settings-open"></span>
            </div><div id="watson-overlay"></div>';


        watson::setPageParams();

        $params['subject'] = str_replace('</body>', $panel . '</body>', $params['subject']);
        return $params['subject'];
    }

    public static function legend($params)
    {
        global $REX, $I18N;
        $panel = '
            <div id="watson-settings">
                <h1>' . $I18N->msg('b_watson_title'). '</h1>
                ' . $params['html'] . '
                <span class="watson-settings-close"></span>
            </div>';

        $params['subject'] = str_replace('</body>', $panel . '</body>', $params['subject']);
        return $params['subject'];
    }


    public static function searcher()
    {
        global $REX, $I18N;

        // Phase 1
        /** @var $searcher watson_searcher[] */
        $searchers = rex_register_extension_point('WATSON_SEARCHER');


        // Phase 2
        // Legenden holen
        if (is_array($searchers) && count($searchers) > 0) {
            $legends = array();
            foreach($searchers as $searcher) {
                $legend = $searcher->legend();
                if ($legend instanceof watson_legend) {
                    $legends[] = $legend->get();
                }
            }

            $html = implode('', $legends);
            $html = '<table class="watson-legend">
                        <thead>
                        <tr>
                            <th class="watson-legend-title">' . $I18N->msg('b_title'). '</th>
                            <th class="watson-legend-keyword">' . $I18N->msg('b_keyword'). '</th>
                            <th class="watson-legend-search">' . $I18N->msg('b_search'). '</th>
                            <th class="watson-legend-add">' . $I18N->msg('b_add'). '</th>
                            <th class="watson-legend-description">' . $I18N->msg('b_description'). '</th>
                        </tr>
                        </thead>
                        <tbody>
                        ' . $html . '
                        </tbody>
                     </table>';
            $params = array('html' => $html);

            rex_register_extension('OUTPUT_FILTER', 'watson_extensions::legend', $params);
        }


        // Phase 2
        // User Eingabe parsen in $input
        $input = rex_request('watson', 'string');
        if ($input != '' && is_array($searchers) && count($searchers) > 0) {

            $watson_search_term = new watson_search_term($input);

            // Eingabe auf Keywords überprüfen
            $save_searchers = array();
            foreach($searchers as $searcher) {
                if (in_array($watson_search_term->getKeyword(), $searcher->keywords())) {
                    $save_searchers[] = $searcher;
                }
            }

            // registriertes Keyword gefunden
            if (count($save_searchers) > 0) {
                $searchers = $save_searchers;
                $watson_search_term->deleteKeywordFromTerms();
            }

            // Eingabe an vorher registrierte Search übergeben und Ergebnisse einsammeln
            /** @var $search_results watson_search_result[] */
            $search_results = array();
            foreach($searchers as $searcher) {
                $search_results[] = $searcher->search($watson_search_term);
            }

            // Ergebnis rendern
            $results = array();
            foreach ($search_results as $search_result) {
                // render json/html whatever
                $results[] = $search_result->render();
            }

            $json = array();
            foreach ($results as $values) {
                foreach ($values as $value) {
                    $json[] = $value;
                }
            }


            if (count($json) == 0) {
                $json[] = array('value_name' => $I18N->msg('b_no_results'), 'value' => $I18N->msg('b_no_results'), 'tokens' => array($I18N->msg('b_no_results')));
            }

            ob_clean();
            header('Content-type: application/json');
            echo json_encode($json);
            exit();
        }
    }


    public static function page_header($params)
    {
        global $REX;
        $myaddon = 'watson';

        $css_files      = $params['css'];
        $js_files       = $params['js'];
        $js_properties  = json_encode(array('resultLimit' => watson::getResultLimit(), 'backend' => true, 'backendUrl' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

        $addon_assets = '../' . $REX['MEDIA_ADDON_DIR'] . '/' . $myaddon . '/';

        foreach ($css_files as $media => $files) {
            foreach ($files as $file) {
                $params['subject'] .= "\n" . '<link rel="stylesheet" type="text/css" media="' . $media . '" href="' . $addon_assets . $file . '" />';
            }
        }

        if ($js_properties) {
            $params['subject'] .= "\n" . '

                    <script type="text/javascript">
                        <!--
                        if (typeof(watson) == "undefined") {
                            var watson = ' . $js_properties . ';
                        }
                        //-->
                    </script>';
        }

        foreach ($js_files as $file) {
            $params['subject'] .= "\n" . '<script type="text/javascript" src="' . $addon_assets . $file . '"></script>';
        }

        return $params['subject'];
    }
}
