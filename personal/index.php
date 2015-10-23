<?
use Local\Direct\Clients;

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Личный кабинет");

?>
<h3>Текущие клиенты</h3><?
$clients = Clients::getByCurrentUser();
foreach ($clients as $client)
{
	?>
	<p><?= $client["NAME"]?></p><?
}

?>
	<h3>Действия</h3>
	<p><a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=<?= DIRECT_API_CLIENT_ID
		?>">Подключить текущего пользователя Директа</a></p>
	<p><a href="update.php">Обновить данные</a></p>
	<p><a href="tmp.php">tmp</a></p><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>