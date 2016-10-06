<?
$url = $_REQUEST['url'];
$return = \Local\Parser::site($url);

if ($return === false)
	$return = array('error' => 'load_error');
