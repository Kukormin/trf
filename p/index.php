<?
/** @global CMain $APPLICATION */

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проекты");

$APPLICATION->IncludeComponent('tim:empty', 'projects', array());

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");