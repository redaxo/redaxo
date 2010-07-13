<?php

// gettweets

class rex_com_twitter{

	var $accounts = array();

	function getAllTweets()
	{
		$g = new rex_sql();
		// $g->debugsql = 1;
		$g->setQuery('select * from rex_com_twitter_account where status=1');
		$accounts = $g->getArray();

		foreach($accounts as $account)
		{
			$r = rex_com_twitter::getAccountTweets($account["name"],$account["password"],$account["since_id"]);
			// letzten Eintrag in Account speichern.
			$g->setTable('rex_com_twitter_account');
			$g->setWhere('id="'.$account["id"].'"');
			$g->setValue('since_id',$r["since_id"]);
			$g->setValue('last_update',time());
			$g->update();

		}

	}

	function getAccountTweets($login,$psw,$since_id = 0)
	{
		$fields = array("created_at","text","source","truncated","favorited","in_reply_to_status_id","in_reply_to_user_id");

		$return = array();
		$return["since_id"] = $since_id;

		// Twitter login
		$t = new Twitter($login,$psw);

		// $tweets = $a->getUserTimeline($id = null, $since = null, $sinceId = null, $count = null, $page = null)
		// $tweets = $a->getFriendsTimeline();

		if($return["since_id"] == 0)
		{
			$return["since_id"] = NULL;
		}
		
		$tweets = $t->getUserTimeline($login,$return["since_id"],NULL,10); // 50 Eintraege immer auf Einmal ziehen.

		// echo '<pre>';var_dump($tweets);echo '</pre>';

		$g = new rex_sql();
		$g->debugsql = 1;

		foreach($tweets as $tweet)
		{
			// $t = date("d.m.Y H:i",$tweet["created_at"])."h";
			// $f = $tweet["user"];
			// echo '<pre>';var_dump($f);echo '</pre>';
			// echo '<pre>';var_dump($tweet);echo '</pre>';
			// $te = $tweet["text"];

			$tweet["id"] = addslashes($tweet["id"]);

			if($return["since_id"]<$tweet["id"])
			{
				$return["since_id"] = $tweet["id"];
			}

			// **** prŸfen ob eintrag vorhanden ist
			$g->setQuery('select twitter_id as id from rex_com_twitter_entry where twitter_id='.$tweet["id"].' LIMIT 1');
			if($g->getRows()==0)
			{
				$g->setTable('rex_com_twitter_entry');
				$g->setValue('added_at',time());
				// $g->setValue('location_lng','');
				// $g->setValue('location_lat','');
				$g->setValue('twitter_id',$tweet['id']);
				$g->setValue('user_id',$tweet['user']['id']);
				$g->setValue('account',$tweet['user']['name']);
				foreach($fields as $f)
				{
					$g->setValue($f,addslashes($tweet[$f]));
				}
				$g->insert();
			}

		}

		return $return;

	}




	function setTwitterchars($content)
	{

		$content = htmlspecialchars($content);
		$content = str_replace("&lt;br /&gt;","<br />", $content);
		$content = str_replace("\r","", $content);
		$suchmuster = '/((\<br \/\>\\n){3,})/i';
		$ersetzung = '<br /><br />';
		$content = preg_replace($suchmuster, $ersetzung, $content);

		$content = eregi_replace(
    'http:\/\/([:/a-z0-9\-\.\,\_\?=\%\#\@\&\+\;\:\-]+)', 
    '<a href="http://\\1" target="_blank">http://\\1</a>', 
		$content);

		$content = eregi_replace(
    '\@([:/a-z0-9\-\.\,\_\?=\%\#\@\&\+\;\:\-]+)', 
    '@<a href="http://www.twitter.com/\\1" target="_blank">\\1</a>', 
		$content);

		$content = breakLongWords($content, 25, " ");

		return $content;
	}


}






?>