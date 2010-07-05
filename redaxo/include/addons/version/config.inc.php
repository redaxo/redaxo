<?php

/**
 * Version
 *
 * @author jan@kristinus.de
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = "version";
$REX['ADDON']['rxid'][$mypage] = '461';
// $REX['ADDON']['name'][$mypage] = 'Version';
// $REX['ADDON']['perm'][$mypage] = 'version[]';
$REX['ADDON']['version'][$mypage] = '0.2';
$REX['ADDON']['author'][$mypage] = 'Jan Kristinus';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';

$REX['EXTRAPERM'][] = 'version[only_working_version]';
// $REX['EXTPERM'][] = 'version[admin]';

if($REX['REDAXO'])
	$I18N->appendFile($REX['INCLUDE_PATH'].'/addons/version/lang/');

// ***** an EPs andocken
rex_register_extension('ART_INIT', 'rex_version_initArticle');
function rex_version_initArticle($params)
{
	global $REX;

	$version = rex_request("rex_version","int");
	if($version == "")
		return;
		
	if(!isset($_SESSION))
		session_start();

	$REX['LOGIN'] = new rex_backend_login($REX['TABLE_PREFIX'] .'user');
	if ($REX['PSWFUNC'] != '')
	  $REX['LOGIN']->setPasswordFunction($REX['PSWFUNC']);

	if ($REX['LOGIN']->checkLogin() !== true)
		return;
	
	$REX['USER'] = &$REX['LOGIN']->USER;

  $params['article']->setSliceRevision($version);
	if(is_a($params['article'], 'rex_article'))
	{
  	$params['article']->getContentAsQuery();
	}
	$params['article']->setEval(TRUE);
}

rex_register_extension('PAGE_CONTENT_HEADER', 'rex_version_header');
function rex_version_header($params)
{

	global $REX,$I18N;

  $return = "";

	$rex_version_article = $REX['LOGIN']->getSessionVar("rex_version_article");
	if(!is_array($rex_version_article))
		$rex_version_article = array();
	
	$working_version_empty = TRUE;
	$gw = rex_sql::factory();
	$gw->setQuery('select * from '.$REX['TABLE_PREFIX'].'article_slice where article_id='.$params['article_id'].' and clang='.$params['clang'].' and revision=1 LIMIT 1');
	if($gw->getRows()>0)
		$working_version_empty = FALSE;
	
	$revisions = array();
	$revisions[0] = $I18N->msg("version_liveversion");
	$revisions[1] = $I18N->msg("version_workingversion");
	
	$version_id = rex_request("rex_set_version","int","-1");

	if($version_id === 0)
	{
			$rex_version_article[$params['article_id']] = 0;
	}elseif($version_id == 1)
	{
			$rex_version_article[$params['article_id']] = 1;
	}elseif(!isset($rex_version_article[$params['article_id']]))
	{
			$rex_version_article[$params['article_id']] = 1;
	}
	
	$func = rex_request("rex_version_func","string");
	switch($func)
	{
		case("copy_work_to_live"):
		  if($working_version_empty)
		  {
		  	$return .= rex_warning($I18N->msg("version_warning_working_version_to_live"));
		  }else if(!$REX['USER']->hasPerm('version[only_working_version]'))
		  {
				require $REX['INCLUDE_PATH'].'/addons/version/functions/function_rex_copyrevisioncontent.inc.php';
				// rex_copyRevisionContent($article_id,$clang,$from_revision_id, $to_revision_id, $gc->getValue("id"),$delete_to_revision);
				rex_copyRevisionContent($params['article_id'],$params['clang'],1, 0, 0, TRUE);
		  	$return .= rex_info($I18N->msg("version_info_working_version_to_live"));
		  }
		break;
		case("copy_live_to_work"):
			require $REX['INCLUDE_PATH'].'/addons/version/functions/function_rex_copyrevisioncontent.inc.php';
			// rex_copyRevisionContent($article_id,$clang,$from_revision_id, $to_revision_id, $gc->getValue("id"),$delete_to_revision);
			rex_copyRevisionContent($params['article_id'],$params['clang'],0, 1, 0, TRUE);
		  $return .= rex_info($I18N->msg("version_info_live_version_to_working"));
		break;
	}
	
  if($REX['USER']->hasPerm('version[only_working_version]'))
  {
		$rex_version_article[$params['article_id']] = 1;
  	unset($revisions[0]);
  }

	$REX['LOGIN']->setSessionVar("rex_version_article", $rex_version_article);

	$link = 'index.php?page='.$params['page'].'&article_id='.$params['article_id'].'&clang='.$params['clang'];

	$return .= '
		<div id="rex-version-header" class="rex-toolbar rex-toolbar-has-form rex-version-revision-'.$rex_version_article[$params['article_id']].'">
				<div class="rex-toolbar-content rex-version-header">

				<form action="index.php" method="post">
				<fieldset>
				<input type="hidden" name="page" value="'. $params['page'] .'" />
				<input type="hidden" name="mode" value="'. $params['mode'] .'" />
        <input type="hidden" name="article_id" value="'. $params['article_id'] .'" />
        <input type="hidden" name="clang" value="'. $params['clang'] .'" />
        <input type="hidden" name="ctype" value="'. $params['ctype'] .'" />
	';

	$s = new rex_select();
	foreach($revisions as $k => $r)
		$s->addOption($r,$k);
	$s->setSelected($rex_version_article[$params['article_id']]);
  $s->setName('rex_set_version');
  $s->setId('rex-select-version-id');
  $s->setSize('1');
  $s->setAttribute('onchange', 'this.form.submit();');

  if($REX['USER']->hasPerm('version[only_working_version]'))
  {
    $s->setDisabled();
  }
   
  $return .= '<ul class="rex-display-inline">';
  $return .= '<li class="rex-navi-first"><label for="rex-select-version-id">'.$I18N->msg('version').':</label> '.$s->get().'</li>';

  if($REX['USER']->hasPerm('version[only_working_version]'))
	{
		if($rex_version_article[$params['article_id']]>0)
		{
      $return .= '<li><a href="'.$link.'&rex_version_func=copy_live_to_work">'.$I18N->msg('version_copy_from_liveversion').'</a></li>';
			$return .= '<li><a href="/'.rex_getUrl($params['article_id'],$params['clang'],array("rex_version"=>1)).'" target="_blank">'.$I18N->msg("version_preview").'</a></li>';
		}
	}else
	{
		if($rex_version_article[$params['article_id']]>0)
		{
			if(!$working_version_empty)
			  $return .= '<li><a href="'.$link.'&rex_version_func=copy_work_to_live">'.$I18N->msg('version_working_to_live').'</a></li>';
      $return .= '<li><a href="../'.rex_getUrl($params['article_id'],$params['clang'],array("rex_version"=>1)).'" target="_blank">'.$I18N->msg("version_preview").'</a></li>';
		}else
		{
			$return .= '<li><a href="'.$link.'&rex_version_func=copy_live_to_work" onclick="return confirm(\''.$I18N->msg('version_confirm_copy_live_to_workingversion').'\');">'.$I18N->msg('version_copy_live_to_workingversion').'</a></li>';
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