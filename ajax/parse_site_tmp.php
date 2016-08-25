<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$result = \Local\Parser::parse1($_REQUEST['url']);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	$APPLICATION->RestartBuffer();
	header('Content-Type: application/json');
	echo json_encode($result);
}