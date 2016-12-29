<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$category = \Local\Main\Category::getById($categoryId, $projectId);

if ($category)
{
	$result = \Local\Main\Category::comboReport($category);

	ob_start();

	?>
	<p>Ключевых фраз в категории (на текущий момент): <b><?= $result['CURRENT'] ?></b></p>
	<p>Ключевых фраз сгенерировано: <b><?= $result['GENERATE'] ?></b></p>
	<ul>
		<li>Новых фраз: <b><?= $result['NEW'] ?></b></li>
		<li>Сгенерированная фраза добавлена ранее: <b><?= $result['EX_BASE'] ?></b></li>
		<li>Фраза добавлена вручную: <b><?= $result['EX_ADD'] ?></b></li>
	</ul><?

	$html = ob_get_contents();
	ob_end_clean();

	$return['messages'] = array(
		array($html, 'success'),
	);

	ob_start();

	?>
	<input type="hidden" name="cid" value="<?= $categoryId ?>" />
	<input type="hidden" name="pid" value="<?= $projectId ?>" />
	<button class="btn ws">Проверить частотность</button><?

	/*
	?>
	<p><input type="checkbox" class="new" /> Показывать только новые фразы</p><?*/

	?>
	<p>Выделить только с частотностью &gt; <input type="text" class="ws_val input-small" value="0" />
		<button class="btn ws_apply">Применить</button></p>
	<p>
		Выбрано <span class="scnt"><?= $result['GENERATE'] ?></span> из <span class="allcnt"><?= $result['GENERATE'] ?></span>
		<button class="btn add_selected">Добавить</button> в текущую категорию "<?= $category['NAME'] ?>"
	</p>
	<table id="combo_results" class="table table-striped table-hover">
	<thead>
	<tr>
		<th class="num"></th>
		<th class="cb"><input class="all" type="checkbox" checked /></th>
		<th>Ключевая фраза</th>
		<th>Статус</th>
		<th>wordstat</th>
	</tr>
	</thead>
	<tbody><?

	$i = 0;
	foreach ($result['ITEMS'] as $item)
	{
		$i++;
		$class = $item['TYPE'] == 'new' ? '' : ' class="old"';
		$ws = $item['WS'] === false ? '' : $item['WS'];
		$trws = $item['WS'] === false ? '-1' : $item['WS'];
		?>
		<tr<?= $class ?> data-ws="<?= $trws ?>">
			<td><?= $i ?></td>
			<td><input class="cb" type="checkbox" name="kw[<?= $item['BASE'] ?>]" checked /></td>
			<td><?= $item['KW'] ?></td>
			<td><?= $item['TYPE'] ?></td>
			<td><?= $ws ?></td>
		</tr><?
	}
	?>
	</tbody>
	</table><?

	$html = ob_get_contents();
	ob_end_clean();

	$return['HTML'] = $html;
}
