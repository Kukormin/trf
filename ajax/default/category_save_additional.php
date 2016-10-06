<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$campaign = \Local\Category::getById($categoryId, $projectId);

if ($campaign)
{
	$addWords = array();
	foreach (explode("\n", $_REQUEST['add_words']) as $word)
	{
		$word = trim($word);
		if ($word)
			$addWords[$word] = $word;
	}

	$result = \Local\Category::additionalWords($campaign, $addWords, $_REQUEST['de'] == 'on');

	$data = array(
		'ADD_WORDS' => $result['WORDS'],
	);
	$campaign = \Local\Category::update($campaign, array('DATA' => $data));

	if ($campaign['UPDATED'])
	{
		ob_start();

		?>
		<p>Результаты работы:</p>
		<ul>
			<li>Добавлено дополнительных фраз: <b><?= $result['ADD'] ?></b></li>
			<li>Активировано старых: <b><?= $result['ACTIV'] ?></b></li>
			<li>Деактивировано: <b><?= $result['DEACTIV'] ?></b></li>
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

		$return['alerts'] = array(
			array($html, 'success'),
		);
	}
	else
	{
		$return['alerts'] = array(
			array('<p>Нет изменений</p>', 'block'),
		);
	}
}