<?
// TODO:: убрать
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$project = \Local\Project::getById($_REQUEST['pid']);
	$campaign = \Local\Category::getById($_REQUEST['cid'], $_REQUEST['pid']);

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
			?>
			<div class="alert alert-success">
				<button class="close" data-dismiss="alert" type="button">×</button>
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
				</ul>
			</div><?
		}
		else
		{
			?>
			<div class="alert alert-block">
				<button class="close" data-dismiss="alert" type="button">×</button>
				<p>Нет изменений</p>
			</div><?
		}
	}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}