<?
$return = array();

$templId = intval($_REQUEST['tid']);
$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$project = \Local\Project::getById($projectId);
if ($project)
{
	$templs = \Local\Templ::getByProject($project['ID']);
	foreach ($templs as $templ)
	{
		if ($templ['ID'] == $templId)
			continue;

		if ($templ['NAME'] == $name)
		{
			$ex = true;
			break;
		}
	}

	if (!$ex && !$onlyCheck)
	{
		$data = array(
			'TEXT' => $_REQUEST['text'],
			'WEIGHT' => $_REQUEST['weight'] == 'on',
			'REPLACE' => $_REQUEST['replace'] == 'on',
			'LINKSET' => $_REQUEST['linkset'],
			'VCARD' => $_REQUEST['vcard'],
			'SCHEME' => $_REQUEST['scheme'],
			'URL' => $_REQUEST['url'],
			'DURL' => $_REQUEST['durl'],
		);
		$newTempl = array(
			'NAME' => $name,
			'PROJECT' => $project['ID'],
			'DATA' => $data,
		);

		if ($templId)
		{
			$templ = \Local\Templ::getById($templId, $project['ID']);
			$templ = \Local\Templ::update($templ, $newTempl);
		}
		else
			$templ = \Local\Templ::add($newTempl);

		$return['redirect'] = \Local\Templ::getListHref($project['ID']);
	}
}

$return['EX'] = $ex;
