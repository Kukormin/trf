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

		$y = $filters['YGSN']['y']['VALUE'];
		$g = $filters['YGSN']['g']['VALUE'];
		$s = $filters['YGSN']['s']['VALUE'];
		$n = $filters['YGSN']['n']['VALUE'];
		$textView = $filters['VIEW']['style']['VALUE'] == 't';
		$max = intval($view['DATA']['AD_COUNT']);

		?>
		<table class="tbl" data-all="<?= $keygroups['NAV']['ITEMS_COUNT'] ?>">
			<thead>
			<tr><?

				$adPrev = false;
				foreach ($view['DATA']['COLUMNS'] as $col)
				{
					if ($col == 'title' || $col == 'text' || $col == 'preview')
					{
						if (!$adPrev)
						{
							?>
							<th></th><?
							$adPrev = true;
						}
					}
					$column = \Local\Main\View::getColumnByCode($col);
					$title = $column['TITLE'] ? ' title="' . $column['TITLE'] . '"' : '';
					?>
					<th<?= $title ?>><?= $column['NAME'] ?></th><?
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
						if ($y && $ad['YANDEX'] || $g && !$ad['YANDEX'])
							if ($s && $ad['SEARCH'] || $n && !$ad['SEARCH'])
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
				$rs = count($fads) > 1 ? ' rowspan="' . count($fads) . '"' : '';

				?>
				<tr><?

					$adPrev = false;
					foreach ($view['DATA']['COLUMNS'] as $col)
					{
						if ($col == 'title' || $col == 'text' || $col == 'preview')
						{
							if (!$adPrev)
							{
								?>
								<td><?

									foreach ($fads as $ad)
									{
										?>
										<a class="btn edit" target="_blank"
										   href="<?= \Local\Main\Ad::getHref($category, $item, $ad) ?>"
										   title="Редактировать объявление"><i></i></a><?
										break;
									}
									?>
								</td><?
								$adPrev = true;
							}
						}

						$class = '';
						if ($col == 'preview')
							$class = ' class="preview"';
						$rowspan = $rs;
						if ($col == 'title' || $col == 'text' || $col == 'preview')
							$rowspan = '';

						?><td<?= $class ?><?= $rowspan ?>><?

						if ($col == 'cb')
						{
							$checked = '';
							$inIds = in_array($item['ID'], $ids);
							if ($_REQUEST['select_all'] && !$inIds || !$_REQUEST['select_all'] && $inIds)
								$checked = ' checked';
							?><input class="select_item" type="checkbox" id="<?= $item['ID'] ?>"<?= $checked ?> /><?
						}
						elseif ($col == 'name')
						{
							$href = \Local\Main\Keygroup::getHref($category, $item);
							?><a href="<?= $href ?>"><?= $item['NAME'] ?></a><?
						}
						elseif ($col == 'ws')
						{
							?><?= $item['WORDSTAT'] ?><?
						}
						elseif ($col == 'mark')
						{
							foreach ($item['MARKS'] as $markId)
							{
								$mark = \Local\Main\Mark::getById($markId);
								echo ' ';
								echo $mark['HTML'];
							}
						}
						elseif ($col == 'title')
						{
							foreach ($fads as $ad)
							{
								?><?= $ad['TITLE'] ?><?
								if ($ad['TITLE_2'])
								{
									?><br /><?= $ad['TITLE_2'] ?><?
								}
								break;
							}
						}
						elseif ($col == 'text')
						{
							foreach ($fads as $ad)
							{
								?><?= $ad['TEXT'] ?><?
								break;
							}
						}
						elseif ($col == 'preview')
						{
							foreach ($fads as $ad)
							{
								\Local\Main\Ad::printExample($ad);
								break;
							}
						}
						elseif ($col == 'action')
						{
							if ($y)
							{
								?>
								<a class="btn add_yandex_ad" target="_blank"
								   href="<?= \Local\Main\Ad::getAddYandexHref($category, $item) ?>"
								   title="Добавить объявление для <?= DIRECT_NAME ?>"><b></b><i></i></a><?
							}
							if ($g)
							{
								?>
								<a class="btn add_google_ad" target="_blank"
								   href="<?= \Local\Main\Ad::getAddGoogleHref($category, $item) ?>"
								   title="Добавить объявление для <?= ADWORDS_NAME ?>"><b></b><i></i></a><?
							}
						}

						?></td><?
					}

					?>
				</tr><?

				foreach ($fads as $i => $ad)
				{
					if (!$i)
						continue;

					?>
					<tr><?

						?><td>
							<a class="btn edit" target="_blank"
							   href="<?= \Local\Main\Ad::getHref($category, $item, $ad) ?>"
							   title="Редактировать объявление"><i></i></a>
						</td><?

						foreach ($view['DATA']['COLUMNS'] as $col)
						{
							if ($col == 'title' || $col == 'text' || $col == 'preview')
							{

								$class = '';
								if ($col == 'preview')
									$class = ' class="preview"';

								?><td<?= $class ?>><?

								if ($col == 'title')
								{
									?><?= $ad['TITLE'] ?><?
									if ($ad['TITLE_2'])
									{
										?><br/><?= $ad['TITLE_2'] ?><?
									}
								}
								elseif ($col == 'text')
								{
									?><?= $ad['TEXT'] ?><?
								}
								elseif ($col == 'preview')
								{
									\Local\Main\Ad::printExample($ad);
								}

								?></td><?
							}
						}

						?>
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