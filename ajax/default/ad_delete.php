<?
$return = array();

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);
$keygroupId = intval($_REQUEST['kgid']);
$adId = intval($_REQUEST['adid']);

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	if ($adId)
	{
		$ad = \Local\Main\Ad::getById($adId, $keygroupId);
		if ($ad)
			\Local\Main\Ad::delete($ad);
	}
}
