<?
namespace Local\Direct;

use \Local\TmpGroups;

class Carma
{
	const overdue = 86400;

	/**
	 * @param $url
	 * @param $keywords array 200 штук ключевых фраз
	 * @param $region
	 * @return array
	 */
	public static function get($url, $keywords, $region)
	{
		$return = array();
		
		$url = htmlspecialchars($url);
		$keyword = htmlspecialchars($keywords[0]);
		$region = htmlspecialchars($region);
		if (!$region)
			$region = '0';
		$arRegions = explode(',', $region);

		$key = $region . '|' . $url;
		$arGroup = TmpGroups::getByKey($key);
		if (!$arGroup) {
			$arOldest = TmpGroups::getOldest();
			if (!$arOldest || MakeTimeStamp($arOldest['TIMESTAMP_X']) > time() - self::overdue)
			{
				$err = TmpGroups::add($key, $arRegions, $url, $keyword);
				if (!$err)
					$arGroup = TmpGroups::getByKey($key);
			}
			else
			{
				$err = TmpGroups::update($arOldest, $key, $arRegions, $url, $keyword);
				if (!$err)
					$arGroup = TmpGroups::getByKey($key);
			}
		}

		if ($arGroup) {
			$return['ITEMS'] = TmpGroups::addKeywords($arGroup, $keywords);
		}

		return $return;
	}

}
