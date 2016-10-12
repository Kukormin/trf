<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$category = \Local\Main\Category::getById($categoryId, $projectId);

if ($category)
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