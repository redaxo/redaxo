<?php

/**
 * @package redaxo\structure
 */
class rex_api_structure_search_content extends rex_api_function
{

    public function execute ()
    {
        $result = new rex_api_typeahead_result(true);
        for ($i = 0; $i < 10; $i ++) {
            $datum = new rex_typeahead_datum('hallo' . $i, [
                'ha' . $i,
                'lo' . $i,
                'xy' . $i
            ]);
            $result->addDatum($datum);
        }
        
        return $result;
    }
}

/**
 *
 * @package redaxo\structure
 */
class rex_api_structure_search_structure extends rex_api_function
{

    public function execute ()
    {
        $result = new rex_api_typeahead_result(true);
        
        $entry = new rex_typeahead_datum('hallo structure', [
            'ha',
            'lo',
            'xy',
            'off'
        ]);
        $result->addDatum($entry);
        
        return $result;
    }
}

class rex_typeahead_datum
{

    /**
     * String representing the underlying value, in other words a label.
     *
     * @var string
     */
    private $value;

    /**
     * Array of keywords used for matching
     *
     * @var string[]
     */
    private $tokens;

    /**
     * Additional custom data provided with the item
     *
     * @var string[]
     */
    private $data;

    /**
     *
     * @param string $value            
     * @param string|string[] $tokens            
     */
    public function __construct ($value, $tokens)
    {
        $this->value = $value;
        $this->tokens = (array) $tokens;
    }

    public function __set ($key, $value)
    {
        $this->data[$key] = $value;
    }

//     public function __get ($key)
//     {
//         if (isset($this->data[$key])) {
//             return $this->data[$key];
//         }
//         return null;
//     }

    public function toArray ()
    {
        $data = [];
        
        $data['value'] = $this->value;
        $data['tokens'] = $this->tokens;
        
        foreach ($this->data as $key => $value) {
            $data[$key] = $value;
        }
        
        return $data;
    }
}

class rex_api_typeahead_result extends rex_api_result_abstract
{

    /**
     * @var rex_typeahead_datum[]
     */
    private $datum = array();

    public function addDatum (rex_typeahead_datum $datum)
    {
        $this->datum[] = $datum;
    }

    public function toJson ()
    {
        $result = [];
        
        foreach ($this->datum as $datum) {
            $result[] = $datum->toArray();
        }
        
        return json_encode($result);
    }
}