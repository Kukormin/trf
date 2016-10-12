<?
$url = $_REQUEST['url'];
$return = \Local\Main\Parser::site($url);

if ($return === false)
	$return = array('error' => 'load_error');
