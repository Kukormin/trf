<?
// TODO:: убрать
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$project = \Local\Project::getById($_REQUEST['pid']);
	if ($project)
	{
		$data = array();
		foreach ($_REQUEST['title'] as $i => $title)
		{
			$item = array(
				'Title' => trim($title),
			    'Href' => trim($_REQUEST['href'][$i]),
			    'Description' => trim($_REQUEST['desc'][$i]),
			);
			$data['ITEMS'][] = $item;
		}
		$fields = array(
			'NAME' => trim($_REQUEST['name']),
			'PROJECT' => $project['ID'],
			'DATA' => $data,
		);

		$set = \Local\Linkset::getById($_REQUEST['sid'], $project['ID']);
		if ($set)
		{
			$set = \Local\Linkset::update($set, $fields);
			if ($set['UPDATED'])
			{
				?>
				<div class="alert alert-success">
					<button class="close" data-dismiss="alert" type="button">×</button>
					<p>Набор быстрых ссылок успешно изменен.</p>
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
		else
		{
			$set = \Local\Linkset::add($fields);
			if ($set)
			{
				?>
				<div class="alert alert-success">
					<button class="close" data-dismiss="alert" type="button">×</button>
					<p>Набор быстрых ссылок успешно добавлен.</p>
				</div><?
			}
			else
			{
				?>
				<div class="alert alert-error">
					<button class="close" data-dismiss="alert" type="button">×</button>
					<p>Ошибка добавления набора быстрых ссылок.</p>
				</div><?
			}
		}
	}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}