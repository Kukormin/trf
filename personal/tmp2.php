<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("tmp");

/*?>
<h3>Кампании для пользователей директа:</h3><?
$clients = Clients::getByCurrentUser();
foreach ($clients['ITEMS'] as $client)
{
	$campaigns = \Local\Direct\Import::checkCampaigns($client);
	$showSyncButton = false;

	?>
	<h4><?= $client["NAME"] ?></h4>
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
		if ($campaign['ACTIVE'] != 'Y')
			$status = 'Не импортирована';
		else
		{
			if ($campaign['CHANGES'])
			{
				$status = 'Есть изменения для синхронизации';
				$showSyncButton = true;
			}
			else
				$status = 'Синхронизирована';
		}
		?>
		<tr>
		<td><?= $campaign['NAME'] ?></td>
		<td><?= $campaign['CampaignID'] ?></td>
		<td><?= $status ?></td>
		<td><?
			if ($campaign['ACTIVE'] != 'Y')
			{
				?>
				<a href="/personal/campaign/import.php?client=<?= $client['ID'] ?>&id=<?= $campaign['ID']
				?>">Импортировать</a><?
			}
			?>
		</td>
		</tr><?
	}

	?>
	</tbody>
	</table><?

	if ($showSyncButton)
	{
		?>
		<p>
			<button class="btn btn-primary client_sync" type="button" data-loading-text="В процессе..."
			        data-client="<?= $client['ID'] ?>">Синхронизировать</button>
		</p>
		<div class="results"></div><?
	}

}

?>
	<h3>Действия</h3>
	<p><a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=<?= DIRECT_API_CLIENT_ID
		?>">Подключить текущего пользователя Директа</a></p>
	<p><a href="update.php">Обновить данные</a></p>
	<p><a href="tmp.php">tmp</a></p><?*/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");