<?php

/**
 * Returns true if the request is a XMLHttpRequest.
 *
 * It works if your JavaScript library set an X-Requested-With HTTP header.
 * Works with Prototype, Mootools, jQuery, and perhaps others.
 *
 * Inspired by a method of the symfony framework.
 *
 * @return bool true if the request is an XMLHttpRequest, false otherwise
 */
function rex_isXmlHttpRequest()
{
  return $_SERVER['X_REQUESTED_WITH'] == 'XMLHttpRequest';
}
