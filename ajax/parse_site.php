<?
// TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$result = array();
	$project = \Local\Main\Project::getAdding();

	if ($project['URL'])
	{
		$result = \Local\Main\Parser::site($project['URL']);
		if ($result === false)
			$result = array('error' => 'load_error');
	}

	header('Content-Type: application/json');
	echo json_encode($result);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}