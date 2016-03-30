<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if(!defined('ERROR_404')) {
	define('ERROR_404', 'Y');
	CHTTP::SetStatus('404 Not Found');
}

$APPLICATION->IncludeFile(
	"/inc/404.php", 
	array(), 
	array(
		"MODE" => "html"
	)
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
