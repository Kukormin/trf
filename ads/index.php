<?
/** @global CMain $APPLICATION */

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Объявления");

?>
<table class="table table-striped table-hover">
	<thead>
	<tr>
		<th>Название</th>
		<th>Статус</th>
		<th>Ставка на поиске</th>
		<th>Ставка на тематических площадках РСЯ</th>
		<th></th>
	</tr>
	</thead>
	<tbody><?

		$groups = \Local\Direct\AdGroups::getByCampaign($_REQUEST['id']);
		foreach ($groups['ITEMS'] as $group)
		{
			$words = \Local\Direct\Keywords::getByGroup($group['Id']);
			$ads = \Local\Direct\Ads::getByGroup($group['Id']);

			$first = true;
			foreach ($words['ITEMS'] as $word)
			{
				?>
				<tr>
					<td><?= $word['NAME'] ?></td>
					<td><?= $word['StatusName'] ?></td>
					<td><?= $word['BidFormatted'] ?></td>
					<td><?= $word['ContextBidFormatted'] ?></td>
					<td><?
						if ($first){
							?>
							<a href="/ads/<?= $group['Id'] ?>/">Объявления</a>
							<?
						}
						?>
					</td>
				</tr><?

				$first = false;
			}

		}

	?>
	</tbody>
</table><?


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>