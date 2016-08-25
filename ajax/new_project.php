<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

	$return = array();
	try
	{
		$return = \Local\Project::addFormSubmit();
	}
	catch (Exception $e)
	{
		// TODO: формулировку
		$return['errors'][] = 'При сохранении данных возникла ошибка.';
	}
	if (isset($return['errors']) && !$return['errors'])
		unset($return['errors']);

	header('Content-Type: application/json');
	echo json_encode($return);

	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
}