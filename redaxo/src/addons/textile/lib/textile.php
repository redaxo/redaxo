<?php

/**
 * @package redaxo\textile
 */
class rex_textile
{
    private static $instances = [];

    public static function parse($code, $restricted = false, $doctype = 'xhtml')
    {
        $instance = self::getInstance($doctype);
        return $restricted ? $instance->TextileRestricted($code) : $instance->TextileThis($code);
    }

    /**
     * @param string $doctype
     *
     * @return Netcarver\Textile\Parser
     */
    private static function getInstance($doctype = 'xhtml')
    {
        if (!isset(self::$instances[$doctype])) {
            self::$instances[$doctype] = new rex_textile_parser($doctype);
        }
        return self::$instances[$doctype];
    }

    public static function showHelpOverview()
    {
        $formats = self::getHelpOverviewFormats();

        $echo = '';


        $echo .= '
            <div class="textile-help-overview">
                <h4>' . rex_i18n::msg('textile_instructions') . '</h4>';

        foreach ($formats as $format) {

            $title = $format[0];
            $content = '';
            $content .= '
                <table class="table">
                    <thead>
                        <tr>
                            <th>' . rex_i18n::msg('textile_input') . '</th>
                            <th>' . rex_i18n::msg('textile_preview') . '</th>
                        </tr>
                    </thead>
                    <tbody>
                         ';

            foreach ($format[1] as $perm => $formats) {
                foreach ($formats as $_format) {
                    $desc = $_format[0];

                    $code = '';
                    if (isset($_format[1])) {
                        $code = $_format[1];
                    }

                    if ($code == '') {
                        $code = $desc;
                    }

                    $code = trim(self::parse($code));

                    $content .= '<tr>
                            <td>' . nl2br(htmlspecialchars($desc)) . '</td>
                            <td>' . $code . '</td>
                        </tr>';
                }
            }

            $content .= '</tbody></table>';

            $fragment = new rex_fragment();
            $fragment->setVar('title', '<i class="rex-icon rex-icon-package-addon"></i> ' . $title, false);
            $fragment->setVar('content', $content, false);
            $fragment->setVar('collapse', true);
            $fragment->setVar('collapsed', true);
            $echo .= $fragment->parse('core/page/section.php');
        }
        $echo .= '</div>';
        echo $echo;
    }

    private static function getHelpOverviewFormats()
    {
        return [
            self::getHelpHeadlines(),
            self::getHelpFormats(),
            self::getHelpLinks(),
            self::getHelpFootnotes(),
            self::getHelpLists(),
            self::getHelpTables(),
        ];
    }

    private static function getHelpHeadlines()
    {
        return [
            rex_i18n::msg('textile_headlines'),
            [
                'headlines1-3' => [
                    ['h1. ' . rex_i18n::msg('textile_headline') . ' 1'],
                    ['h2. ' . rex_i18n::msg('textile_headline') . ' 2'],
                    ['h3. ' . rex_i18n::msg('textile_headline') . ' 3'],
                ],
                'headlines4-6' => [
                    ['h4. ' . rex_i18n::msg('textile_headline') . ' 4'],
                    ['h5. ' . rex_i18n::msg('textile_headline') . ' 5'],
                    ['h6. ' . rex_i18n::msg('textile_headline') . ' 6'],
                ],
            ],
        ];
    }

    private static function getHelpFormats()
    {
        return [
            rex_i18n::msg('textile_text_formatting'),
            [
                'text_xhtml' => [
                    ['_' . rex_i18n::msg('textile_text_italic') . '_'],
                    ['*' . rex_i18n::msg('textile_text_bold') . '*'],
                ],
                'text_html' => [
                    ['__' . rex_i18n::msg('textile_text_italic') . '__'],
                    ['**' . rex_i18n::msg('textile_text_bold') . '**'],
                ],
                'cite' => [
                    ['bq. ' . rex_i18n::msg('textile_text_cite')],
                    ['??' . rex_i18n::msg('textile_text_source_author') . '??'],
                ],
                'overwork' => [
                    ['-' . rex_i18n::msg('textile_text_strike') . '-'],
                    ['+' . rex_i18n::msg('textile_text_insert') . '+'],
                    ['^' . rex_i18n::msg('textile_text_sup') . '^'],
                    ['~' . rex_i18n::msg('textile_text_sub') . '~'],
                ],
                'code' => [
                    ['@<?php echo "Hi"; ?>@'],
                ],
            ],
        ];
    }

    private static function getHelpLinks()
    {
        return [
            rex_i18n::msg('textile_links'),
            [
                'links_intern' => [
                    [rex_i18n::rawMsg('textile_link_internal') . ':redaxo://5'],
                    [rex_i18n::rawMsg('textile_link_internal_anchor') . ':redaxo://7#AGB'],
                ],
                'links_extern' => [
                    [rex_i18n::rawMsg('textile_link_external') . ':http://www.redaxo.org'],
                    [rex_i18n::rawMsg('textile_link_external_anchor') . ':http://www.redaxo.org#news'],
                ],
                'links_attributes' => [
                    [rex_i18n::rawMsg('textile_link_attr_title') . ':media/test.jpg'],
                    [rex_i18n::rawMsg('textile_link_attr_rel') . ':media/test.jpg'],
                    [rex_i18n::rawMsg('textile_link_attr_title_rel') . ':media/test.jpg'],
                ],
                'anchor' => [
                    [rex_i18n::rawMsg('textile_link_anchor') . ":\n\np(#Impressum). " . rex_i18n::msg('textile_link_anchor_text')],
                ],
            ],
        ];
    }

    private static function getHelpFootnotes()
    {
        return [
            rex_i18n::msg('textile_footnotes'),
            [
                'footnotes' => [
                    [rex_i18n::msg('textile_footnote_text') . '[1] ..'],
                    ['fn1. ' . rex_i18n::msg('textile_footnote_note')],
                ],
            ],
        ];
    }

    private static function getHelpLists()
    {
        return [
            rex_i18n::msg('textile_lists'),
            [
                'lists' => [
                    [rex_i18n::msg('textile_numeric_list') . ":\n# redaxo.org\n# www.redaxo.org/de/forum/"],
                    [rex_i18n::msg('textile_enum_list') . ":\n* redaxo.org\n* www.redaxo.org/de/forum/"],
                ],
            ],
        ];
    }

    private static function getHelpTables()
    {
        return [
            rex_i18n::msg('textile_tables'),
            [
                'tables' => [
                    ["|_. Id|_. Name|\n|1|Peter|"],
                    ["|www.redaxo.org|35|\n|doku.redaxo.org|32|\n|wiki.redaxo.org|12|"],
                ],
            ],
        ];
    }
}

/**
 * @package redaxo\textile
 */
class rex_textile_parser extends Netcarver\Textile\Parser
{
    public function __construct($doctype = 'xhtml')
    {
        parent::__construct($doctype);
        $this->unrestricted_url_schemes[] = 'redaxo';
    }
}
