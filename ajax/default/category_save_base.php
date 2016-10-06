<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$campaign = \Local\Category::getById($categoryId, $projectId);

if ($campaign)
{
	$baseWords = array();
	foreach ($_REQUEST['w'] as $k => $words)
	{
		$words1 = array();
		foreach (explode("\n", $words) as $word)
		{
			$word = trim($word);
			if ($word)
				$words1[] = $word;
		}
		if ($words1) {
			$baseWords[] = array(
				'WORDS' => $words1,
				'REQ' => $_REQUEST['r'][$k] == 'on',
			);
		}
	}
	$data = array(
		'BASE' => $baseWords,
		'BASE_MAX' => intval($_REQUEST['max']),
	);
	$campaign = \Local\Category::update($campaign, array('DATA' => $data));

	if ($campaign['UPDATED'])
	{
		$result = \Local\Category::combo($campaign);

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
