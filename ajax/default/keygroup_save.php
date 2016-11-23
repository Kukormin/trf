<?
$return = array();

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);
$keygroupId = intval($_REQUEST['kgid']);

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	$keygroup = \Local\Main\Keygroup::getById($keygroupId, $categoryId, $projectId);
	if ($keygroup)
	{
		$marks = array_keys($_REQUEST['mark']);
		if (!$marks)
			$marks = array();
		$newKeygroup = array(
		    'MARKS' => $marks,
		    'TEMPLATES' => $templates,
		);
		\Local\Main\Keygroup::update($keygroup, $newKeygroup);
	}
}
