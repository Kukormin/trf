<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$category = \Local\Main\Category::getById($categoryId, $projectId);

if ($category)
{
	$title = array();
	foreach ($_REQUEST['w1'] as $item)
	{
		$item = trim($item);
		if ($item)
			$title[$item] = strlen($item);
	}
	arsort($title);

	$text = array();
	foreach ($_REQUEST['w2'] as $item)
	{
		$item = trim($item);
		if ($item)
			$text[$item] = strlen($item);
	}
	arsort($text);

	$data = array(
		'TITLE_PLUS' => array_keys($title),
		'TEXT_PLUS' => array_keys($text),
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