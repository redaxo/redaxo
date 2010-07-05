<?php

/**
 * Twitter class
 *
 * This source file can be used to communicate with Twitter (http://twitter.com)
 *
 * The class is documented in the file itself. If you find any bugs help me out and report them. Reporting can be done by sending an email to php-twitter-bugs[at]verkoyen[dot]eu.
 * If you report a bug, make sure you give me enough information (include your code).
 *
 * Changelog since 1.0.0
 * - dont send postfields as an array, pass them as an urldecoded string (otherwise @ won't work
 *
 * Changelog since 1.0.1
 * - fixed a bug in verifyCredentials, it return a boolean instead of throwing an exception when the credentials are invalid (thx @Rahul)
 *
 * Changelog since 1.0.2
 * - sinceId is from now on treated as a string instead of int. (thx @Paul Matthews)
 *
 * Changelog since 1.0.3
 * - rewrote some comments
 * - fixed some PHPDoc
 * - it seems Twitter removed the $since-parameter, so I removed it from getFriendsTimeline, getUserTimeline, getDirectMessages, getSentDirectMessages, ...
 * - implemented maxId into getFriendsTimeline
 * - renamed getReplies to getMentions to reflect the Twitter API
 * - added $count for getDirectMessages, getSentDirectMessages, ...
 * - added getFriendship which shows more details about a friendship
 * - added getFriendIds and getFollowerIds which return only the ids instead of a user-array
 * - added existsBlock which test if a block exists
 * - added getBlocked, which returns an array of blocked user-arrays
 * - added getBlockedIds, which returns an array of blocked ids
 *
 * Changelog since 1.0.4
 * - renamed verifyCrendentials to verifyCredentials (typo)
 *
 *
 * License
 * Copyright (c) 2008, Tijs Verkoyen. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.
 *
 * This software is provided by the author "as is" and any express or implied warranties, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose are disclaimed. In no event shall the author be liable for any direct, indirect, incidental, special, exemplary, or consequential damages (including, but not limited to, procurement of substitute goods or services; loss of use, data, or profits; or business interruption) however caused and on any theory of liability, whether in contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of this software, even if advised of the possibility of such damage.
 *
 * @author    Tijs Verkoyen <php-twitter@verkoyen.eu>
 * @version   1.0.5
 *
 * @copyright Copyright (c) 2008, Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class Twitter
{
  // internal constant to enable/disable debugging
  const DEBUG = false;

  // url for the twitter-api
  const TWITTER_API_URL = 'http://twitter.com';

  // port for the twitter-api
  const TWITTER_API_PORT = 80;

  // current version
  const VERSION = '1.0.5';


  /**
   * The password for an authenticating user
   *
   * @var string
   */
  private $password;


  /**
   * The timeout
   *
   * @var int
   */
  private $timeOut = 60;


  /**
   * The user agent
   *
   * @var string
   */
  private $userAgent;


  /**
   * The username for an authenticating user
   *
   * @var string
   */
  private $username;


