<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$result = array();
	$project = \Local\Main\Project::getAdding();

	if ($project['URL'])
	{
		$result = \Local\Main\Parser::metric($project['URL']);
		$result['GOOGLE_CLIENT'] = $project['GOOGLE_CLIENT'];
		$result['YANDEX_CLIENT'] = $project['YANDEX_CLIENT'];
	}

	header('Content-Type: application/json');
	echo json_encode($result);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}