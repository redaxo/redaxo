<?php

class watson
{
    public static function setPageParams()
    {
        parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $query);

        $_SESSION['WATSON']['PAGE_PARAMS'] = $query;
    }

    public static function getPageParam($param, $default = null)
    {
        if (isset($_SESSION['WATSON']['PAGE_PARAMS'][$param])) {
            return $_SESSION['WATSON']['PAGE_PARAMS'][$param];
        }
        return $default;
    }


    public static function getResultLimit()
    {
        return 10;
    }


    public static function debug($value, $exit = true)
    {
        echo '<pre style="text-align: left">';
        print_r($value);
        echo '</pre>';

        if ($exit) {
            exit();
        }

    }

    /**
     * Generates URL-encoded query string
     *
     * @param array  $params
     * @param string $argSeparator
     * @return string
     */
    public static function buildQuery(array $params, $argSeparator = '&')
    {
        $query = array();
        $func = function (array $params, $fullkey = null) use (&$query, &$func) {
            foreach ($params as $key => $value) {
                $key = $fullkey ? $fullkey . '[' . urlencode($key) . ']' : urlencode($key);
                if (is_array($value)) {
                    $func($value, $key);
                } else {
                    $query[] = $key . '=' . str_replace('%2F', '/', urlencode($value));
                }
            }
        };
        $func($params);
        return implode($argSeparator, $query);
    }

    /**
     * Returns the url to the backend-controller (index.php from backend)
     */
    public static function url(array $params = array())
    {
        $query = watson::buildQuery($params);
        $query = $query ? '?' . $query : '';
        return htmlspecialchars('index.php' . $query);
    }

    /**
     * Adds the table prefix to the table name
     *
     * @param string $table Table name
     * @return string
     */
    public static function getTable($table)
    {
        global $REX;
        return $REX['TABLE_PREFIX'] . $table;
    }

}
