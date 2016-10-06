<?
$return = array();

$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$url = trim($_REQUEST['url']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$projects = \Local\Project::getByCurrentUser();
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
		$project = \Local\Project::getById($projectId);
		$project = \Local\Project::update($project, array(
			'NAME' => $name,
		));
		if ($project['UPDATED'])
			$return['name'] = $project['NAME'];
	}
	else
	{
		$project = \Local\Project::add($url, $name);
		if ($project['NEW'])
			$return['redirect'] = \Local\Project::getHref($project['ID']) . 'settings/';
	}
}