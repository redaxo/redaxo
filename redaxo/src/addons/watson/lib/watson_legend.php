<?php

class watson_legend
{
    private $name;
    private $keywords = array();

    public function __construct($value = null)
    {
        if ($value) {
            $this->setName($value);
        }
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getName()
    {
        return $this->name;
    }


    public function addKeyword($keyword, $search_mode = false, $add_mode = false, $description = '')
    {
        $array = array();
        $array['keyword'] = $keyword;

        if ($search_mode) {
            $array['search_mode'] = true;
        } else {
            $array['search_mode'] = false;
        }

        if ($add_mode) {
            $array['add_mode'] = true;
        } else {
            $array['add_mode'] = false;
        }


        $array['description'] = strip_tags($description);

        $this->keywords[] = $array;
    }


    public function getKeywords()
    {
        return $this->keywords;
    }


    public function get()
    {
        $return = '';

        $name     = $this->getName();
        $keywords = $this->getKeywords();

        if (count($keywords) > 0) {
            $c = 0;
            foreach ($keywords as $keyword) {
                $c++;

                $return .= '<tr>';

                if ($c == 1) {
                    $return .= '<th class="watson-legend-title" rowspan="' . count($keywords) . '">' . $name . '</th>';
                }

                $return .= '<td class="watson-legend-keyword">' . $keyword['keyword'] . '</td>';

                $class = '';
                if ($keyword['search_mode']) {
                    $class = ' class="watson-legend-check"';
                }
                $return .= '<td class="watson-legend-search"><span' . $class . '></span></td>';


                $class = '';
                if ($keyword['add_mode']) {
                    $class = ' class="watson-legend-check"';
                }
                $return .= '<td class="watson-legend-add"><span' . $class . '></span></td>';


                $return .= '<td class="watson-legend-description">' . $keyword['description'] . '</td>';


                $return .= '</tr>';
            }
        } else {
            $return = '<tr><th class="watson-legend-title" colspan="5">' . $name . '</th></tr>';
        }

        //$return = '<table class="watson-legend">' . $return . '</table>';

        return $return;
    }

}
