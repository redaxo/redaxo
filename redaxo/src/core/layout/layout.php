<?php
  require rex_path::core('layout/top.php');

  $params = array();
  $params['key'] = 'test';
  $fragment = new rex_fragment();
  $fragment->decorate('layout', $params);
  echo $fragment->parse('output.tpl');
  unset($fragment);

  require rex_path::core('layout/bottom.php');
