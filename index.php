<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Главная");

if ($USER->IsAuthorized())
	$APPLICATION->IncludeComponent('tim:nav', '', array());
else
{
	?>
	<div><img src="/i/main.jpg"></div>
	<div>Эту страницу видят неавторизованные</div><?
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>