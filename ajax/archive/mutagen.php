<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$word = trim($_REQUEST['word']);

	include ($_SERVER["DOCUMENT_ROOT"] . '/local/lib/mutagen/mutagen.php');
	$ljClient = new IXR_Client('mutagen.ru', '/?xmlrpc');
	$mutagen = new mutagen($ljClient);

	// альтернативный вариант с паролем в md5
	$ljClient->query('mutagen.login',"lenchik37", md5("bd19m84g"), "true"); //
	$ljResponse = $ljClient->getResponse();
	debugmessage($ljResponse);

	$ljClient->query('mutagen.balance');
	$ljResponse = $ljClient->getResponse();
	debugmessage($ljResponse);
/*
	// конкуренция
	/*$data = $mutagen->key_create_task($word);
	debugmessage($data);
	$data = $mutagen->key_get_task($data["task_id"]);
	debugmessage($data);

*/
	// левая колонка вордстата
	$data = $mutagen->suggest_create_task($word);
	$data = $mutagen->suggest_get_task($data["task_id"]);
	debugmessage($data);


	//wordstat_key - левая колонка вордстат, 40 страниц, 2000 ключей
	//wordstat_key_50 - левая колонка вордстат, первая страница, 50 ключей.
	//wordstat_n - частотность вордстат
	//wordstat_q - частотность "вордстат".
	//wordstat_qs - частотность !"вордстат".
	//direct - биды директ

	/*$ljClient->query('mutagen.parser.get', $word, "direct", "213");
	$ljResponse = $ljClient->getResponse();
	debugmessage($ljResponse);
	/*

	/*$ljClient->query('mutagen.parser.get', "mp3", "direct");
	$ljResponse = $ljClient->getResponse();
	print "-один запрос к парсеру direct без указания региона";
	print_r($ljResponse);
	print "<br>";*/

	/*$ljClient->query('mutagen.parser.mass.new', array(
		"прокат автомобилей",
		"аренда автомобилей",
		"прокат авто",
		"аренда авто",
	), "wordstat_n", 2, "проверка из api");
	$ljResponse = $ljClient->getResponse();
	debugmessage($ljResponse);*/


	/*$ljClient->query('mutagen.parser.mass.list');
	$ljResponse = $ljClient->getResponse();
	debugmessage($ljResponse);

	$last_mass_id = key($ljResponse);

	$ljClient->query('mutagen.parser.mass.id', $last_mass_id);
	$ljResponse = $ljClient->getResponse();
	debugmessage($ljResponse);*/



	$ljClient->query('mutagen.balance');
	$ljResponse = $ljClient->getResponse();
	debugmessage($ljResponse);


	/*$url = 'https://wordstat.yandex.ru/stat/words';
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
	}*/

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}