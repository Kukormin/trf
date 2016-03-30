<?
use Local\Direct\Clients;

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Личный кабинет");

?>
<h3>Пользователи директа:</h3><?
$clients = Clients::getByCurrentUser();
foreach ($clients['ITEMS'] as $client)
{
	?>
	<h4><?= $client["NAME"] ?></h4><?
	$campaigns = Clients::checkCampaigns($client);
	?>
	<table class="table table-striped table-hover">
	<thead>
	<tr>
		<th>Название</th>
		<th>№</th>
		<th>Статус</th>
		<th></th>
	</tr>
	</thead>
	<tbody><?

	foreach ($campaigns['ITEMS'] as $campaign)
	{
		$status = '';
		if (!$campaign['ACTIVE'] != 'Y')
			$status = 'Не импортирована';
		else
		{
			if ($campaign['CHANGES'])
				$status = 'Есть изменения для синхронизации';
			else
				$status = 'Синхронизирована';
		}
		?>
		<tr>
		<td><?= $campaign['NAME'] ?></td>
		<td><?= $campaign['CampaignID'] ?></td>
		<td><?= $status ?></td>
		<td><a href="/personal/campaign/import.php?client=<?= $client['ID'] ?>&id=<?= $campaign['ID']
			?>">Импортировать</a></td>
		<td><a href="/ads/<?= $campaign['CampaignID'] ?>/">Синхронизировать</a></td>
		</tr><?
	}

	?>
	</tbody>
	</table><?

}

?>
	<h3>Действия</h3>
	<p><a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=<?= DIRECT_API_CLIENT_ID
		?>">Подключить текущего пользователя Директа</a></p>
	<p><a href="update.php">Обновить данные</a></p>
	<p><a href="tmp.php">tmp</a></p><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>