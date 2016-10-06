<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Главная");

if ($USER->IsAuthorized())
	$APPLICATION->IncludeComponent('tim:empty', 'projects', array());
else
{
	?>
	<div><img src="/i/main.jpg"></div>
	<div>Описание сайта.</div><?
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>