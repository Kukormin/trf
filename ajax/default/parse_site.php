<?
$url = $_REQUEST['url'];
$return = \Local\Utils\Parser::site($url);

if ($return === false)
	$return = array('error' => 'load_error');
