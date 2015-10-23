<?
namespace Local\Direct;

use Local\ExtCache;
use Local\Utils;

/**
 * Группы объявлений Директа
 */
class AdGroups
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Direct/AdGroups/';

	/**
	 * ID свойства "Статус"
	 */
	const STATUS_PROP_ID = 37;

	/**
	 * Возвращает все группы объявлений клиента
	 * @param string $clientLogin
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getByClient($clientLogin, $refreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$clientLogin,
			),
			static::CACHE_PATH . __FUNCTION__ . '/'
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$iblockElement = new \CIBlockElement();
			$rsItems = $iblockElement->GetList(array(), array(
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_groups'),
			    'PROPERTY_Login' => $clientLogin,
			), false, false, array(
				'ID', 'NAME',
			    'PROPERTY_Id',
			    'PROPERTY_CampaignId',
			    'PROPERTY_Status',
			    'PROPERTY_RegionIds',
			    'PROPERTY_NegativeKeywords',
			));
			while ($item = $rsItems->Fetch())
			{
				$return['ITEMS'][$item['ID']] = array(
					'_ID' => $item['ID'],
					'NAME' => $item['NAME'],
					'Id' => intval($item['PROPERTY_ID_VALUE']),
					'CampaignId' => intval($item['PROPERTY_CAMPAIGNID_VALUE']),
					'Status' => $item['PROPERTY_STATUS_ENUM_ID'],
					'StatusName' => $item['PROPERTY_STATUS_VALUE'],
					'RegionIds' => $item['PROPERTY_REGIONIDS_VALUE']['TEXT'],
					'RegionIdsArray' => unserialize($item['PROPERTY_REGIONIDS_VALUE']['TEXT']),
					'NegativeKeywords' => $item['PROPERTY_NEGATIVEKEYWORDS_VALUE']['TEXT'],
					'NegativeKeywordsArray' => $item['PROPERTY_NEGATIVEKEYWORDS_VALUE']['TEXT'] ?
						unserialize($item['PROPERTY_NEGATIVEKEYWORDS_VALUE']['TEXT']) : false,
				);
				$return['DIRECT'][$item['PROPERTY_ID_VALUE']] = $item['ID'];
				$return['IDS'][] = $item['PROPERTY_ID_VALUE'];
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает группу объявлений по ID
	 * @param $clientLogin
	 * @param $id
	 * @return mixed
	 */
	public static function getById($clientLogin, $id)
	{
		$all = self::getByClient($clientLogin);
		return $all['ITEMS'][$id];
	}

	/**
	 * Возвращает группу объявлений по ID в Директе
	 * @param $clientLogin
	 * @param $directId
	 * @return mixed
	 */
	public static function getByDirectId($clientLogin, $directId)
	{
		$all = self::getByClient($clientLogin);
		$id = $all['DIRECT'][$directId];
		return $all['ITEMS'][$id];
	}

	/**
	 * Добавляет или обновляет группу объявлений
	 * @param $clientLogin
	 * @param $source
	 * @return bool
	 */
	public static function check($clientLogin, $source)
	{
		$group = self::getByDirectId($clientLogin, $source['Id']);
		if ($group)
			$updated = self::update($group, $source);
		else
			$updated = self::add($clientLogin, $source);

		return $updated;
	}

	/**
	 * Добавляет группу объявлений на основнии данных из API
	 * @param $clientLogin
	 * @param $source
	 * @return bool
	 */
	public static function add($clientLogin, $source)
	{
		$regionIds = array(
			'VALUE' => array(
				'TEXT' => serialize($source['RegionIds']),
				'TYPE' => 'text',
			),
		);
		$negativeKeywords = false;
		if (isset($source['NegativeKeywords']['Items']))
			$negativeKeywords = array(
				'VALUE' => array(
					'TEXT' => serialize($source['NegativeKeywords']['Items']),
					'TYPE' => 'text',
				),
			);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);

		$iblockElement = new \CIBlockElement();
		$iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_groups'),
			'NAME' => $source['Name'],
			'PROPERTY_VALUES' => array(
				'Id' => $source['Id'],
				'CampaignId' => $source['CampaignId'],
				'Login' => $clientLogin,
				'Status' => $status,
				'RegionIds' => $regionIds,
				'NegativeKeywords' => $negativeKeywords,
			),
		));
		return true;
	}

	/**
	 * Обновляет свойства группы объявлений, если они изменились
	 * @param $adGroup
	 * @param $source
	 * @return bool
	 */
	public static function update($adGroup, $source)
	{
		$updated = false;

		$iblockElement = new \CIBlockElement();

		if ($adGroup['NAME'] != $source['Name'])
		{
			$iblockElement->Update($adGroup['ID'], array(
				'NAME' => $source['Name'],
			));
			$updated = true;
		}

		$regionIds = array(
			'VALUE' => array(
				'TEXT' => serialize($source['RegionIds']),
				'TYPE' => 'text',
			),
		);
		$negativeKeywords = false;
		if (isset($source['NegativeKeywords']['Items']))
			$negativeKeywords = array(
				'VALUE' => array(
					'TEXT' => serialize($source['NegativeKeywords']['Items']),
					'TYPE' => 'text',
				),
			);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);

		$propValues = array(
			'CampaignId' => $source['CampaignId'],
			'Status' => $status,
			'RegionIds' => $regionIds,
			'NegativeKeywords' => $negativeKeywords,
		);

		$update = array();
		foreach ($propValues as $k => $v)
		{
			if ($k == 'RegionIds')
			{
				if ($adGroup[$k] != $v['VALUE']['TEXT'])
					$update[$k] = $v;
			}
			elseif ($k == 'NegativeKeywords')
			{
				if ($adGroup[$k] === false && $v === false)
					continue;

				if ($adGroup[$k] != $v['VALUE']['TEXT'])
					$update[$k] = $v;
			}
			elseif ($adGroup[$k] != $v)
				$update[$k] = $v;
		}
		if ($update)
		{
			$iblockElement->SetPropertyValuesEx($adGroup['_ID'], Utils::getIBlockIdByCode('y_groups'), $update);
			$updated = true;
		}

		return $updated;
	}

}
