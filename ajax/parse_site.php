<?
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$result = array();
	$project = \Local\Project::getAdding();

	if ($project['URL'])
	{
		$result = \Local\Parser::site($project['URL']);
	}

	header('Content-Type: application/json');
	echo json_encode($result);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}