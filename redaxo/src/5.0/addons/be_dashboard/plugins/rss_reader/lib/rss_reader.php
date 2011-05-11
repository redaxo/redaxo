<?php

/**
 * RSS Reader Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 * @version svn:$Id$
 */


/**
 * There are two ways that you can create a new rssReader object. The first
 * is by passing a feed URL as a parameter to the constructor
 * (as well as optionally setting the cache location and cache expiry). This
 * will initialise the whole feed with all of the default settings, and you
 * can begin accessing methods and properties immediately.
 *
 * The second way is to create the rssReader object with no parameters
 * at all. This will enable you to set configuration options. After setting
 * them, you must initialise the feed using $feed->init(). At that point the
 * object's methods and properties will be available to you. This format is
 * what is used throughout this documentation.
 *
 * @param string $feed_url This is the URL you want to parse.
 * @param string $cache_location This is where you want the cache to be stored.
 * @param int $cache_duration This is the number of seconds that you want to store the cache file for.
 */
class rex_rssReader extends SimplePie
{
  public function __construct($feed_url = null, $cache_location = null, $cache_duration = null)
  {
    global $REX;

    parent::__construct();

    if($cache_location == null)
    {
      $cache_location = rex_path::cache('dashboard/');
    }

    $this->set_feed_url($feed_url);
    $this->set_cache_location($cache_location);
    $this->set_cache_duration($cache_duration);

    $this->init();
  }
}