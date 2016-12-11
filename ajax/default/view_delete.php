<?
$return = array();

$viewId = intval($_REQUEST['id']);

$views = \Local\Main\View::getByCurrentUser();
foreach ($views as $view)
{
	if ($view['ID'] == $viewId)
	{
		\Local\Main\View::delete($viewId);
		break;
	}
}
