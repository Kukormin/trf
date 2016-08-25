<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$word = trim($_REQUEST['word']);
	$url = 'https://wordstat.yandex.ru/stat/words';
	$post = 'db=&filter=all&map=world&page=1&page_type=words&period=monthly&regions=&sort=cnt&type=list&words=' . $word;

	$filename = $_SERVER['DOCUMENT_ROOT'] . '/_log/urls/' . md5($post) . '.html';
	if (file_exists($filename))
	{
		$result = file_get_contents($filename);
	}
	else
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_TRANSFER_ENCODING, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/_log/wsc.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE,  $_SERVER['DOCUMENT_ROOT'] . '/_log/wsc.txt');
		$result = curl_exec($ch);
		curl_close($ch);

		file_put_contents($filename, $result);
	}

	$res = json_decode($result, true);
	if ($res['need_login'] == 1)
	{
		$ts = round(getmicrotime() * 1000);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://passport.yandex.ru/passport?mode=auth&from=&retpath=&twoweeks=yes');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_TRANSFER_ENCODING, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'login=www-ros-dengi-ru-2&passwd=12www-ros-dengi-ru-2&timestamp=' . $ts);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/_log/wsc.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE,  $_SERVER['DOCUMENT_ROOT'] . '/_log/wsc.txt');
		$result = curl_exec($ch);
		curl_close($ch);

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/_log/urls/_A-' . md5($post) . '.html', $result);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_TRANSFER_ENCODING, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/_log/wsc.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE,  $_SERVER['DOCUMENT_ROOT'] . '/_log/wsc.txt');
		$result = curl_exec($ch);
		curl_close($ch);

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/_log/urls/_P-' . md5($post) . '.html', $result);
	}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}