<?
/** @global CMain $APPLICATION */

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Проект");

$APPLICATION->IncludeComponent('tim:empty', 'project', array());

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");