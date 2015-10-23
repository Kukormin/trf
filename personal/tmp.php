<?
use Local\Direct\Clients;

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("tmp");

Clients::tmp();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");