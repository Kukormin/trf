<?
$return = '';

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);
$keygroupId = intval($_REQUEST['kgid']);
$adId = intval($_REQUEST['adid']);

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	$ad = array(
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
		'PROJECT' => $projectId,
		'CATEGORY' => $categoryId,
		'GROUP' => $keygroupId,
	);

	$project = \Local\Main\Project::getById($projectId);
	$ad['HOST'] = $project['URL'];
	$ad['SCHEME'] = $category['DATA']['SCHEME'];
	\Local\Main\Ad::printExample($ad);
}
