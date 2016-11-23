<?
$return = array();

$categoryId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$category = \Local\Main\Category::getById($categoryId, $projectId);

if ($category)
{
	$baseWords = array();
	foreach ($_REQUEST['w'] as $k => $words)
	{
		$i = count($words);
		while (!$words[$i - 1] && $i > 0)
			$i--;

		if ($i > 0)
		{
			$words = array_slice($words, 0, $i);
			$baseWords[] = array(
				'WORDS' => $words,
				'REQ' => $_REQUEST['r'][$k] == '1',
			);
		}
	}
	$max = intval($_REQUEST['max']);
	if ($max < 1)
		$max = 4;
	$data = array(
		'BASE' => $baseWords,
		'BASE_MAX' => $max,
	);
	$category = \Local\Main\Category::update($category, array('DATA' => $data));
}
