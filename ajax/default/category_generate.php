<?
$return = array();

/*$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$category = \Local\Main\Category::getById($categoryId, $projectId);

if ($category)
{
	$result = \Local\Main\Category::combo($category);

	ob_start();

	?>
	<p>Результаты работы:</p>
	<ul>
		<li>Ключевых фраз сгенерировано: <b><?= $result['NEW'] ?></b></li>
		<li>Добавлено новых фраз: <b><?= $result['ADD'] ?></b></li>
		<li>Активировано старых: <b><?= $result['ACTIV'] ?></b></li>
		<li>Деактивировано: <b><?= $result['DEACTIV'] ?></b></li>
		<li>Дополнительных фраз переведено в базовые: <b><?= $result['TO_BASE'] ?></b></li>
		<li>Без изменения: <b><?= $result['NO'] ?></b></li>
	</ul>
	<p>Текущее состояние:</p>
	<ul>
		<li>Активных базовых ключевых фраз: <b><?= $result['NEW'] ?></b></li>
		<li>Деактивированных фраз: <b><?= $result['OLD'] ?></b></li>
		<li>Дополнительных (вручную добавленных) фраз: <b><?= $result['USER'] ?></b></li>
	</ul><?

	$html = ob_get_contents();
	ob_end_clean();

	$return['messages'] = array(
		array($html, 'success'),
	);
}
*/