<?
$return = array();

$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$url = trim($_REQUEST['url']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$projects = \Local\Main\Project::getByCurrentUser();
foreach ($projects as $project)
{
	if ($project['ID'] == $projectId)
		continue;

	if ($project['NAME'] == $name)
	{
		$ex = true;
		break;
	}
}

$return['EX'] = $ex;

if (!$ex && !$onlyCheck)
{
	if ($projectId)
	{
		$project = \Local\Main\Project::getById($projectId);
		$project = \Local\Main\Project::update($project, array(
			'NAME' => $name,
		));
		if ($project['UPDATED'])
			$return['name'] = $project['NAME'];
	}
	else
	{
		$project = \Local\Main\Project::add($url, $name);
		if ($project['NEW'])
			$return['redirect'] = \Local\Main\Project::getHref($project['ID']) . 'settings/';
	}
}