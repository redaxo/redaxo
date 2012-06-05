<?php

/**
 * Version
 *
 * @author jan@kristinus.de
 *
 * @package redaxo5
 */

$mypage = 'version';

rex_perm::register('version[live_version]', null, rex_perm::OPTIONS);

// ***** an EPs andocken
rex_extension::register('ART_INIT', 'rex_version_initArticle');
function rex_version_initArticle($params)
{
  $version = rex_request('rex_version', 'int');
  if ($version != 1)
    return;

  if (!isset($_SESSION))
    session_start();

  if (!rex_backend_login::hasSession()) {
    echo 'no permission for the working version';
    exit();
  }

  $params['article']->setSliceRevision($version);
  if (is_a($params['article'], 'rex_article_content')) {
    $params['article']->getContentAsQuery();
  }
  $params['article']->setEval(true);
}

rex_extension::register('PAGE_CONTENT_HEADER', 'rex_version_header');
function rex_version_header($params)
{

  $return = '';

  $rex_version_article = rex::getProperty('login')->getSessionVar('rex_version_article');
  if (!is_array($rex_version_article))
    $rex_version_article = array();

  $working_version_empty = true;
  $gw = rex_sql::factory();
  $gw->setQuery('select * from ' . rex::getTablePrefix() . 'article_slice where article_id=' . $params['article_id'] . ' and clang=' . $params['clang'] . ' and revision=1 LIMIT 1');
  if ($gw->getRows() > 0)
    $working_version_empty = false;

  $revisions = array();
  $revisions[0] = rex_i18n::msg('version_liveversion');
  $revisions[1] = rex_i18n::msg('version_workingversion');

  $version_id = rex_request('rex_set_version', 'int', '-1');

  if ($version_id === 0) {
      $rex_version_article[$params['article_id']] = 0;
  } elseif ($version_id == 1) {
      $rex_version_article[$params['article_id']] = 1;
  } elseif (!isset($rex_version_article[$params['article_id']])) {
      $rex_version_article[$params['article_id']] = 1;
  }

  $func = rex_request('rex_version_func', 'string');
  switch ($func) {
    case 'copy_work_to_live':
      if ($working_version_empty) {
        $return .= rex_view::warning(rex_i18n::msg('version_warning_working_version_to_live'));
      } elseif (rex::getUser()->hasPerm('version[live_version]')) {
        rex_article_revision::copyContent($params['article_id'], $params['clang'], rex_article_revision::WORK, rex_article_revision::LIVE);
        $return .= rex_view::info(rex_i18n::msg('version_info_working_version_to_live'));
      }
    break;
    case 'copy_live_to_work':
      rex_article_revision::copyContent($params['article_id'], $params['clang'], rex_article_revision::LIVE, rex_article_revision::WORK);
      $return .= rex_view::info(rex_i18n::msg('version_info_live_version_to_working'));
    break;
  }

  if (!rex::getUser()->hasPerm('version[live_version]')) {
    $rex_version_article[$params['article_id']] = 1;
    unset($revisions[0]);
  }

  rex::getProperty('login')->setSessionVar('rex_version_article', $rex_version_article);

  $link = 'index.php?page=' . $params['page'] . '&article_id=' . $params['article_id'] . '&clang=' . $params['clang'];

  $return .= '
    <div id="rex-version-header" class="rex-toolbar rex-toolbar-has-form rex-version-revision-' . $rex_version_article[$params['article_id']] . '">
        <div class="rex-toolbar-content rex-version-header">

        <form action="index.php" method="post">
        <fieldset>
        <input type="hidden" name="page" value="' . $params['page'] . '" />
        <input type="hidden" name="mode" value="' . $params['mode'] . '" />
        <input type="hidden" name="article_id" value="' . $params['article_id'] . '" />
        <input type="hidden" name="clang" value="' . $params['clang'] . '" />
        <input type="hidden" name="ctype" value="' . $params['ctype'] . '" />
  ';

  $s = new rex_select();
  foreach ($revisions as $k => $r)
    $s->addOption($r, $k);
  $s->setSelected($rex_version_article[$params['article_id']]);
  $s->setName('rex_set_version');
  $s->setId('rex-select-version-id');
  $s->setSize('1');
  $s->setAttribute('onchange', 'this.form.submit();');

  if (!rex::getUser()->hasPerm('version[live_version]')) {
    $s->setDisabled();
  }

  $return .= '<ul class="rex-display-inline">';
  $return .= '<li class="rex-navi-first"><label for="rex-select-version-id">' . rex_i18n::msg('version') . ':</label> ' . $s->get() . '</li>';

  if (!rex::getUser()->hasPerm('version[live_version]')) {
    if ($rex_version_article[$params['article_id']] > 0) {
      $return .= '<li><a href="' . $link . '&rex_version_func=copy_live_to_work">' . rex_i18n::msg('version_copy_from_liveversion') . '</a></li>';
      $return .= '<li><a href="' . rex_getUrl($params['article_id'], $params['clang'], array('rex_version' => 1)) . '" target="_blank">' . rex_i18n::msg('version_preview') . '</a></li>';
    }
  } else {
    if ($rex_version_article[$params['article_id']] > 0) {
      if (!$working_version_empty)
        $return .= '<li><a href="' . $link . '&rex_version_func=copy_work_to_live">' . rex_i18n::msg('version_working_to_live') . '</a></li>';
      $return .= '<li><a href="' . rex_getUrl($params['article_id'], $params['clang'], array('rex_version' => 1)) . '" target="_blank">' . rex_i18n::msg('version_preview') . '</a></li>';
    } else {
      $return .= '<li><a href="' . $link . '&rex_version_func=copy_live_to_work" data-confirm="' . rex_i18n::msg('version_confirm_copy_live_to_workingversion') . '">' . rex_i18n::msg('version_copy_live_to_workingversion') . '</a></li>';
    }
  }
  $return .= '</ul>';

  $return .= '

          <noscript>
            <input type="submit" />
          </noscript>
        </fieldset>
        </form>

      </div>
      <div class="rex-clearer"></div>

<style type="text/css">
  /* <![CDATA[ */
    #rex-version-header label {
      font-weight: bold;
    }
    #rex-version-header li {
      margin-right: 15px;
    }
    div.rex-version-revision-0 {
      background-color:#bbddaa;
    }
    div.rex-version-revision-1 {
      background-color:#EFECD1;
    }
  /* ]]> */
</style>

    </div>
  ';

  $params['slice_revision'] = $rex_version_article[$params['article_id']];

  return $return;
}
