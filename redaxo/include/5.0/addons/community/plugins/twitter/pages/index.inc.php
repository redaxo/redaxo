<?php

$page = 'community';
$subpage = 'plugin.twitter';
$tripage = rex_request("tripage","string");
$func = rex_request("func","string","");

$tripages = array(
    array('name'=>'entry',    'href'=>'index.php?page=community&subpage=plugin.twitter&tripage=entry',  'label'=>'Twittereintr&auml;ge'),
    array('name'=>'account',  'href'=>'index.php?page=community&subpage=plugin.twitter&tripage=account','label'=>'Twitteraccount'),
    array('name'=>'entry',    'href'=>'index.php?page=community&subpage=plugin.twitter&func=get_tweets','label'=>'Hole Tweets')
    );

$file = '';
    
switch($tripage)
{
	case("account"):
		$table = 'rex_com_twitter_account';
    $bezeichner = "Twitteraccount";
    // Dont show this fields in list
    $gufa = array("tweet");
		break;
	default:
		$tripage = 'entry';
    $table = 'rex_com_twitter_entry';
    $bezeichner = "Twittereintrag";
    // Dont show this fields in list
    $gufa = array("text","source","truncated","added_at","location_lng","location_lat","favorited","in_reply_to_status_id","in_reply_to_user_id");
		break;
}

// trinavi
echo '<ul>';
foreach($tripages as $t)
{
	if($tripage == $t['name'])
   echo '<li class="active"><a class="active" href="'.$t["href"].'">'.$t["label"].'</a></li>';
  else
   echo '<li><a href="'.$t["href"].'">'.$t["label"].'</a></li>';
}
echo '</ul>';




if($func == "get_tweets")
{
	rex_com_twitter::getAllTweets();
	echo rex_info('Tweets wurden aktualisiert');
	$func = "";
}




include $REX["INCLUDE_PATH"].'/addons/community/plugins/twitter/helper/xformhelper.inc.php';

?>