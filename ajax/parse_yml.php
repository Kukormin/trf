<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$catalog = array();
	$project = \Local\Project::getAdding();

	if ($project['YML'])
	{
		if ($project['DATA']['CATALOG'])
			$catalog = $project['DATA']['CATALOG'];
		else
		{
			$catalog = \Local\Parser::yml($project['YML']);
			if ($catalog)
			{
				$data = array(
					'DATA' => array(
						'CATALOG' => $catalog,
					),
				);
				\Local\Project::update($project, $data);
			}
		}
	}

	$APPLICATION->IncludeComponent('tim:empty', 'catalog', array('TREE' => $catalog));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}