<?
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$result = array();
	$project = \Local\Project::getById($_REQUEST['pid']);
	$category = \Local\Category::getById($_REQUEST['cid'], $_REQUEST['pid']);

	if ($category)
	{
		$params = \Local\Keygroup::getParamsForGetList();
		$keygroups = \Local\Keygroup::getList($category['PROJECT'], $category['ID'], $params);
		?>
		<table class="table table-striped table-hover">
			<thead>
			<tr>
				<th>№</th>
				<th></th>
				<th>Ключевая фраза</th>
				<th></th>
				<th>Частота</th>
				<th>Заголовок</th>
				<th></th>
				<th>Описание</th>
				<th></th>
				<th>URL</th>
				<th>Быстрые ссылки</th>
			</tr>
			</thead>
			<tbody><?


			foreach ($keygroups['ITEMS'] as $item)
			{
				?>
				<tr>
				<td><?= $item['ID'] ?></td>
				<td><?= $item['BASE'] ?></td>
				<td><?= $item['NAME'] ?></td>
				<td><?= strlen($item['NAME']) ?></td>
				<td><?= $item['WORDSTAT'] ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				</tr><?
			}

			?>
			</tbody>
		</table>
		<?

		$iCur = $keygroups['NAV']['CURRENT_PAGE'];
		$iEnd = $keygroups['NAV']['PAGE_COUNT'];
		if ($iEnd > 1) {
			$iStart = $iCur - 3;
			$iFinish = $iCur + 3;
			if ($iStart < 1) {
				$iFinish -= $iStart - 1;
				$iStart = 1;
			}
			if ($iFinish > $iEnd) {
				$iStart -= $iFinish - $iEnd;
				if ($iStart < 1) {
					$iStart = 1;
				}
				$iFinish = $iEnd;
			}

			$iStart1 = $iCur - 2;
			$iFinish1 = $iCur + 2;
			if ($iStart1 < 1) {
				$iFinish1 -= $iStart1 - 1;
				$iStart1 = 1;
			}
			if ($iFinish1 > $iEnd) {
				$iStart1 -= $iFinish1 - $iEnd;
				if ($iStart1 < 1) {
					$iStart1 = 1;
				}
				$iFinish1 = $iEnd;
			}
			?>
			<div class="pagination">
				<ul><?

					if ($iCur > 1) {
						?>
						<li><a href="#" data-page="<?= ($iCur-1) ?>">←</a></li><?
					}
					else {
						?>
						<li class="disabled"><a href="#">←</a></li><?
					}
					if ($iStart > 1) {
						if (1 == $iCur) {
							?>
							<li class="active"><a href="#">1</a></li><?
						}
						else {
							$sHref = $GLOBALS['APPLICATION']->GetCurPageParam("page=1", array("page"));
							?>
							<li><a href="#" data-page="1">1</a></li><?
						}
						if ($iStart > 2) {
							?>
							<li class="disabled"><a href="#">...</a></li><?
						}
					}
					for ($i = $iStart; $i <= $iFinish; $i++) {
						if ($i == $iCur) {
							?>
							<li class="active"><a href="#"><?= $i ?></a></li><?
						}
						else {
							?>
							<li><a href="#" data-page="<?= $i ?>"><?= $i ?></a></li><?
						}
					}
					if ($iFinish < $iEnd) {
						if ($iFinish < $iEnd - 1) {
							?>
							<li class="disabled"><a href="#">...</a></li><?
						}
						if ($iEnd == $iCur) {
							?>
							<li class="active"><a href="#"><?= $iEnd ?></a></li><?
						}
						else {
							?>
							<li><a href="#" data-page="<?= $iEnd ?>"><?= $iEnd ?></a></li><?
						}
					}
					if ($iCur < $iEnd) {
						?>
						<li><a href="#" data-page="<?= ($iCur+1) ?>">→</a></li><?
					}
					else {
						?>
						<li class="disabled"><a href="#">→</a></li><?
					}

					?>
				</ul>
			</div><?

		}

	}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}