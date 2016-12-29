<?
$return = array();

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	foreach ($_REQUEST['data'] as $keygroupId => $keygroupData)
	{
		$clearCache = false;
		foreach ($keygroupData as $adId => $adData)
		{
			$newAd = array();
			$platform = false;

			foreach ($adData as $col => $value)
			{
				$key = '';
				if ($col == 'title')
					$key = 'TITLE';
				elseif ($col == 'title2')
					$key = 'TITLE_2';
				elseif ($col == 'text')
					$key = 'TEXT';
				elseif ($col == 'url')
					$key = 'URL';
				elseif ($col == 'link')
					$key = 'LINK';
				elseif ($col == 'link2')
					$key = 'LINK_2';
				elseif ($col == 'platform')
				{
					$newAd['YANDEX'] = $value[0] == 'y' ? 1 : 0;
					$newAd['SEARCH'] = $value[1] == 's' ? 1 : 0;
					$platform = true;
				}

				if ($key)
					$newAd[$key] = htmlspecialchars($value);
			}

			if (substr($adId, 0, 1) == '~')
			{
				if ($newAd)
				{
					$newAd['GROUP'] = $keygroupId;
					$newAd['CATEGORY'] = $categoryId;
					$newAd['PROJECT'] = $projectId;
					if (!$platform)
					{
						$newAd['YANDEX'] = 1;
						$newAd['SEARCH'] = 1;
					}
					$id = \Local\Main\Ad::add($newAd);
					$return['added'][substr($adId, 1)] = $id;
					$clearCache = true;
				}
			}
			else
			{
				$ad = \Local\Main\Ad::getById($adId, $keygroupId);
				if (!$ad)
					$ad = \Local\Main\Ad::getById($adId, $keygroupId, true);
				if ($ad)
				{
					\Local\Main\Ad::update($ad, $newAd);
					$clearCache = true;
				}
			}
		}
		if ($clearCache)
			\Local\Main\Ad::getByKeygroup($keygroupId, true);
	}
}
