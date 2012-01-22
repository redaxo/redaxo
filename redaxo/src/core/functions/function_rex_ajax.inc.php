<?php

/**
 * Returns true if the request is a XMLHttpRequest.
 *
 * This only works if your javaScript library sets an X-Requested-With HTTP header.
 * This is the case with Prototype, Mootools, jQuery, and perhaps others.
 *
 * Inspired by a method of the symfony framework.
 *
 * @return bool true if the request is an XMLHttpRequest, false otherwise
 */
function rex_isXmlHttpRequest()
{
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}
