<?
$return = '';

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	$project = \Local\Main\Project::getById($projectId);

	$construct = array();
	$base = $category['DATA']['BASE'];
	foreach ($_REQUEST['c'] as $key => $area)
	{
		foreach ($area['p'] as $i => $partKey)
		{
			if ($partKey == 'text')
				$data = $area['text'][$i];
			elseif ($partKey == 'keyword')
				$data = '';
			else
			{
				$col = substr($partKey, 3);
				$data = array();
				if ($area['col'][$i][0])
					$data[''] = $area['col'][$i][0];
				foreach ($base[$col]['WORDS'] as $j => $baseWord)
				{
					$word = $area['col'][$i][$j + 1];
					if ($word != $baseWord)
						$data[$baseWord] = $word;
				}
			}

			$construct[$key][] = array(
				'KEY' => $partKey,
				'D' => $data,
			);
		}
	}
	$data = array(
		'CONSTRUCT' => $construct,
		'TITLE_56' => $_REQUEST['title_56'] == 'on',
		'TITLE_FIRST_BIG' => $_REQUEST['title_first_big'] == 'on',
		'TITLE_2_FIRST_BIG' => $_REQUEST['title_2_first_big'] == 'on',
		'TEXT_FIRST_BIG' => $_REQUEST['text_first_big'] == 'on',
		'LINKSET' => intval($_REQUEST['linkset']),
		'VCARD' => intval($_REQUEST['vcard']),
	);

	$templ = array(
		'CATEGORY' => $category['ID'],
		'YANDEX' => intval($_REQUEST['yandex']),
		'SEARCH' => intval($_REQUEST['search']),
		'DATA' => $data,
	);

	// TODO: оптимизировать
	$keygroups = \Local\Main\Keygroup::getList($projectId, $categoryId);
	$items = $keygroups['ITEMS'];
	shuffle($items);
	$i = 0;
	foreach ($items as $keygroup)
	{
		$ad = \Local\Main\Ad::generateByTemplate($keygroup, $templ, $category, $project['URL']);
		\Local\Main\Ad::printExample($ad);

		$i++;
		if ($i == 3)
			break;
	}

}
