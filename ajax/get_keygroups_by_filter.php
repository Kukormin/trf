<?
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$result = array();
	$project = \Local\Main\Project::getById($_REQUEST['pid']);
	$category = \Local\Main\Category::getById($_REQUEST['cid'], $_REQUEST['pid']);

	if ($category)
	{
		$ids = array();
		$filters = \Local\Main\Keygroup::getFilters();
		$view = \Local\Main\Keygroup::getViewSetting();
		$params = \Local\Main\Keygroup::getParamsForGetList($filters, $view);
		if ($_REQUEST['action'])
		{
			$actionParams = array(
				'filter' => $params['filter'],
			);
			if ($_REQUEST['ids'] != 'all')
			{
				$ids = explode(',', $_REQUEST['ids']);
				$actionParams['filter']['ID'] = $ids;
			}
			$keygroups = \Local\Main\Keygroup::getList($category['PROJECT'], $category['ID'], $actionParams);
			$clearCache = false;
			foreach ($keygroups['ITEMS'] as $item)
			{
				$updated = false;
				if ($_REQUEST['action'] == 'add_mark')
					$updated = \Local\Main\Keygroup::addMark($item, $_REQUEST['add_mark']);
				elseif ($_REQUEST['action'] == 'remove_mark')
					$updated = \Local\Main\Keygroup::removeMark($item, $_REQUEST['add_mark']);
				elseif ($_REQUEST['action'] == 'remove_all_mark')
					$updated = \Local\Main\Keygroup::removeAllMark($item);

				if ($updated)
					$clearCache = true;
			}

			if ($clearCache)
				\Local\Main\Keygroup::clearCache($_REQUEST['pid'], $_REQUEST['cid']);
		}
		$keygroups = \Local\Main\Keygroup::getList($category['PROJECT'], $category['ID'], $params);

		$type = $filters['type']['VALUE'];
		$textView = $view['style']['VALUE'] == 't';
		$max = intval($view['cnt']['VALUE']);

		?>
		<table class="table table-striped table-hover" data-all="<?= $keygroups['NAV']['ITEMS_COUNT'] ?>">
			<thead>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th>Ключевая фраза</th>
				<th></th>
				<th>W</th>
				<th>Метки</th>
				<th colspan="2">Заголовок</th>
				<th>Текст</th>
				<th></th>
				<th></th>
			</tr>
			</thead>
			<tbody><?

			foreach ($keygroups['ITEMS'] as $item)
			{
				$marks = '';
				foreach ($item['MARKS'] as $markId)
				{
					$mark = \Local\Main\Mark::getById($markId);
					if ($mark)
						$marks .= ' '. $mark['HTML'];
				}
				$href = \Local\Main\Keygroup::getHref($category, $item);
				$checked = '';
				if ($_REQUEST['ids'] == 'all' || in_array($item['ID'], $ids))
					$checked = ' checked';

				$title = '';
				$text = '';
				$firstAd = false;

				$ads = \Local\Main\Ad::getByKeygroup($item['ID']);
				$fads = array();
				$cnt = 0;
				foreach ($ads as $ad)
				{
					if ($type['y'] && $ad['YANDEX'] || $type['g'] && !$ad['YANDEX'])
						if ($type['s'] && $ad['SEARCH'] || $type['n'] && !$ad['SEARCH'])
						{
							$fads[] = $ad;
							$cnt++;
							if ($max && $cnt >= $max)
								break;
						}
				}

				$rspan = count($fads);
				$rs = $textView && $rspan > 1 ? ' rowspan="' . $rspan . '"' : '';

				if ($textView)
				{
					if (!$rspan)
						$fads[] = array();

					foreach ($fads as $i => $ad)
					{
						?>
						<tr><?
						if (!$i)
						{
							?>
							<td<?= $rs ?>><input class="select_item" type="checkbox"
							                     id="<?= $item['ID'] ?>"<?= $checked ?> /></td>
							<td<?= $rs ?>></td>
							<td<?= $rs ?>></td>
							<td<?= $rs ?>><a href="<?= $href ?>"><?= $item['NAME'] ?></a></td>
							<td<?= $rs ?>><?= strlen($item['NAME']) ?></td>
							<td<?= $rs ?>><?= $item['WORDSTAT'] ?></td>
							<td<?= $rs ?>><?= $marks ?></td><?
						}
						?>
						<td><?= $ad['TITLE'] ?></td>
						<td><?= $ad['TITLE_2'] ?></td>
						<td><?= $ad['TEXT'] ?></td><?
						if (!$i)
						{
							?>
							<td<?= $rs ?>></td>
							<td<?= $rs ?>></td><?
						}
						?>
						</tr><?
					}
				}
				else
				{
					?>
					<tr>
						<td<?= $rs ?>><input class="select_item" type="checkbox" id="<?= $item['ID'] ?>"<?= $checked ?> />
						</td>
						<td<?= $rs ?>></td>
						<td<?= $rs ?>></td>
						<td<?= $rs ?>><a href="<?= $href ?>"><?= $item['NAME'] ?></a></td>
						<td<?= $rs ?>><?= strlen($item['NAME']) ?></td>
						<td<?= $rs ?>><?= $item['WORDSTAT'] ?></td>
						<td<?= $rs ?>><?= $marks ?></td>
						<td colspan="3"><?

							foreach ($fads as $ad)
							{
								$ad['HOST'] = $project['URL'];
								$ad['SCHEME'] = $category['DATA']['SCHEME'];
								\Local\Main\Ad::printExample($ad);
							}

							?>
						</td>
						<td<?= $rs ?>></td>
						<td<?= $rs ?>></td>
					</tr><?
				}
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