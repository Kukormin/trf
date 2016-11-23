<?
$return = array();

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);
$templId = intval($_REQUEST['tid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	$templs = \Local\Main\Templ::getByCategory($category['ID']);
	foreach ($templs as $templ)
	{
		if ($templ['ID'] == $templId)
			continue;

		if ($templ['NAME'] == $name)
		{
			$ex = true;
			break;
		}
	}

	if (!$ex && !$onlyCheck)
	{
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
		$newTempl = array(
			'NAME' => $name,
			'CATEGORY' => $category['ID'],
			'YANDEX' => intval($_REQUEST['yandex']),
			'SEARCH' => intval($_REQUEST['search']),
			'DATA' => $data,
		);

		if ($templId)
		{
			$templ = \Local\Main\Templ::getById($templId, $category['ID']);
			$templ = \Local\Main\Templ::update($templ, $newTempl);
		}
		else
			$templ = \Local\Main\Templ::add($newTempl);

		$return['redirect'] = \Local\Main\Templ::getListHref($category);
	}
}

$return['EX'] = $ex;
