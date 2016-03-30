<?
/** @global CMain $APPLICATION */

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои кампании");

$clients = \Local\Direct\Clients::getByCurrentUser();
foreach ($clients as $client)
{
	?>
	<h3><?= $client["NAME"] ?></h3><?

	$campaigns = \Local\Direct\Campaigns::getByClient($client["NAME"]);

	?>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Название</th>
				<th>№</th>
				<th>Активность</th>
				<th>Начало</th>
				<th>Кликов</th>
				<th></th>
			</tr>
		</thead>
		<tbody><?

			foreach ($campaigns['ITEMS'] as $campaign)
			{
				?>
				<tr>
					<td><?= $campaign['NAME'] ?></td>
					<td><?= $campaign['CampaignID'] ?></td>
					<td><?= $campaign['IsActive'] ?></td>
					<td><?= $campaign['StartDate'] ?></td>
					<td><?= $campaign['Clicks'] ?></td>
					<td><a href="/ads/<?= $campaign['CampaignID'] ?>/">Объявления</a></td>
				</tr><?
			}

		?>
		</tbody>
	</table><?

}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>