<?
$return = array();

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);
$keygroupId = intval($_REQUEST['kgid']);

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	$base = $_REQUEST['base'];
	$parts = explode('|', $base);
	$baseCount = count($parts);
	$type = \Local\Main\Keygroup::TYPE_BASE;
	if ($keygroupId)
	{
		$keygroup = \Local\Main\Keygroup::getById($keygroupId, $categoryId, $projectId);
		if ($keygroup)
			\Local\Main\Keygroup::updateBaseType($keygroupId, $base, $baseCount, $type);
		else
			return false;
	}
	else
	{
		$kw = '';
		foreach ($parts as $part)
		{
			$tmp = explode('#', $part);
			if ($kw)
				$kw .= ' ';
			$kw .= $tmp[1];
		}
		\Local\Main\Keygroup::add($projectId, $categoryId, $kw, $base, $baseCount, $type);
	}

	\Local\Main\Keygroup::clearCache($category['PROJECT'], $category['ID']);
	$return['OK'] = true;
}
