<?
$return = '';

$data = array();

if (isset($_REQUEST['filters_show']))
	$data['FILTERS_SHOW'] = $_REQUEST['filters_show'];
if (isset($_REQUEST['filters_active']))
{
	$tmp = explode('|', $_REQUEST['filters_active']);
	$active = array();
	foreach ($tmp as $item)
		$active[$item] = 1;
	$data['FILTERS_ACTIVE'] = $active;
}

if ($data)
	\Local\Main\User::saveData($data);
