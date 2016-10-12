<?
$return = array();

$url = $_REQUEST['url'];
$res = \Local\Main\Parser::getWithInfo($url);

if ($res['HTML'] === false)
	$return['error'] = 'Ошибка загрузки сайта';
elseif ($res['http_code'] != 200)
	$return['error'] = 'Ошибка загрузки сайта - ' . $res['http_code'];