// class methods
  /**
   * Default constructor
   *
   * @return  void
   * @param string[optional] $username  The username for an authenticating user
   * @param string[optional] $password  The password for an authenticating user
   */
  public function __construct($username = null, $password = null)
  {
    if($username !== null) $this->setUsername($username);
    if($password !== null) $this->setPassword($password);
  }


  /**
   * Make the call
   *
   * @return  string
   * @param string $url
   * @param array[optiona] $aParameters
   * @param bool[optional] $authenticate
   * @param bool[optional] $usePost
   */
  private function doCall($url, $aParameters = array(), $authenticate = false, $usePost = true)
  {
    // redefine
    $url = (string) $url;
    $aParameters = (array) $aParameters;
    $authenticate = (bool) $authenticate;
    $usePost = (bool) $usePost;

    // build url
    $url = self::TWITTER_API_URL .'/'. $url;

    // validate needed authentication
    if($authenticate && ($this->getUsername() == '' || $this->getPassword() == '')) throw new TwitterException('No username or password was set.');

    // rebuild url if we don't use post
    if(!empty($aParameters) && !$usePost)
    {
      // init var
      $queryString = '';

      // loop parameters and add them to the queryString
      foreach($aParameters as $key => $value) $queryString .= '&'. $key .'='. urlencode(utf8_encode($value));

      // cleanup querystring
      $queryString = trim($queryString, '&');

      // append to url
      $url .= '?'. $queryString;
    }

    // set options
    $options[CURLOPT_URL] = $url;
    $options[CURLOPT_PORT] = self::TWITTER_API_PORT;
    $options[CURLOPT_USERAGENT] = $this->getUserAgent();
    $options[CURLOPT_FOLLOWLOCATION] = true;
    $options[CURLOPT_RETURNTRANSFER] = true;
    $options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();

    // should we authenticate?
    if($authenticate)
    {
      $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
      $options[CURLOPT_USERPWD] = $this->getUsername() .':'. $this->getPassword();
    }

    // are there any parameters?
    if(!empty($aParameters) && $usePost)
    {
      $var = '';

      // rebuild parameters
      foreach($aParameters as $key => $value) $var .= '&'. $key .'='. urlencode($value);

      // set extra options
      $options[CURLOPT_POST] = true;
      $options[CURLOPT_POSTFIELDS] = trim($var, '&');

      // Probaly Twitter's webserver doesn't support the Expect: 100-continue header. So we reset it.
      $options[CURLOPT_HTTPHEADER] = array('Expect:');
    }

    // init
    $curl = curl_init();

    // set options
    curl_setopt_array($curl, $options);

    // execute
    $response = curl_exec($curl);
    $headers = curl_getinfo($curl);

    // fetch errors
    $errorNumber = curl_errno($curl);
    $errorMessage = curl_error($curl);

    // close
    curl_close($curl);

    // validate body
    $xml = @simplexml_load_string($response);
    if($xml !== false && isset($xml->error)) throw new TwitterException((string) $xml->error);

    // invalid headers
    if(!in_array($headers['http_code'], array(0, 200)))
    {
      // should we provide debug information
      if(self::DEBUG)
      {
        // make it output proper
        echo '<pre>';

        // dump the header-information
        var_dump($headers);

        // dump the raw response
        var_dump($response);

        // end proper format
        echo '</pre>';

        // stop the script
        exit;
      }

      // throw error
      throw new TwitterException(null, (int) $headers['http_code']);
    }

    // error?
    if($errorNumber != '') throw new TwitterException($errorMessage, $errorNumber);

    // return
    return $response;
  }


  /**
   * Get the password
   *
   * @return  string
   */
  private function getPassword()
  {
    return (string) $this->password;
  }


  /**
   * Get the timeout
   *
   * @return  int
   */
  public function getTimeOut()
  {
    return (int) $this->timeOut;
  }


  /**
   * Get the useragent that will be used. Our version will be prepended to yours.
   * It will look like: "PHP Akismet/<version> <your-user-agent>"
   *
   * @return  string
   */
  public function getUserAgent()
  {
    return (string) 'PHP Twitter/'. self::VERSION .' '. $this->userAgent;
  }


  /**
   * Get the username
   *
   * @return  string
   */
  private function getUsername()
  {
    return (string) $this->username;
  }


  /**
   * Converts a piece of XML into a message-array
   *
   * @return  array
   * @param SimpleXMLElement $xml
   */
  private function messageXMLToArray($xml)
  {
    // validate xml
    if(!isset($xml->id, $xml->text, $xml->created_at, $xml->sender, $xml->recipient)) throw new TwitterException('Invalid xml for message.');

    // convert into array
    $aMessage['id'] = (string) $xml->id;
    $aMessage['created_at'] = (int) strtotime($xml->created_at);
    $aMessage['text'] = (string) utf8_decode($xml->text);
    $aMessage['sender'] = $this->userXMLToArray($xml->sender);
    $aMessage['recipient'] = $this->userXMLToArray($xml->recipient);

    // return
    return $aMessage;
  }


  /**
   * Set password
   *
   * @return  void
   * @param string $password
   */
  private function setPassword($password)
  {
    $this->password = (string) $password;
  }


  /**
   * Set the timeout
   *
   * @return  void
   * @param int $seconds  The timeout in seconds
   */
  public function setTimeOut($seconds)
  {
    $this->timeOut = (int) $seconds;
  }


  /**
   * Get the useragent that will be used. Our version will be prepended to yours.
   * It will look like: "PHP Akismet/<version> <your-user-agent>"
   *
   * @return  void
   * @param string $userAgent Your user-agent, it should look like <app-name>/<app-version>
   */
  public function setUserAgent($userAgent)
  {
    $this->userAgent = (string) $userAgent;
  }


  /**
   * Set username
   *
   * @return  void
   * @param string $username
   */
  private function setUsername($username)
  {
    $this->username = (string) $username;
  }


  /**
   * Converts a piece of XML into a status-array
   *
   * @return  array
   * @param SimpleXMLElement $xml
   */
  private function statusXMLToArray($xml)
  {
    // validate xml
    if(!isset($xml->id, $xml->text, $xml->created_at, $xml->source, $xml->truncated, $xml->in_reply_to_status_id, $xml->in_reply_to_user_id, $xml->favorited, $xml->user)) throw new TwitterException('Invalid xml for message.');

    // convert into array
    $aStatus['id'] = (string) $xml->id;
    $aStatus['created_at'] = (int) strtotime($xml->created_at);
    $aStatus['text'] = utf8_decode((string) $xml->text);
    $aStatus['source'] = (isset($xml->source)) ? (string) $xml->source : '';
    $aStatus['user'] = $this->userXMLToArray($xml->user);
    $aStatus['truncated'] = (isset($xml->truncated) && $xml->truncated == 'true');
    $aStatus['favorited'] = (isset($xml->favorited) && $xml->favorited == 'true');
    $aStatus['in_reply_to_status_id'] = (string) $xml->in_reply_to_status_id;
    $aStatus['in_reply_to_user_id'] = (string) $xml->in_reply_to_user_id;

    // return
    return $aStatus;
  }


  /**
   * Converts a piece of XML into an user-array
   *
   * @return  array
   * @param SimpleXMLElement $xml
   */
  private function userXMLToArray($xml, $extended = false)
  {
    // validate xml
    if(!isset($xml->id, $xml->name, $xml->screen_name, $xml->description, $xml->location, $xml->profile_image_url, $xml->url, $xml->protected, $xml->followers_count)) throw new TwitterException('Invalid xml for message.');


    // convert into array
    $aUser['id'] = (string) $xml->id;
    $aUser['name'] = utf8_decode((string) $xml->name);
    $aUser['screen_name'] = utf8_decode((string) $xml->screen_name);
    $aUser['description'] = utf8_decode((string) $xml->description);
    $aUser['location'] = utf8_decode((string) $xml->location);
    $aUser['url'] = (string) $xml->url;
    $aUser['protected'] = (isset($xml->protected) && $xml->protected == 'true');
    $aUser['followers_count'] = (int) $xml->followers_count;
    $aUser['profile_image_url'] = (string) $xml->profile_image_url;

    // extended info?
    if($extended)
    {
      if(isset($xml->profile_background_color)) $aUser['profile_background_color'] = utf8_decode((string) $xml->profile_background_color);
      if(isset($xml->profile_text_color)) $aUser['profile_text_color'] = utf8_decode((string) $xml->profile_text_color);
      if(isset($xml->profile_link_color)) $aUser['profile_link_color'] = utf8_decode((string) $xml->profile_link_color);
      if(isset($xml->profile_sidebar_fill_color)) $aUser['profile_sidebar_fill_color'] = utf8_decode((string) $xml->profile_sidebar_fill_color);
      if(isset($xml->profile_sidebar_border_color)) $aUser['profile_sidebar_border_color'] = utf8_decode((string) $xml->profile_sidebar_border_color);
      if(isset($xml->profile_background_image_url)) $aUser['profile_background_image_url'] = utf8_decode((string) $xml->profile_background_image_url);
      if(isset($xml->profile_background_tile)) $aUser['profile_background_tile'] = (isset($xml->profile_background_tile) && $xml->profile_background_tile == 'true');
      if(isset($xml->created_at)) $aUser['created_at'] = (int) strtotime((string) $xml->created_at);
      if(isset($xml->following)) $aUser['following'] = (isset($xml->following) && $xml->following == 'true');
      if(isset($xml->notifications)) $aUser['notifications'] = (isset($xml->notifications) && $xml->notifications == 'true');
      if(isset($xml->statuses_count)) $aUser['statuses_count'] = (int) $xml->statuses_count;
      if(isset($xml->friends_count)) $aUser['friends_count'] =  (int) $xml->friends_count;
      if(isset($xml->favourites_count)) $aUser['favourites_count'] = (int) $xml->favourites_count;
      if(isset($xml->time_zone)) $aUser['time_zone'] = utf8_decode((string) $xml->time_zone);
      if(isset($xml->utc_offset)) $aUser['utc_offset'] = (int) $xml->utc_offset;
    }

    // return
    return (array) $aUser;
  }


