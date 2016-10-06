<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$campaign = \Local\Category::getById($categoryId, $projectId);

if ($campaign)
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
	$campaign = \Local\Category::update($campaign, array('DATA' => $data));

	if ($campaign['UPDATED'])
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