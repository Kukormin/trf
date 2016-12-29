<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$category = \Local\Main\Category::getById($categoryId, $projectId);

if ($category)
{
	$res = \Local\System\Task::addCategoryWordstat($categoryId, $projectId, 'combo');

	if ($res)
	{
		$return['messages'] = array(
			array('<p>Задача добавлена</p>', 'success'),
		);
		$return['tasks'] = true;
	}
	//$result = \Local\Main\Category::combo($category);
	//$ws = new \Local\Yandex\Wordstat();
	//$ws->getStat();
}
