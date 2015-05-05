<?php


class watson_calculator extends watson_searcher
{

    public function keywords()
    {
        return array('=');
    }

    public function legend()
    {
        global $I18N;

        $legend = new watson_legend();
        $legend->setName($I18N->msg('b_watson_legend_calculator'));
        $legend->addKeyword('=', false, false);
        $legend->addKeyword(strtolower($I18N->msg('b_gross')), false, false, $I18N->msg('b_watson_legend_calculator_gross'));
        $legend->addKeyword(strtolower($I18N->msg('b_net')), false, false, $I18N->msg('b_watson_legend_calculator_net'));
        $legend->addKeyword(strtolower($I18N->msg('b_vat_calculator')), false, false, $I18N->msg('b_watson_legend_calculator_vat'));

        return $legend;
    }

    public function search(watson_search_term $watson_search_term)
    {
        global $REX, $I18N;

        $watson_search_result = new watson_search_result();

        if ($watson_search_term->getTerms()) {

            $terms = $watson_search_term->getTerms();
            $terms_as_string = $watson_search_term->getTermsAsString();

            // Runden
            $round = false;

            // Konstanten
            $constants = array(
                'Pi'    => 3.141592653589793,       // Pi
                'G'     => 6.67384 * pow(10, -11)   // Gravitationskonstante
            );

            // Konstanten ersetzen
            $terms = str_replace(array_keys($constants), str_replace(',', '.', array_values($constants)), str_replace(',', '.', $terms));

            // Keyword
            $keyword = strtolower($terms[0]);

            // Kalkulator
            $calc = new SimpleCalc();

            // Prozentrechnung
            // "20 von 200"  -> 40
            // "20% von 200" -> 40
            if (strpos($terms_as_string, ' ' . $I18N->msg('b_percent_of') . ' ') !== false) {
                $a = rtrim($terms[0], '%') . '%';
                unset($terms[0], $terms[1]);

                // restlichen Angaben zusammensetzen und berechnen
                $terms = implode('', $terms);
                $b = $calc->calculate($terms);

                // Formel
                $terms = $b . ' * ' . $a;

            }

            if ($keyword == strtolower($I18N->msg('b_gross')) || $keyword == strtolower($I18N->msg('b_net')) || $keyword == strtolower($I18N->msg('b_vat_calculator'))) {
                // Brutto
                // "brutto 200"     -> 238
                // "brutto 19 200"  -> 238
                // "brutto 7 200"   -> 214

                // Netto
                // "netto 200"      -> 168.07
                // "netto 19 200"   -> 168.07
                // "netto 7 200"    -> 186.92

                // Ust
                // "ust 200"    -> 31.93
                // "ust 19 200" -> 31.93
                // "ust 7 200"  -> 13.08

                $round = true;

                unset($terms[0]);

                $a = 0;
                $b = 0;
                $count = count($terms);
                if ($count == 1) {
                    // Eingabe
                    // "$keyword 200"

                    $a = $I18N->msg('b_standard_tax') . '%';
                    $b = $calc->calculate($terms[1]);

                } elseif ($count >= 2) {
                    // Eingabe
                    // "$keyword 7 200"
                    // "$keyword 7 200+20"
                    // "$keyword 7 200 + 20"

                    $a = rtrim($terms[1], '%') . '%';
                    unset($terms[1]);

                    // restlichen Angaben zusammensetzen und berechnen
                    $terms = implode('', $terms);
                    $b = $calc->calculate($terms);
                }

                // Formeln
                if ($keyword == strtolower($I18N->msg('b_gross'))) {
                    $terms = $b . ' + ' . $b . ' * ' . $a;
                } elseif ($keyword == strtolower($I18N->msg('b_net'))) {
                    $terms = $b . ' / (1 + ' . $a . ')';
                } elseif ($keyword == strtolower($I18N->msg('b_vat_calculator'))) {
                    $terms = $b . ' - ' . $b . ' / (1 + ' . $a . ')';
                }

            }

            $terms = str_replace('%', '/100', $terms);

            if (is_array($terms)) {
                $terms = implode(' ', $terms);
            }

            // Ergebnis anzeigen, wenn keine Wörter vorhanden sind
            // Eingabe "name" -> 0
            if (!preg_match('@[a-zA-DF-Z]+@', $terms)) {
                $result = $calc->calculate($terms);

                if ($round) {
                    $result = round($result, 2);
                }

                $result = str_replace('.', ',', $result);

                $entry = new watson_search_entry();
                $entry->setValue('= ' . $result);
                $entry->setIcon('../' . $REX['MEDIA_ADDON_DIR'] . '/watson/icon_hat.png');

                $watson_search_result->addEntry($entry);
            }

        }

        return $watson_search_result;
    }

}
