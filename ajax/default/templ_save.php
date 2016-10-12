<?
$return = array();

$templId = intval($_REQUEST['tid']);
$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$project = \Local\Main\Project::getById($projectId);
if ($project)
{
	$templs = \Local\Main\Templ::getByProject($project['ID']);
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
			'REPLACE' => $_REQUEST['replace'] == 'on',
			'TITLE_PLUS' => $_REQUEST['title_plus'] == 'on',
			'TEXT_TITLE' => $_REQUEST['text_title'] == 'on',
			'TEXT_TITLE_PLUS' => $_REQUEST['text_title_plus'] == 'on',
			'TEXT_PLUS' => $_REQUEST['text_plus'] == 'on',
			'LINKSET' => $_REQUEST['linkset'],
			'VCARD' => $_REQUEST['vcard'],
		);
		$newTempl = array(
			'NAME' => $name,
			'PROJECT' => $project['ID'],
			'DATA' => $data,
		);

		if ($templId)
		{
			$templ = \Local\Main\Templ::getById($templId, $project['ID']);
			$templ = \Local\Main\Templ::update($templ, $newTempl);
		}
		else
			$templ = \Local\Main\Templ::add($newTempl);

		$return['redirect'] = \Local\Main\Templ::getListHref($project['ID']);
	}
}

$return['EX'] = $ex;
