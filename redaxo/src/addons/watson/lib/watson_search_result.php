<?php


class watson_search_result
{
    private $entries;
    private $header;
    private $footer;



    public function __construct()
    {

    }



    /**
     * Sets result entry
     *
     * @param watson_search_entry $entry
     */
    public function addEntry($entry)
    {
        $this->entries[] = $entry;
    }



    /**
     * render all result entries
     */
    public function render()
    {
        $entries = $this->entries;

        $returns = array();
        if (count($entries) > 0) {
            foreach ($entries as $entry) {
                $return = array();

                $classes = array();
                $styles  = array();

                $value = $entry->getValue();

                $return['value_name']   = $entry->getValue();
                $return['description']  = '';

                if ($entry->hasValueSuffix()) {
                    // Suffix anhängen, da sonst nur ein Ergebnis erscheint
                    // Bspl. gleicher Artikelname in 2 Sprachen
                    $value .= ' ' . $entry->getValueSuffix();

                    $classes[]                  = 'watson-has-value-suffix';
                    $return['value_suffix']     = $entry->getValueSuffix();
                }

                if ($entry->hasIcon()) {
                    $classes[]                  = 'watson-has-icon';
                    $styles[]                   = 'background-image: url(' . $entry->getIcon() . ');';
                }

                if ($entry->hasDescription()) {
                    $classes[]                  = 'watson-has-description';
                    $return['description']      = $entry->getDescription();
                }

                if ($entry->hasUrl()) {
                    $return['url']              = $entry->getUrl();
                    $return['url_open_window']  = $entry->getUrlOpenWindow();
                }

                if ($entry->hasQuickLookUrl()) {
                    $classes[]                  = 'watson-has-quick-look';
                    $return['quick_look_url']   = $entry->getQuickLookUrl();
                }

                $return['value']        = $value;
                $return['tokens']       = array($value);


                $class = count($classes) > 0 ? ' ' . implode(' ', $classes) : '';
                $style = count($styles) > 0 ? implode(' ', $styles) : '';

                $return['class']        = $class;
                $return['style']        = $style;

                $returns[] = $return;
            }

        }

        return $returns;
    }
}
