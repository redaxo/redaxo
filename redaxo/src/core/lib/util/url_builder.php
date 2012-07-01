<?php

class rex_url_builder implements rex_url_provider {
  private $parts;
  private $params;

  public function __construct($baseUrl = null)
  {
    $this->parts = array();
    $this->params = array();

    if($baseUrl) {
      $this->parseUrl($baseUrl);
    }
  }

  private function parseUrl($url)
  {
    $this->parts = parse_url($url);
    if(isset($this->parts['query'])) {
      parse_str($this->parts['query'], $this->params);
    }
  }

  public function addParams(array $array)
  {
    $this->params = array_merge($this->params, $array);
  }

  public function setParam($name, $value)
  {
    $this->params[$name] = $value;
  }

  public function getParam($name, $default = null)
  {
    return isset($this->params[$name]) ? $this->params[$name] : $default;
  }

  public function removeParam($name)
  {
    unset($this->params[$name]);
  }

  public function getUrl(array $params = array()) {
    $params = array_merge($this->params, $params);

    $param_str = '';
    foreach($params as $name => $val) {
      $param_str .= urlencode($name) .'='. urlencode($val) .'&';
    }
    $param_str = rtrim($param_str, '&');

    $url = '';
    $url .= isset($this->parts['scheme']) ? $this->parts['scheme'] .'://': '';
    $url .= isset($this->parts['user']) ? $this->parts['user'] : '';
    $url .= isset($this->parts['pass']) ? ':'. $this->parts['pass'] : '';
    $url .= isset($this->parts['user']) ? '@' : '';
    $url .= isset($this->parts['host']) ? $this->parts['host'] : '';
    $url .= isset($this->parts['port']) && $this->parts['port'] != '80' ? ':'.$this->parts['port'] : '';
    $url .= isset($this->parts['path']) ? $this->parts['path'] : '';
    $url .= !empty($param_str) ? '?'. $param_str : '';
    $url .= isset($this->parts['fragment']) ? '#'. $this->parts['fragment'] : '';

    return $url;
  }
}