// timeline methods
  /**
   * Returns the 20 most recent statuses from non-protected users who have set a custom user icon.
   * Note that the public timeline is cached for 60 seconds so requesting it more often than that is a waste of resources.
   *
   * @return  array
   */
  public function getPublicTimeline()
  {
    // do the call
    $response = $this->doCall('statuses/public_timeline.xml');

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aStatuses = array();

    // loop statuses
    foreach ($xml->status as $status) $aStatuses[] = $this->statusXMLToArray($status);

    // return
    return (array) $aStatuses;
  }


  /**
   * Returns the 20 most recent statuses posted by the authenticating user and that user's friends.
   * This is the equivalent of /home on the Web.
   *
   * @return  array
   * @param string[optional] $sinceId Returns only statuses with an id greater than (that is, more recent than) the specified $sinceId.
   * @param string[optional] $maxId Returns only statuses with an ID less than (that is, older than) or equal to the specified $maxId.
   * @param int[optional] $count  Specifies the number of statuses to retrieve. May not be greater than 200.
   * @param int[optional] $page
   */
  public function getFriendsTimeline($sinceId = null, $maxId = null, $count = null, $page = null)
  {
    // validate parameters
    if($sinceId !== null && (string) $sinceId == '') throw new TwitterException('Invalid value for sinceId.');
    if($maxId !== null && (string) $maxId == '') throw new TwitterException('Invalid value for maxId.');
    if($count !== null && (int) $count > 200) throw new TwitterException('Count can\'t be larger then 200.');

    // build url
    $aParameters = array();
    if($sinceId !== null) $aParameters['since_id'] = (string) $sinceId;
    if($maxId !== null) $aParameters['max_id'] = (string) $maxId;
    if($count !== null) $aParameters['count'] = (int) $count;
    if($page !== null) $aParameters['page'] = (int) $page;

    // do the call
    $response = $this->doCall('statuses/friends_timeline.xml', $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aStatuses = array();

    // loop statuses
    foreach ($xml->status as $status) $aStatuses[] = $this->statusXMLToArray($status);

    // return
    return (array) $aStatuses;
  }


  /**
   * Returns the 20 most recent statuses posted from the authenticating user. It's also possible to request another user's timeline via the id parameter below.
   * This is the equivalent of the Web /archive page for your own user, or the profile page for a third party.
   *
   * @return  array
   * @param string[optional] $id  Specifies the id or screen name of the user for whom to return the friends_timeline.
   * @param string[optional] $sinceId Returns only statuses with an id greater than (that is, more recent than) the specified $sinceId.
   * @param string[optional] $maxId Returns only statuses with an ID less than (that is, older than) or equal to the specified $maxId.
   * @param int[optional] $count  Specifies the number of statuses to retrieve. May not be greater than 200.
   * @param int[optional] $page Specifies the page or results to retrieve.
   */
  public function getUserTimeline($id = null, $sinceId = null, $maxId = null, $count = null, $page = null)
  {
    // validate parameters
    if($sinceId !== null && (string) $sinceId == '') throw new TwitterException('Invalid value for sinceId.');
    if($maxId !== null && (string) $maxId == '') throw new TwitterException('Invalid value for maxId.');
    if($count !== null && (int) $count > 200) throw new TwitterException('Count can\'t be larger then 200.');

    // build parameters
    $aParameters = array();
    if($sinceId !== null) $aParameters['since_id'] = (string) $sinceId;
    if($maxId !== null) $aParameters['max_id'] = (string) $maxId;
    if($count !== null) $aParameters['count'] = (int) $count;
    if($page !== null) $aParameters['page'] = (int) $page;

    // build url
    $url = 'statuses/user_timeline.xml';
    if($id !== null) $url = 'statuses/user_timeline/'. urlencode($id) .'.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aStatuses = array();

    // loop statuses
    foreach ($xml->status as $status) $aStatuses[] = $this->statusXMLToArray($status);

    // return
    return (array) $aStatuses;
  }


  /**
   * Returns the 20 most recent mentions (status containing @username) for the authenticating user.
   *
   * @return  array
   * @param string[optional] $sinceId Returns only statuses with an id greater than (that is, more recent than) the specified $sinceId.
   * @param string[optional] $maxId Returns only statuses with an ID less than (that is, older than) or equal to the specified $maxId.
   * @param int[optional] $count  Specifies the number of statuses to retrieve. May not be greater than 200.
   * @param int[optional] $page Specifies the page or results to retrieve.
   */
  public function getMentionsReplies($sinceId = null, $maxId = null, $count = null, $page = null)
  {
    // validate parameters
    if($sinceId !== null && (string) $sinceId == '') throw new TwitterException('Invalid value for sinceId.');
    if($maxId !== null && (string) $maxId == '') throw new TwitterException('Invalid value for maxId.');
    if($count !== null && (int) $count > 200) throw new TwitterException('Count can\'t be larger then 200.');

    // build parameters
    $aParameters = array();
    if($sinceId !== null) $aParameters['since_id'] = (string) $sinceId;
    if($maxId !== null) $aParameters['max_id'] = (string) $maxId;
    if($count !== null) $aParameters['count'] = (int) $count;
    if($page !== null) $aParameters['page'] = (int) $page;

    // do the call
    $response = $this->doCall('statuses/mentions.xml', $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aStatuses = array();

    // loop statuses
    foreach ($xml->status as $status) $aStatuses[] = $this->statusXMLToArray($status);

    // return
    return (array) $aStatuses;
  }


// status methods
  /**
   * Returns a single status, specified by the id parameter below.
   *
   * @return  array
   * @param int $id The numerical id of the status you're trying to retrieve.
   */
  public function getStatus($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'statuses/show/'. urlencode($id) .'.xml';

    // do the call
    $response = $this->doCall($url);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->statusXMLToArray($xml);
  }


  /**
   * Updates the authenticating user's status.
   * A status update with text identical to the authenticating user's current status will be ignored.
   *
   * @return  array
   * @param string $status  The text of your status update. Should not be more than 140 characters.
   * @param int[optional] $inReplyToId  The id of an existing status that the status to be posted is in reply to.
   */
  public function updateStatus($status, $inReplyToId = null)
  {
    // redefine
    $status = (string) $status;

    // validate parameters
    if(strlen($status) > 140) throw new TwitterException('Maximum 140 characters allowed for status.');

    // build parameters
    $aParameters = array();
    $aParameters['status'] = $status;
    if($inReplyToId !== null) $aParameters['in_reply_to_status_id'] = (int) $inReplyToId;

    // do the call
    $response = $this->doCall('statuses/update.xml', $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->statusXMLToArray($xml);
  }


  /**
   * Destroys the status specified by the required $id parameter.
   * The authenticating user must be the author of the specified status.
   *
   * @return  array
   * @param int[optional] $id
   */
  public function deleteStatus($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'statuses/destroy/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->statusXMLToArray($xml);
  }


// user methods
  /**
   * Returns extended information of a given user, specified by id or screen name.
   * This information includes design settings, so third party developers can theme their widgets according to a given user's preferences.
   * You must be properly authenticated to request the page of a protected user.
   *
   * @return  array
   * @param string $id  The id or screen name of a user.
   */
  public function getUser($id)
  {
    // build parameters
    $aParameters = array();

    // build url
    $url = 'users/show/'. urlencode($id) .'.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml, true);
  }


  /**
   * Returns up to 100 of the authenticating user's friends who have most recently updated.
   * It's also possible to request another user's recent friends list via the $id parameter.
   *
   * @return  array
   * @param string[optional] $id  The id or screen name of the user for whom to request a list of friends.
   * @param int[optional] $page Specifies the page of friends to receive.
   */
  public function getFriends($id = null, $cursor = null)
  {
    // build parameters
    $aParameters = array();
    if($page !== null) $aParameters['page'] = (int) $page;

    // build url
    $url = 'statuses/friends.xml';
    if($id !== null) $url = 'statuses/friends/'. urlencode($id) .'.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aUsers = array();

    // loop statuses
    foreach ($xml->user as $user) $aUsers[] = $this->userXMLToArray($user);

    // return
    return (array) $aUsers;
  }


  /**
   * Returns the authenticating user's followers.
   *
   * @return  array
   * @param string[optional] $id   The id or screen name of the user for whom to request a list of followers.
   * @param int[optional] $page
   */
  public function getFollowers($id = null, $page = null)
  {
    // build parameters
    $aParameters = array();
    if($page !== null) $aParameters['page'] = (int) $page;

    // build url
    $url = 'statuses/followers.xml';
    if($id !== null) $url = 'statuses/followers/'. urlencode($id) .'.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aUsers = array();

    // loop statuses
    foreach ($xml->user as $user) $aUsers[] = $this->userXMLToArray($user);

    // return
    return (array) $aUsers;
  }



// direct message methods
  /**
   * Returns a list of the 20 most recent direct messages sent to the authenticating user.
   *
   * @return  array
   * @param string[optional] $sinceId Returns only direct messages with an id greater than (that is, more recent than) the specified $sinceId.
   * @param string[optional] $maxId Returns only statuses with an ID less than (that is, older than) or equal to the specified $maxId.
   * @param int[optional] $count  Specifies the number of statuses to retrieve. May not be greater than 200.
   * @param int[optional] $page
   */
  public function getDirectMessages($sinceId = null, $maxId = null, $count = null, $page = null)
  {
    // validate parameters
    if($sinceId !== null && (string) $sinceId == '') throw new TwitterException('Invalid value for sinceId.');
    if($maxId !== null && (string) $maxId == '') throw new TwitterException('Invalid value for maxId.');
    if($count !== null && (int) $count > 200) throw new TwitterException('Count can\'t be larger then 200.');

    // build url
    $aParameters = array();
    if($sinceId !== null) $aParameters['since_id'] = (string) $sinceId;
    if($maxId !== null) $aParameters['max_id'] = (string) $maxId;
    if($count !== null) $aParameters['count'] = (int) $count;
    if($page !== null) $aParameters['page'] = (int) $page;

    // do the call
    $response = $this->doCall('direct_messages.xml', $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aDirectMessages = array();

    // loop statuses
    foreach ($xml->direct_message as $message) $aDirectMessages[] = $this->messageXMLToArray($message);

    // return
    return (array) $aDirectMessages;
  }


  /**
   * Returns a list of the 20 most recent direct messages sent by the authenticating user.
   *
   * @return  array
   * @param string[optional] $sinceId Returns only sent direct messages with an id greater than (that is, more recent than) the specified $sinceId.
   * @param string[optional] $maxId Returns only statuses with an ID less than (that is, older than) or equal to the specified $maxId.
   * @param int[optiona] $count Specifies the number of direct messages to retrieve. May not be greater than 200.
   * @param int[optional] $page
   */
  public function getSentDirectMessages($sinceId = null, $maxId = null, $count = null, $page = null)
  {
    // validate parameters
    if($sinceId !== null && (string) $sinceId == '') throw new TwitterException('Invalid value for sinceId.');
    if($maxId !== null && (string) $maxId == '') throw new TwitterException('Invalid value for maxId.');
    if($count !== null && (int) $count > 200) throw new TwitterException('Count can\'t be larger then 200.');

    // build url
    $aParameters = array();
    if($sinceId !== null) $aParameters['since_id'] = (string) $sinceId;
    if($maxId !== null) $aParameters['max_id'] = (string) $maxId;
    if($count !== null) $aParameters['count'] = (int) $count;
    if($page !== null) $aParameters['page'] = (int) $page;

    // do the call
    $response = $this->doCall('direct_messages/sent.xml', $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aDirectMessages = array();

    // loop statuses
    foreach ($xml->direct_message as $message) $aDirectMessages[] = $this->messageXMLToArray($message);

    // return
    return (array) $aDirectMessages;
  }


  /**
   * Sends a new direct message to the specified user from the authenticating user.
   *
   * @return  array
   * @param string $id  The id or screen name of the recipient user.
   * @param string $text  The text of your direct message. Keep it under 140 characters.
   */
  public function sendDirectMessage($id, $text)
  {
    // redefine
    $id = (string) $id;
    $text = (string) $text;

    // validate parameters
    if(strlen($text) > 140) throw new TwitterException('Maximum 140 characters allowed for status.');

    // build parameters
    $aParameters = array();
    $aParameters['user'] = $id;
    $aParameters['text'] = $text;

    // do the call
    $response = $this->doCall('direct_messages/new.xml', $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->messageXMLToArray($xml);
  }


  /**
   * Destroys the direct message.
   * The authenticating user must be the recipient of the specified direct message.
   *
   * @return  array
   * @param string $id
   */
  public function deleteDirectMessage($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'direct_messages/destroy/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->messageXMLToArray($xml);
  }


// friendship methods
  /**
   * Befriends the user specified in the id parameter as the authenticating user.
   *
   * @return  array
   * @param string $id  The id or screen name of the user to befriend.
   * @param bool[optional] $follow  Enable notifications for the target user in addition to becoming friends.
   */
  public function createFriendship($id, $follow = true)
  {
    // redefine
    $id = (string) $id;
    $follow = (bool) $follow;

    // build url
    $url = 'friendships/create/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;
    if($follow) $aParameters['follow'] = $follow;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml);
  }


  /**
   * Discontinues friendship with the user.
   *
   * @return  array
   * @param string $id
   */
  public function deleteFriendship($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'friendships/destroy/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml);
  }


  /**
   * Tests if a friendship exists between two users.
   *
   * @return  bool
   * @param string $id  The id or screen_name of the first user to test friendship for.
   * @param string $friendId  The id or screen_name of the second user to test friendship for.
   */
  public function existsFriendship($id, $friendId)
  {
    // redefine
    $id = (string) $id;
    $friendId = (string) $friendId;

    // build parameters
    $aParameters = array();
    $aParameters['user_a'] = (string) $id;
    $aParameters['user_b'] = (string) $friendId;

    // do the call
    $response = $this->doCall('friendships/exists.xml', $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (bool) ($xml == 'true');
  }


  /**
   * Returns detailed information about the relationship between two users.
   *
   * @return  array
   * @param string $id  The id or screen name of the subject user.
   * @param string $friendId  The id or screen name of the target user.
   */
  public function getFriendship($id, $friendId)
  {
    // redefine
    $id = (string) $id;
    $friendId = (string) $friendId;

    // build parameters
    $aParameters = array();
    if((bool) preg_match("/^[0-9]+$/", $id)) $aParameters['source_id'] = $id;
    else $aParameters['source_screen_name'] = (string) $id;
    if((bool) preg_match("/^[0-9]+$/", $friendId)) $aParameters['target_id'] = $friendId;
    else $aParameters['target_screen_name'] = $friendId;

    // do the call
    $response = $this->doCall('friendships/show.xml', $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    $aReturn = array();
    $aReturn['target']['id'] = (string) $xml->target->id;
    $aReturn['target']['screen_name'] = (string) utf8_decode($xml->target->screen_name);
    $aReturn['target']['following'] = (bool) ((string) $xml->target->following == 'true');
    $aReturn['target']['followed_by'] = (bool) ((string) $xml->target->followed_by == 'true');

    $aReturn['source']['id'] = (string) $xml->source->id;
    $aReturn['source']['screen_name'] = (string) utf8_decode($xml->source->screen_name);
    $aReturn['source']['following'] = (bool) ((string) $xml->source->following == 'true');
    $aReturn['source']['followed_by'] = (bool) ((string) $xml->source->followed_by == 'true');
    $aReturn['source']['notifications_enabled'] = (bool) ((string) $xml->source->notifications_enabled == 'true');
    $aReturn['source']['blocking'] = (bool) ((string) $xml->source->blocking == 'true');

    // return
    return (array) $aReturn;
  }


// social grap methods
  /**
   * Returns an array of numeric IDs for every user the specified user is following.
   *
   * @return  array
   * @param string[optional] $id  The id or screen name of the user for whom to request a list of friends.
   * @param int[optional] $page Specifies the page number of the results beginning at 1. A single page contains 5000 ids. This is recommended for users with large ID lists. If not provided all ids are returned. (Please note that the result set isn't guaranteed to be 5000 every time as suspended users will be filtered out.)
   */
  public function getFriendIds($id = null, $page = null)
  {
    // build parameters
    $aParameters = array();
    if($page !== null) $aParameters['page'] = (int) $page;

    // build url
    $url = 'friends/ids.xml';
    if($id !== null) $url = 'friends/ids/'. urlencode($id) .'.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aReturn = array();

    if(isset($xml->id))
    {
      // loop ids
      foreach($xml->id as $id) $aReturn[] = (string) $id;
    }

    // return
    return (array) $aReturn;
  }


  /**
   * Returns an array of numeric IDs for every user following the specified user.
   *
   * @return  array
   * @param string[optional] $id  The id or screen name  of the user to retrieve the friends ID list for.
   * @param int[optional] $page Specifies the page number of the results beginning at 1. A single page contains 5000 ids. This is recommended for users with large ID lists. If not provided all ids are returned. (Please note that the result set isn't guaranteed to be 5000 every time as suspended users will be filtered out.)
   */
  public function getFollowerIds($id = null, $page = null)
  {
    // build parameters
    $aParameters = array();
    if($page !== null) $aParameters['page'] = (int) $page;

    // build url
    $url = 'followers/ids.xml';
    if($id !== null) $url = 'followers/ids/'. urlencode($id) .'.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aReturn = array();

    if(isset($xml->id))
    {
      // loop ids
      foreach($xml->id as $id) $aReturn[] = (string) $id;
    }

    // return
    return (array) $aReturn;
  }


// account methods
  /**
   * Verifies your credentials
   * Use this method to test if supplied user credentials are valid.
   *
   * @return  bool
   */
  public function verifyCredentials()
  {
    try
    {
      // do the call
      $response = $this->doCall('account/verify_credentials.xml', array(), true);

      // content was found
      if($response != '') return true;

      // no content
      else return false;
    }
    catch (Exception $e)
    {
      if($e->getCode() == 401 || $e->getMessage() == 'Could not authenticate you.') return false;
      else throw $e;
    }
  }


  /**
   * Returns the remaining number of API requests available to the requesting user before the API limit is reached for the current hour.
   *
   * @return  array
   */
  public function getRateLimitStatus()
  {
    // do the call
    $response = $this->doCall('account/rate_limit_status.xml', array(), true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // create response
    if(isset($xml->{'remaining-hits'})) $aResponse['remaining_hits'] = (int) $xml->{'remaining-hits'};
    if(isset($xml->{'reset-time-in-seconds'})) $aResponse['reset_time'] = (int) $xml->{'reset-time-in-seconds'};
    if(isset($xml->{'hourly-limit'})) $aResponse['hourly_limit'] = (int) $xml->{'hourly-limit'};

    // return
    return $aResponse;
  }


  /**
   * Ends the session of the authenticating user, returning a null cookie.
   * Use this method to sign users out of client-facing applications like widgets.
   *
   * @return  void
   */
  public function endSession()
  {
    $this->doCall('account/end_session');
  }


  /**
   * Sets which device Twitter delivers updates to for the authenticating user.
   * Sending none as the device parameter will disable IM or SMS updates.
   *
   * @return  array
   * @param string $device  Must be one of: sms, im, none.
   */
  public function updateDeliveryDevice($device)
  {
    // redefine
    $device = (string) $device;

    // init vars
    $aPossibleDevices = array('sms', 'im', 'none');

    // validate parameters
    if(!in_array($device, $aPossibleDevices)) throw new TwitterException('Invalid value for device. Possible values are: '. implode(', ', $aPossibleDevices) .'.');

    // build url
    $url = 'account/update_delivery_device.xml';

    // build parameters
    $aParameters = array();
    $aParameters['device'] = $device;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml);
  }


  /**
   * Sets one or more hex values that control the color scheme of the authenticating user's profile page on twitter.com.
   * Only the parameters specified will be updated.
   *
   * @return  array
   * @param string[optiona] $backgroundColor
   * @param string[optiona] $textColor
   * @param string[optiona] $linkColor
   * @param string[optiona] $sidebarBackgroundColor
   * @param string[optiona] $sidebarBorderColor
   */
  public function updateProfileColors($backgroundColor = null, $textColor = null, $linkColor = null, $sidebarBackgroundColor = null, $sidebarBorderColor = null)
  {
    // validate parameters
    if($backgroundColor === null && $textColor === null && $linkColor === null && $sidebarBackgroundColor === null && $sidebarBorderColor === null) throw new TwitterException('Specify at least one parameter.');
    if($backgroundColor !== null && (strlen($backgroundColor) < 3 || strlen($backgroundColor) > 6)) throw new TwitterException('Invalid color for background color.');
    if($textColor !== null && (strlen($textColor) < 3 || strlen($textColor) > 6)) throw new TwitterException('Invalid color for text color.');
    if($linkColor !== null && (strlen($linkColor) < 3 || strlen($linkColor) > 6)) throw new TwitterException('Invalid color for link color.');
    if($sidebarBackgroundColor !== null && (strlen($sidebarBackgroundColor) < 3 || strlen($sidebarBackgroundColor) > 6)) throw new TwitterException('Invalid color for sidebar background color.');
    if($sidebarBorderColor !== null && (strlen($sidebarBorderColor) < 3 || strlen($sidebarBorderColor) > 6)) throw new TwitterException('Invalid color for sidebar border color.');

    // build parameters
    if($backgroundColor !== null) $aParameters['profile_background_color'] = (string) $backgroundColor;
    if($textColor !== null) $aParameters['profile_text_color'] = (string) $textColor;
    if($linkColor !== null) $aParameters['profile_link_color'] = (string) $linkColor;
    if($sidebarBackgroundColor !== null) $aParameters['profile_sidebar_fill_color'] = (string) $sidebarBackgroundColor;
    if($sidebarBorderColor !== null) $aParameters['profile_sidebar_border_color'] = (string) $sidebarBorderColor;

    // make the call
    $response = $this->doCall('account/update_profile_colors.xml', $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml, true);
  }


  /**
   * Updates the authenticating user's profile image.
   * Expects raw multipart data, not a URL to an image.
   *
   * @remark  not implemented yet, feel free to code
   * @return  void
   * @param string $image
   */
  public function updateProfileImage($image)
  {
    throw new TwitterException(null, 501);

    // build parameters
    $aParameters = array();
    $aParameters['image'] = (string) $image;

    // make the call
    $response = $this->doCall('account/update_profile_image.xml', $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml, true);
  }


  /**
   * Updates the authenticating user's profile background image.
   * Expects raw multipart data, not a URL to an image.
   *
   * @remark  not implemented yet, feel free to code
   * @return  void
   * @param string $image
   */
  public function updateProfileBackgroundImage($image)
  {
    throw new TwitterException(null, 501);

    // build parameters
    $aParameters = array();
    $aParameters['image'] = (string) $image;

    // make the call
    $response = $this->doCall('account/update_profile_background_image.xml', $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml, true);
  }


  /**
   * Sets values that users are able to set under the "Account" tab of their settings page.
   * Only the parameters specified will be updated.
   *
   * @return  array
   * @param string[optional] $name
   * @param string[optional] $email
   * @param string[optional] $url
   * @param string[optional] $location
   * @param string[optional] $description
   */
  public function updateProfile($name = null, $email = null, $url = null, $location = null, $description = null)
  {
    // validate parameters
    if($name === null && $email === null && $url === null && $location === null && $description === null) throw new TwitterException('Specify at least one parameter.');
    if($name !== null && strlen($name) > 40) throw new TwitterException('Maximum 40 characters allowed for name.');
    if($email !== null && strlen($email) > 40) throw new TwitterException('Maximum 40 characters allowed for email.');
    if($url !== null && strlen($url) > 100) throw new TwitterException('Maximum 100 characters allowed for url.');
    if($location !== null && strlen($location) > 30) throw new TwitterException('Maximum 30 characters allowed for location.');
    if($description !== null && strlen($description) > 160) throw new TwitterException('Maximum 160 characters allowed for description.');

    // build parameters
    if($name !== null) $aParameters['name'] = (string) $name;
    if($email !== null) $aParameters['email'] = (string) $email;
    if($url !== null) $aParameters['url'] = (string) $url;
    if($location !== null) $aParameters['location'] = (string) $location;
    if($description !== null) $aParameters['description'] = (string) $description;

    // make the call
    $response = $this->doCall('account/update_profile.xml', $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml, true);
  }


// favorite methods
  /**
   * Returns the 20 most recent favorite statuses for the authenticating user or user specified by the $id parameter
   *
   * @return  array
   * @param string[optional] $id  The id or screen name of the user for whom to request a list of favorite statuses.
   * @param int[optional] $page
   */
  public function getFavorites($id = null, $page = null)
  {
    // build parameters
    $aParameters = array();
    if($page !== null) $aParameters['page'] = (int) $page;

    $url = 'favorites.xml';
    if($id !== null) $url = 'favorites/'. urlencode($id) .'.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aStatuses = array();

    // loop statuses
    foreach ($xml->status as $status) $aStatuses[] = $this->statusXMLToArray($status);

    // return
    return (array) $aStatuses;
  }


  /**
   * Favorites the status specified in the id parameter as the authenticating user.
   *
   * @return  array
   * @param string $id
   */
  public function createFavorite($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'favorites/create/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->statusXMLToArray($xml);
  }


  /**
   * Un-favorites the status specified in the id parameter as the authenticating user.
   *
   * @return  array
   * @param string $id
   */
  public function deleteFavorite($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'favorites/destroy/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->statusXMLToArray($xml);
  }


// notification methods
  /**
   * Enables notifications for updates from the specified user to the authenticating user.
   * This method requires the authenticated user to already be friends with the specified user otherwise the error "there was a problem following the specified user" will be returned.
   *
   * @return  void
   * @param string $id
   */
  public function follow($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'notifications/follow/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml);
  }


  /**
   * Disables notifications for updates from the specified user to the authenticating user.
   * This method requires the authenticated user to already be friends with the specified user otherwise the error "there was a problem following the specified user" will be returned.
   *
   * @return  void
   * @param string $id
   */
  public function unfollow($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'notifications/leave/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml);
  }


// block methods
  /**
   * Blocks the user specified in the id parameter as the authenticating user.
   *
   * @return  void
   * @param string $id
   */
  public function createBlock($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'blocks/create/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml);
  }


  /**
   * Un-blocks the user specified in the id parameter as the authenticating user.
   *
   * @return  void
   * @param string $id
   */
  public function deleteBlock($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'blocks/destroy/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    $response = $this->doCall($url, $aParameters, true);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return (array) $this->userXMLToArray($xml);
  }


  /**
   * Returns if the authenticating user is blocking a target user.
   *
   * @return  bool
   * @param string $id  The id or screen_name of the potentially blocked user.
   */
  public function existsBlock($id)
  {
    // redefine
    $id = (string) $id;

    // build url
    $url = 'blocks/exists/'. urlencode($id) .'.xml';

    // build parameters
    $aParameters = array();
    $aParameters['id'] = $id;

    // do the call
    try
    {
      $response = $this->doCall($url, $aParameters, true, false);
    }

    // catch exceptions
    catch(Exception $e)
    {
      // not blocking
      if($e->getMessage() == 'You are not blocking this user.') return false;

      // other exceptions
      else throw $e;
    }

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // return
    return true;
  }


  /**
   * Returns an array of user that the authenticating user is blocking.
   *
   * @return  array
   * @param int[optional] $page Specifies the page number of the results beginning at 1. A single page contains 20 ids.
   */
  public function getBlocked($page = null)
  {
    // build parameters
    $aParameters = array();
    if($page !== null) $aParameters['page'] = (int) $page;

    // build url
    $url = 'blocks/blocking.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aUsers = array();

    // loop statuses
    foreach ($xml->user as $user) $aUsers[] = $this->userXMLToArray($user);

    // return
    return (array) $aUsers;
  }


  /**
   * Returns an array of numeric user ids the authenticating user is blocking.
   *
   * @return  array
   */
  public function getBlockedIds()
  {
    // build parameters
    $aParameters = array();

    // build url
    $url = 'blocks/blocking/ids.xml';

    // do the call
    $response = $this->doCall($url, $aParameters, true, false);

    // convert into xml-object
    $xml = @simplexml_load_string($response);

    // validate
    if($xml == false) throw new TwitterException('invalid body');

    // init var
    $aUsers = array();

    // loop statuses
    foreach ($xml->id as $id) $aUsers[] = (string) $id;

    // return
    return (array) $aUsers;
  }


// help methods
  /**
   * Test the connection to Twitter
   *
   * @return  bool
   */
  public function test()
  {
    // make the call
    $response = $this->doCall('help/test.xml');

    // validate response & return
    return (bool) ($response == '<ok>true</ok>');
  }
}


/**
 * Twitter Exception class
 *
 * @author  Tijs Verkoyen <php-twitter@verkoyen.eu>
 */
class TwitterException extends Exception
{
  /**
   * Http header-codes
   *
   * @var array
   */
  private $aStatusCodes = array(100 => 'Continue',
                  101 => 'Switching Protocols',
                  200 => 'OK',
                  201 => 'Created',
                  202 => 'Accepted',
                  203 => 'Non-Authoritative Information',
                  204 => 'No Content',
                  205 => 'Reset Content',
                  206 => 'Partial Content',
                  300 => 'Multiple Choices',
                  301 => 'Moved Permanently',
                  301 => 'Status code is received in response to a request other than GET or HEAD, the user agent MUST NOT automatically redirect the request unless it can be confirmed by the user, since this might change the conditions under which the request was issued.',
                  302 => 'Found',
                  302 => 'Status code is received in response to a request other than GET or HEAD, the user agent MUST NOT automatically redirect the request unless it can be confirmed by the user, since this might change the conditions under which the request was issued.',
                  303 => 'See Other',
                  304 => 'Not Modified',
                  305 => 'Use Proxy',
                  306 => '(Unused)',
                  307 => 'Temporary Redirect',
                  400 => 'Bad Request',
                  401 => 'Unauthorized',
                  402 => 'Payment Required',
                  403 => 'Forbidden',
                  404 => 'Not Found',
                  405 => 'Method Not Allowed',
                  406 => 'Not Acceptable',
                  407 => 'Proxy Authentication Required',
                  408 => 'Request Timeout',
                  409 => 'Conflict',
                  411 => 'Length Required',
                  412 => 'Precondition Failed',
                  413 => 'Request Entity Too Large',
                  414 => 'Request-URI Too Long',
                  415 => 'Unsupported Media Type',
                  416 => 'Requested Range Not Satisfiable',
                  417 => 'Expectation Failed',
                  500 => 'Internal Server Error',
                  501 => 'Not Implemented',
                  502 => 'Bad Gateway',
                  503 => 'Service Unavailable',
                  504 => 'Gateway Timeout',
                  505 => 'HTTP Version Not Supported');


  /**
   * Default constructor
   *
   * @return  void
   * @param string[optional] $message
   * @param int[optional] $code
   */
  public function __construct($message = null, $code = null)
  {
    // set message
    if($message === null && isset($this->aStatusCodes[(int) $code])) $message = $this->aStatusCodes[(int) $code];

    // call parent
    parent::__construct((string) $message, $code);
  }
}

?>