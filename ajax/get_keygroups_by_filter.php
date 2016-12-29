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
		$pageSizes = \Local\Main\Keygroup::getPageSizes();
		$view = \Local\Main\View::getCurrentView();
		$filters = \Local\Main\Keygroup::getFilters();
		$filters['PAGE_SIZE'] = $pageSizes['SIZE'];
		$params = \Local\Main\Keygroup::getParamsForGetList($filters);
		if ($_REQUEST['action'])
		{
			$actionParams = array(
				'filter' => $params['filter'],
			);
			$ids = explode(',', $_REQUEST['ids']);
			if ($_REQUEST['select_all'])
			{
				if ($ids)
					$actionParams['filter']['!ID'] = $ids;
			}
			else
			{
				if ($ids)
					$actionParams['filter']['ID'] = $ids;
			}
			$templ = array();
			if ($_REQUEST['action'] == 'add_templ')
				$templ = \Local\Main\Templ::getById($_REQUEST['add_templ'], $category['ID']);

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
				if ($_REQUEST['action'] == 'add_templ')
					\Local\Main\Ad::addByTemplate($item, $templ, $category);

				if ($updated)
					$clearCache = true;
			}

			if ($clearCache)
				\Local\Main\Keygroup::clearCache($_REQUEST['pid'], $_REQUEST['cid']);
		}
		$keygroups = \Local\Main\Keygroup::getList($category['PROJECT'], $category['ID'], $params);

		$max = intval($view['DATA']['AD_COUNT']);

		?>
		<table class="tbl" data-all="<?= $keygroups['NAV']['ITEMS_COUNT'] ?>">
			<thead>
			<tr><?

				foreach ($view['DATA']['COLUMNS'] as $col)
				{
					if ($col == 'ad')
					{
						foreach ($view['DATA']['AD_COLUMNS'] as $adCol)
						{
							$column = \Local\Main\View::getAdColumnByCode($adCol);
							$title = $column['TITLE'] ? ' title="' . $column['TITLE'] . '"' : '';
							?>
							<th class="col-<?= $adCol ?>"<?= $title ?>><?= $column['NAME'] ?></th><?
						}
					}
					else
					{
						$column = \Local\Main\View::getColumnByCode($col);
						$title = $column['TITLE'] ? ' title="' . $column['TITLE'] . '"' : '';
						?>
						<th class="col-<?= $col ?>"<?= $title ?>><?= $column['NAME'] ?></th><?
					}
				}

				?>
			</tr>
			</thead>
			<tbody><?

			foreach ($keygroups['ITEMS'] as $item)
			{
				$fads = array();
				if ($view['SHOW_AD'])
				{
					$ads = \Local\Main\Ad::getByKeygroup($item['ID']);
					$cnt = 0;
					foreach ($ads as $ad)
					{
						if ($filters['PLATFORM'][$ad['PLATFORM']]['VALUE'])
						{
							$ad['HOST'] = $project['URL'];
							$ad['SCHEME'] = $category['DATA']['SCHEME'];
							$fads[] = $ad;
							$cnt++;
							if ($max && $cnt >= $max)
								break;
						}
					}
				}
				$adCount = count($fads);
				if (!$fads)
					$fads[] = array();

				foreach ($fads as $i => $ad)
				{
					\Local\Main\Ad::printRow($ad, $item, $category, $i == 0, $adCount, $view,
						$filters['PLATFORM'], $ids);
				}

			}

			?>
			</tbody>
			<tbody class="hidden"><?

				// Строка-шаблон для добавления объявлений
				\Local\Main\Ad::printRow(array(), array(), array(), false, 1, $view,
					$filters['PLATFORM']);

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

				Элементов на странице:
				<select name="size" id="size"><?
					foreach ($pageSizes['ITEMS'] as $k => $v)
					{
						$checked = $v['SELECTED'] ? ' selected' : '';
						?><option value="<?= $k ?>"<?= $checked ?>><?= $v['NAME'] ?></option><?
					}
					?>
				</select>
			</div><?

		}

	}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}