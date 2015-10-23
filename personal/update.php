<?
use Local\Direct\Clients;

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Обновление данных");

Clients::updateAll();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");