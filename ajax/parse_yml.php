<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$catalog = array();
	$links = array();
	$project = \Local\Main\Project::getAdding();

	if ($project['YML'])
	{
		if ($project['DATA']['CATALOG'])
			$catalog = $project['DATA']['CATALOG'];
		else
		{
			$catalog = \Local\Main\Parser::yml($project['YML']);
			if ($catalog)
			{
				$data = array(
					'DATA' => array(
						'CATALOG' => $catalog,
					),
				);
				\Local\Main\Project::update($project, $data);
			}
		}
	}

	if ($project['URL'])
	{
		if ($project['DATA']['LINKS'])
			$links = $project['DATA']['LINKS'];
		else
		{
			$links = \Local\Main\Parser::links($project['URL']);
			if ($links)
			{
				$data = array(
					'DATA' => array(
						'LINKS' => $links,
					),
				);
				\Local\Main\Project::update($project, $data);
			}
		}
	}

	$APPLICATION->IncludeComponent('tim:empty', 'catalog', array(
		'CATALOG' => $catalog,
		'LINKS' => $links,
	    'URL' => $project['URL'],
	));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}