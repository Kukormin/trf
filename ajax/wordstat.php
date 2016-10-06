<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


	$cookieFile = $_SERVER['DOCUMENT_ROOT'] . '/_log/wsc.txt';
	$userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:48.0) Gecko/20100101 Firefox/48.0';

	$word = $_REQUEST['word'];

	$cookie = file_get_contents($cookieFile);
	$cr = (strpos($cookie, "\r\n") !== false)? "\r\n" : "\n";
	$a_rows = explode($cr, trim($cookie, $cr));
	$fuid = '';
	foreach ($a_rows as $i=> $row) {
		if (strpos($row, "\tfuid01\t") === false) continue;
		$a_cols = explode("\t", $row);
		$fuid = $a_cols[6];
	}

	if (!$fuid)
	{
		$url = 'https://wordstat.yandex.ru/';
		$header = array(
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
			'Cache-Control: max-age=0',
			'Connection: keep-alive',
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		$filename = $_SERVER['DOCUMENT_ROOT'] . '/_log/ws.html';
		file_put_contents($filename, $result);


		if (strpos($result, 'mode=logout') === false)
		{
			$url = 'https://passport.yandex.ru/passport?mode=auth&from=&retpath=&twoweeks=yes';
			$header = array(
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
				'Cache-Control: max-age=0',
				'Connection: keep-alive',
			);
			$ts = round(getmicrotime() * 1000);
			$post = 'login=www-ros-dengi-ru-2&passwd=12www-ros-dengi-ru-2&timestamp=' . $ts;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
			$result = curl_exec($ch);
			curl_close($ch);

			$filename = $_SERVER['DOCUMENT_ROOT'] . '/_log/login.html';
			file_put_contents($filename, $result);

			$url = 'https://kiks.yandex.ru/su/';
			$header = array(
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
				'Cache-Control: max-age=0',
				'Connection: keep-alive',
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);

			$filename = $_SERVER['DOCUMENT_ROOT'] . '/_log/kiks.html';
			file_put_contents($filename, $result);
		}

		$ok = strpos($result, 'mode=logout') !== false;
	}
	else
		$ok = true;

	$ar = array();
	if ($ok)
	{
		$filename = $_SERVER['DOCUMENT_ROOT'] . '/_log/ws/' . md5($word) . '.html';
		$cache = false;
		if (file_exists($filename))
		{
			$s = file_get_contents($filename);
			$cache = true;
		}
		else
		{
			$url = 'https://wordstat.yandex.ru/stat/words';
			$header = array(
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
				'Cache-Control: max-age=0',
				'Connection: keep-alive',
				'X-Requested-With: XMLHttpRequest',
			);
			$post = 'db=&filter=all&map=world&page=1&page_type=words&period=monthly&regions=&sort=cnt&type=list&words=' . $word;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_REFERER, 'https://wordstat.yandex.ru');
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
			$s = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);

			file_put_contents($filename, $s);
		}

		$ar = json_decode($s, true);
		$ar['fuid'] = $fuid;
		$ar['ua'] = substr($userAgent, 0, 25);
		$ar['cache'] = $cache;
	}

	header('Content-Type: application/json');
	echo json_encode($ar, JSON_UNESCAPED_UNICODE);



	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}