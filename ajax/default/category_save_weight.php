<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$campaign = \Local\Category::getById($categoryId, $projectId);

if ($campaign)
{
	$weight = array();
	foreach ($_REQUEST['w'] as $item)
	{
		$item = trim($item);
		if ($item)
			$weight[$item] = strlen($item);
	}
	arsort($weight);

	$data = array(
		'WEIGHT' => array_keys($weight),
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