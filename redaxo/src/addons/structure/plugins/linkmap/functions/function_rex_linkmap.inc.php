<?php

function rex_linkmap_backlink($id, $name)
{
  return 'javascript:insertLink(\'redaxo://'.$id.'\',\''.addSlashes($name).'\');';
}