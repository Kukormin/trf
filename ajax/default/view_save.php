<?
$return = array();

$viewId = intval($_REQUEST['vid']);
$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$views = \Local\Main\View::getByCurrentUser();
foreach ($views as $view)
{
	if ($view['ID'] == $viewId)
		continue;

	if ($view['NAME'] == $name)
	{
		$ex = true;
		break;
	}
}

if (!$ex && !$onlyCheck)
{
	$columns = $_REQUEST['columns'];
	if (in_array('cb', $columns))
		$columns[] = 'cb';
	if (in_array('name', $columns))
		$columns[] = 'name';
	if (in_array('action', $columns))
		$columns[] = 'action';

	$data = array(
		'AD_COUNT' => intval($_REQUEST['ad_count']),
		'COLUMNS' => $_REQUEST['columns'],
	);
	$newView = array(
		'NAME' => $name,
		'DATA' => $data,
	);

	if ($viewId)
	{
		$view = \Local\Main\View::getById($viewId);
		$view = \Local\Main\View::update($view, $newView);
	}
	else
		$view = \Local\Main\View::add($newView);

	$return['redirect'] = \Local\Main\View::getViewsHref();
}

$return['EX'] = $ex;
