<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$project = \Local\Project::getById($projectId);
if ($project)
{
	$cats = \Local\Category::getByProject($project['ID']);
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
			$category = \Local\Category::getById($categoryId, $project['ID']);
			$category = \Local\Category::update($category, array(
				'NAME' => $name,
			));
			if ($category['UPDATED'])
				$return['name'] = $category['NAME'];
		}
		else
		{
			$category = \Local\Category::add($name, $project['ID']);
			if ($category['NEW'])
				$return['redirect'] = \Local\Category::getHref($category) . 'settings/';
		}
	}
}

$return['EX'] = $ex;
