<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$assetInstance = \Bitrix\Main\Page\Asset::getInstance();
$assetInstance->addJs(SITE_TEMPLATE_PATH . '/js/import.js');
$APPLICATION->SetTitle("Импорт кампании");

$client = \Local\Direct\Clients::getById($_REQUEST['client']);
if (!$client)
{
	?><p>Неверный Id клиента</p><?
}

$campaign = \Local\Direct\Campaigns::getById($client['NAME'], $_REQUEST['id']);
if (!$campaign)
{
	?><p>Неверный Id кампании</p><?
}

?>
<h3>Импорт кампании "<?= $campaign['NAME'] ?>":</h3>
<button class="btn btn-primary" type="button" id="import_start"
        data-client="<?= $client['ID'] ?>" data-id="<?= $campaign['ID'] ?>">Начать импорт</button><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>