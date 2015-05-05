<?php

class watson_search_term
{
    private $keyword;
    private $input;
    private $mode;
    private $terms;


    public function __construct($input)
    {
        $this->input    = $input;

        $terms          = explode(' ', $this->input);
        $this->keyword  = $terms[0];
        $this->terms    = $terms;

        $this->setMode();
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    public function getInput()
    {
        return $this->input;
    }

    private function setMode()
    {
        $sign = substr($this->keyword, -1);
        switch ($sign) {
            case '+':
                $this->mode = 'ADD';
                break;
        }

        // Mode Zeichen trimmen
        if ($this->mode) {
            $this->keyword  = rtrim($this->keyword, $sign);
            $this->terms[0] = rtrim($this->terms[0], $sign);
        }
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function isAddMode()
    {
        return $this->mode == 'ADD';
    }

    public function getTerms()
    {
        return $this->terms;
    }

    public function getTermsAsString()
    {
        return implode(' ', $this->getTerms());
    }

    public function getSqlWhere($fields)
    {
        $where = array();
        foreach ($fields as $field) {
            $w = array();
            foreach ($this->getTerms() as $term) {
                $w[] = $field . ' LIKE "%' . $term . '%"';
            }

            $where[] = '(' . implode(' AND ', $w) . ')';
        }

        return implode(' OR ', $where);
    }

    public function deleteKeywordFromTerms()
    {
        foreach ($this->terms as $key => $term) {
            if ($this->keyword == $term) {
                unset($this->terms[$key]);
            }
        }
    }


}
