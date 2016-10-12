<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$project = \Local\Main\Project::getById($projectId);
if ($project)
{
	$cats = \Local\Main\Category::getByProject($project['ID']);
	foreach ($cats as $cat)
	{
		if ($cat['ID'] == $categoryId)
			continue;

		if ($cat['NAME'] == $name)
		{
			$ex = true;
			break;
		}
	}

	if (!$ex && !$onlyCheck)
	{
		if ($categoryId)
		{
			$category = \Local\Main\Category::getById($categoryId, $project['ID']);
			$category = \Local\Main\Category::update($category, array(
				'NAME' => $name,
			));
			if ($category['UPDATED'])
				$return['name'] = $category['NAME'];
		}
		else
		{
			$category = \Local\Main\Category::add($name, $project['ID']);
			if ($category['NEW'])
				$return['redirect'] = \Local\Main\Category::getHref($category) . 'settings/';
		}
	}
}

$return['EX'] = $ex;
