<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$category = \Local\Main\Category::getById($categoryId, $projectId);

if ($category)
{
	$addWords = array();
	foreach (explode("\n", $_REQUEST['add_words']) as $word)
	{
		$word = trim($word);
		if ($word)
			$addWords[$word] = $word;
	}

	$result = \Local\Main\Category::additionalWords($category, $addWords, $_REQUEST['de'] == 'on');

	$data = array(
		'ADD_WORDS' => $result['WORDS'],
	);
	$category = \Local\Main\Category::update($category, array('DATA' => $data));

	if ($category['UPDATED'])
	{
		ob_start();

		?>
		<p>Результаты работы:</p>
		<ul>
			<li>Добавлено дополнительных фраз: <b><?= $result['ADD'] ?></b></li>
			<li>Активировано старых: <b><?= $result['ACTIV'] ?></b></li>
			<li>Удалено: <b><?= $result['DELETE'] ?></b></li>
			<li>Фраза уже является базовой: <b><?= $result['BASE'] ?></b></li>
			<li>Без изменения: <b><?= $result['NO'] ?></b></li>
		</ul>
		<p>Текущее состояние:</p>
		<ul>
			<li>Активных базовых ключевых фраз: <b><?= $result['NEW'] ?></b></li>
			<li>Деактивированных фраз: <b><?= $result['OLD'] ?></b></li>
			<li>Дополнительных (вручную добавленных) фраз: <b><?= $result['USER'] + $result['ADD'] +
					$result['ACTIV'] + $result['NO'] ?></b></li>
		</ul><?

		$html = ob_get_contents();
		ob_end_clean();

		$return['messages'] = array(
			array($html, 'success'),
		);
	}
	else
	{
		$return['messages'] = array(
			array('<p>Нет изменений</p>', 'block'),
		);
	}
}