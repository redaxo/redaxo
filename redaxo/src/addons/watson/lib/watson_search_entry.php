<?php


class watson_search_entry
{
    private $value;
    private $value_suffix;
    private $icon;
    private $description;
    private $quick_look_url;
    private $url;
    private $url_open_window;



    public function __construct()
    {

    }



    /**
     * Sets a value
     *
     * @param string $value
     */
    public function setValue($value, $suffix = null)
    {
        $this->value = $value;

        if ($suffix) {
            $this->setValueSuffix($suffix);
        }
    }

    /**
     * Returns the value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns whether a value is set
     *
     * @return bool
     */
    public function hasValue()
    {
        return !empty($this->value);
    }



    /**
     * Sets a value_suffix
     *
     * @param string $value
     */
    public function setValueSuffix($value)
    {
        $this->value_suffix = $value;
    }

    /**
     * Returns the value_suffix
     *
     * @return string
     */
    public function getValueSuffix()
    {
        return $this->value_suffix;
    }

    /**
     * Returns whether a value_suffix is set
     *
     * @return bool
     */
    public function hasValueSuffix()
    {
        return !empty($this->value_suffix);
    }



    /**
     * Sets a icon
     *
     * @param string $value
     */
    public function setIcon($value)
    {
        $this->icon = $value;
    }

    /**
     * Returns the icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Returns whether a icon is set
     *
     * @return bool
     */
    public function hasIcon()
    {
        return !empty($this->icon) && file_exists($this->icon);
    }



    /**
     * Sets a description
     *
     * @param string $value
     */
    public function setDescription($value)
    {
        $this->description = $value;
    }

    /**
     * Returns the icon
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns whether a icon is set
     *
     * @return bool
     */
    public function hasDescription()
    {
        return !empty($this->description);
    }



    /**
     * Sets a url
     *
     * @param string $url
     * @param bool   $new_window
     */
    public function setUrl($url, $open_window = false)
    {
        $this->url             = htmlspecialchars_decode($url);
        $this->url_open_window = $open_window;
    }

    /**
     * Returns the url class
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns whether a url is set
     *
     * @return string
     */
    public function hasUrl()
    {
        return !empty($this->url);
    }

    /**
     * Returns the url open window class
     *
     * @return bool
     */
    public function getUrlOpenWindow()
    {
        return $this->url_open_window;
    }



    /**
     * Sets a quick look url
     *
     * @param string $value
     */
    public function setQuickLookUrl($value)
    {
        $this->quick_look_url = $value;
    }

    /**
     * Returns the quick look url class
     *
     * @return string
     */
    public function getQuickLookUrl()
    {
        return $this->quick_look_url;
    }

    /**
     * Returns whether a quick look url is set
     *
     * @return bool
     */
    public function hasQuickLookUrl()
    {
        return !empty($this->quick_look_url);
    }
}
