<?
/** @global CMain $APPLICATION */

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новый проект");

$APPLICATION->IncludeComponent('tim:empty', 'new_project', array());

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");