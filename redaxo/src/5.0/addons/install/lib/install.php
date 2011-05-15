<?php


class rex_install {






	function getCurrentREDAXOVersion()
	{
		$v = rex_core::getProperty('version').rex_core::getProperty('subversion');

		// TODO:
		return "4.3";

	}




	function getAddOns()
	{
		$addons = array();
		$c = rex_install::request('GET', 'www.redaxo.org', 80, '/de/_system/_webservice/addons/', array('v' => rex_install::getCurrentREDAXOVersion()));
		if($c !== FALSE)
		{
			$json_c = json_decode($c,TRUE);
			foreach($json_c as $a)
			{
				$addons[$a["addon_key"]] = $a;
			}
			ksort($addons);
		}
		return $addons;
	}

	function getModules()
	{
		$addons = array();
		$c = rex_install::request('GET', 'www.redaxo.org', 80, '/de/_system/_webservice/modules/', array('v' => rex_install::getCurrentREDAXOVersion()));
		if($c !== FALSE)
		{
			$json_c = json_decode($c,TRUE);
			foreach($json_c as $a)
			{
				$addons[$a["addon_key"]] = $a;
			}
			ksort($addons);
		}
		return $addons;
	}

	function getTemplates()
	{
		$addons = array();
		$c = rex_install::request('GET', 'www.redaxo.org', 80, '/de/_system/_webservice/templates/', array('v' => rex_install::getCurrentREDAXOVersion()));
		if($c !== FALSE)
		{
			$json_c = json_decode($c,TRUE);
			foreach($json_c as $a)
			{
				$addons[$a["addon_key"]] = $a;
			}
			ksort($addons);
		}
		return $addons;
	}

	function getPlugIns()
	{
		$addons = array();
		$c = rex_install::request('GET', 'www.redaxo.org', 80, '/de/_system/_webservice/plugins/', array('v' => rex_install::getCurrentREDAXOVersion()));
		if($c !== FALSE)
		{
			$json_c = json_decode($c,TRUE);
			foreach($json_c as $a)
			{
				$addons[$a["addon_key"]] = $a;
			}
			ksort($addons);
		}
		return $addons;
	}















	function request(
	$verb = 'GET',             /* HTTP Request Method (GET and POST supported) */
	$ip,                       /* Target IP/Hostname */
	$port = 80,                /* Target TCP port */
	$uri = '/',                /* Target URI */
	$getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */
	$postdata = array(),       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */
	$cookie = array(),         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */
	$custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */
	$timeout = 1000,           /* Socket timeout in milliseconds */
	$req_hdr = false,          /* Include HTTP request headers */
	$res_hdr = false           /* Include HTTP response headers */
	)
	{
		$ret = '';
		$verb = strtoupper($verb);
		$cookie_str = '';
		$getdata_str = count($getdata) ? '?' : '';
		$postdata_str = '';

		foreach ($getdata as $k => $v) {
			$getdata_str .= urlencode($k) .'='. urlencode($v);
		}

		foreach ($postdata as $k => $v) {
			$postdata_str .= urlencode($k) .'='. urlencode($v) .'&';
		}

		foreach ($cookie as $k => $v) {
			$cookie_str .= urlencode($k) .'='. urlencode($v) .'; ';
		}

		$crlf = "\r\n";
		$req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf;
		$req .= 'Host: '. $ip . $crlf;
		$req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf;
		$req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf;
		$req .= 'Accept-Language: en-us,en;q=0.5' . $crlf;
		$req .= 'Accept-Encoding: deflate' . $crlf;
		$req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf;

		foreach ($custom_headers as $k => $v) {
			$req .= $k .': '. $v . $crlf;
		}

		if (!empty($cookie_str)) {
			$req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf;
		}

		if ($verb == 'POST' && !empty($postdata_str))
		{
			$postdata_str = substr($postdata_str, 0, -1);
			$req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf;
			$req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf;
			$req .= $postdata_str;
		}else
		{
			$req .= $crlf;
		}

		if ($req_hdr){
			$ret .= $req;
		}

		if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false)
		{
			return FALSE;
			return "Error $errno: $errstr\n";
		}

		stream_set_timeout($fp, 0, $timeout * 1000);

		fputs($fp, $req);
		while ($line = fgets($fp)){
			$ret .= $line;
		}
		fclose($fp);

		if (!$res_hdr){
			$ret = substr($ret, strpos($ret, "\r\n\r\n") + 4);
		}

		return $ret;
	}



}



?>