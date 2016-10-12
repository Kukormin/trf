<?
namespace Local\Utils;

use Local\Api\ApiException;
use Local\Direct\Api5;
use Local\System\ExtCache;

/**
 * Регионы
 */
class Region
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Utils/Region/';

	/**
	 * ID инфоблока с регионами
	 */
	const IBLOCK_ID = 9;

	/**
	 * Возвращает всех регионы
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getAll($refreshCache = false)
	{
		$extCache = new ExtCache(
			array(
				__FUNCTION__,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			86400 * 100,
			false
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$return = array();

			$iblockElement = new \CIBlockElement();
			$rsItems = $iblockElement->GetList(array(), array(
				'IBLOCK_ID' => self::IBLOCK_ID,
			), false, false, array(
				'ID', 'NAME', 'CODE',
				'PROPERTY_YANDEX', 'PROPERTY_TYPE',
			));
			while ($item = $rsItems->Fetch())
			{
				$yandex = intval($item['PROPERTY_YANDEX_VALUE']);
				$return[$yandex] = array(
					'ID' => intval($item['ID']),
					'NAME' => $item['NAME'],
					'PARENT' => intval($item['CODE']),
					'YANDEX' => $yandex,
				    'TYPE' => $item['PROPERTY_TYPE_VALUE']
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getByYandex($id)
	{
		$all = self::getAll();
		return $all[$id];
	}

	/*public static function getByName($name)
	{
		$all = self::getAll();
		foreach ($all as $id => $region)
		{
			if ($region['NAME'] == $name)
				return $region;
		}

		return false;
	}*/

	/**
	 * Проверяет регион на соответствие данным от Яндекса
	 * @param $id
	 * @param $parent
	 * @param $name
	 * @param $type
	 * @return bool
	 */
	public static function check($id, $parent, $name, $type)
	{
		$updated = false;
		$iblockElement = new \CIBlockElement();

		$region = self::getByYandex($id);
		if (!$region)
		{
			$iblockElement->Add(array(
				'IBLOCK_ID' => self::IBLOCK_ID,
				'NAME' => $name,
				'CODE' => $parent,
				'PROPERTY_VALUES' => array(
					'YANDEX' => $id,
				    'TYPE' => $type,
				),
			));
			return true;
		}

		$update = array();
		if ($region['PARENT'] != $parent)
			$update['CODE'] = $parent;
		if ($region['NAME'] != $name)
			$update['NAME'] = $name;
		if ($update)
		{
			$iblockElement->Update($region['ID'], $update);
			$updated = true;
		}

		if ($region['TYPE'] != $type)
		{
			$iblockElement->SetPropertyValuesEx($region['ID'], self::IBLOCK_ID, array('TYPE' => $type));
			$updated = true;
		}

		return $updated;
	}

	public static function sync()
	{
		$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'dictionaries');

		$res = $api->method('get', array(
			'DictionaryNames' => array(
				'GeoRegions',
			),
		));
		if ($res['error'])
			throw new ApiException($res['error'], 500);

		$cache = false;
		foreach ($res['result']['GeoRegions'] as $region)
		{
			$updated = self::check($region['GeoRegionId'], $region['ParentId'],
				$region['GeoRegionName'], $region['GeoRegionType']);
			if ($updated)
				$cache = true;
		}

		if ($cache)
			self::getAll(true);
	}

}

