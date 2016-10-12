<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$category = \Local\Main\Category::getById($categoryId, $projectId);

if ($category)
{
	$replace = array();
	foreach ($_REQUEST['from'] as $i => $from)
	{
		$from = trim($from);
		$to = trim($_REQUEST['to'][$i]);
		if ($from)
			$replace[$from] = $to;
	}

	$data = array(
		'REPLACE' => $replace,
	);
	$category = \Local\Main\Category::update($category, array('DATA' => $data));

	if ($category['UPDATED'])
	{
		$return['alerts'] = array(
			array('<p>Успешно сохранено</p>', 'success'),
		);
	}
	else
	{
		$return['alerts'] = array(
			array('<p>Нет изменений</p>', 'block'),
		);
	}
}