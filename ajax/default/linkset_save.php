<?
$return = array();

$setId = intval($_REQUEST['sid']);
$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$project = \Local\Project::getById($projectId);
if ($project)
{
	$sets = \Local\Linkset::getByProject($project['ID']);
	foreach ($sets as $set)
	{
		if ($set['ID'] == $setId)
			continue;

		if ($set['NAME'] == $name)
		{
			$ex = true;
			break;
		}
	}

	if (!$ex && !$onlyCheck)
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
		$newSet = array(
			'NAME' => $name,
			'PROJECT' => $project['ID'],
			'DATA' => $data,
		);

		if ($setId)
		{
			$set = \Local\Linkset::getById($setId, $project['ID']);
			$set = \Local\Linkset::update($set, $newSet);
		}
		else
			$set = \Local\Linkset::add($newSet);

		$return['redirect'] = \Local\Linkset::getListHref($project['ID']);
	}
}

$return['EX'] = $ex;
