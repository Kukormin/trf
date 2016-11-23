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
	$newAd = array(
		'YANDEX' => intval($_REQUEST['yandex']),
		'SEARCH' => intval($_REQUEST['search']),
		'TITLE' => htmlspecialchars($_REQUEST['title']),
		'TITLE_2' => htmlspecialchars($_REQUEST['title_2']),
		'TEXT' => htmlspecialchars($_REQUEST['text']),
		'URL' => htmlspecialchars($_REQUEST['url']),
		'LINK' => htmlspecialchars($_REQUEST['link']),
		'LINK_2' => htmlspecialchars($_REQUEST['link_2']),
		'LINKSET' => intval($_REQUEST['linkset']),
		'VCARD' => intval($_REQUEST['vcard']),
	);

	if ($adId)
	{
		$ad = \Local\Main\Ad::getById($adId, $keygroupId);
		$ad = \Local\Main\Ad::update($ad, $newAd);
	}
	else
	{
		$newAd['GROUP'] = $keygroupId;
		$newAd['CATEGORY'] = $categoryId;
		$newAd['PROJECT'] = $projectId;
		$ad = \Local\Main\Ad::add($newAd);
	}

	$keygroup = \Local\Main\Keygroup::getById($keygroupId, $categoryId, $projectId);
	$return['redirect'] = \Local\Main\Keygroup::getHref($category, $keygroup);
}
