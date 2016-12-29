<?
$return = array();

$task = \Local\System\Task::getByCurrentUser();

if (!$task)
{
	$alerts = \Local\System\Alerts::getActive();
	$return['off'] = true;
	foreach ($alerts as $alert)
		$return['alerts'][$alert['ID']] = \Local\System\Alerts::getHtml($alert);
}