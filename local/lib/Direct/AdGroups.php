<?
namespace Local\Direct;

use Local\Api\ApiException;
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
	 * ID свойства "Тип"
	 */
	const TYPE_PROP_ID = 108;

	/**
	 * Возвращает группу объявлений по Id в Директе
	 * @param $directId
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getByDirectId($directId, $refreshCache = false)
	{
		$directId = intval($directId);
		if (!$directId)
			return false;

		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$directId,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			864000,
			false
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$iblockElement = new \CIBlockElement();
			$rsItems = $iblockElement->GetList(array(), array(
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_groups'),
				'=XML_ID' => $directId,
			), false, false, array(
				'ID', 'NAME',
				'PROPERTY_Id',
				'PROPERTY_CampaignId',
				'PROPERTY_Status',
				'PROPERTY_Type',
				'PROPERTY_RegionIds',
				'PROPERTY_NegativeKeywords',
				'PROPERTY_TrackingParams',
			));
			if ($item = $rsItems->Fetch())
			{
				$return = array(
					'ID' => $item['ID'],
					'Name' => $item['NAME'],
					'GroupId' => intval($item['PROPERTY_ID_VALUE']),
					'CampaignId' => intval($item['PROPERTY_CAMPAIGNID_VALUE']),
					'Status' => $item['PROPERTY_STATUS_ENUM_ID'],
					'StatusName' => $item['PROPERTY_STATUS_VALUE'],
					'Type' => $item['PROPERTY_TYPE_ENUM_ID'],
					'TypeName' => $item['PROPERTY_TYPE_VALUE'],
					'TrackingParams' => $item['PROPERTY_TRACKINGPARAMS_VALUE'],
					'RegionIds' => $item['PROPERTY_REGIONIDS_VALUE']['TEXT'],
					'RegionIdsArray' => json_decode($item['PROPERTY_REGIONIDS_VALUE']['TEXT'], true),
					'NegativeKeywords' => $item['PROPERTY_NEGATIVEKEYWORDS_VALUE']['TEXT'],
					'NegativeKeywordsArray' => json_decode($item['PROPERTY_NEGATIVEKEYWORDS_VALUE']['TEXT'], true),
				);
			}
			else
				$extCache->abortDataCache();

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Добавляет или обновляет группу объявлений
	 * @param $source
	 * @param $result
	 * @throws ApiException
	 */
	public static function check($source, &$result)
	{
		$group = self::getByDirectId($source['Id']);
		if ($group)
		{
			$res = self::update($group, $source);
			if ($res)
				$result['update']++;
			else
				$result['same']++;
		}
		else
		{
			self::add($source);
			$result['add']++;
		}
	}

	/**
	 * Добавляет группу объявлений на основнии данных из API
	 * @param $source
	 * @return int
	 * @throws ApiException
	 */
	public static function add($source)
	{
		$regionIds = array(
			'VALUE' => array(
				'TEXT' => json_encode($source['RegionIds']),
				'TYPE' => 'text',
			),
		);
		$negativeKeywords = false;
		if (isset($source['NegativeKeywords']['Items']))
			$negativeKeywords = array(
				'VALUE' => array(
					'TEXT' => json_encode($source['NegativeKeywords']['Items'], JSON_UNESCAPED_UNICODE),
					'TYPE' => 'text',
				),
			);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$type = Utils::getPropertyIdByXml($source['Type'], self::TYPE_PROP_ID);

		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_groups'),
			'NAME' => $source['Name'],
			'XML_ID' => $source['Id'],
			'PROPERTY_VALUES' => array(
				'Id' => $source['Id'],
				'CampaignId' => $source['CampaignId'],
				'Status' => $status,
				'RegionIds' => $regionIds,
				'NegativeKeywords' => $negativeKeywords,
				'Type' => $type,
				'TrackingParams' => $source['TrackingParams'],
			),
		));
		if (!$id)
			throw new ApiException('group_add_error', 500, $iblockElement->LAST_ERROR);

		return $id;
	}

	/**
	 * Обновляет свойства группы объявлений, если они изменились
	 * @param $group
	 * @param $source
	 * @return bool
	 */
	public static function update($group, $source)
	{
		$updated = false;

		$iblockElement = new \CIBlockElement();

		if ($group['Name'] != $source['Name'])
		{
			$iblockElement->Update($group['ID'], array(
				'NAME' => $source['Name'],
			));
			$updated = true;
		}

		$regionIds = array(
			'VALUE' => array(
				'TEXT' => json_encode($source['RegionIds']),
				'TYPE' => 'text',
			),
		);
		$negativeKeywords = false;
		if (isset($source['NegativeKeywords']['Items']))
			$negativeKeywords = array(
				'VALUE' => array(
					'TEXT' => json_encode($source['NegativeKeywords']['Items'], JSON_UNESCAPED_UNICODE),
					'TYPE' => 'text',
				),
			);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$type = Utils::getPropertyIdByXml($source['Type'], self::TYPE_PROP_ID);

		$propValues = array(
			'CampaignId' => $source['CampaignId'],
			'Status' => $status,
			'RegionIds' => $regionIds,
			'NegativeKeywords' => $negativeKeywords,
			'Type' => $type,
			'TrackingParams' => $source['TrackingParams'],
		);

		$update = array();
		foreach ($propValues as $k => $v)
		{
			if ($k == 'RegionIds')
			{
				if ($group[$k] != $v['VALUE']['TEXT'])
					$update[$k] = $v;
			}
			elseif ($k == 'NegativeKeywords')
			{
				if ($group[$k] === false && $v === false)
					continue;

				if ($group[$k] != $v['VALUE']['TEXT'])
					$update[$k] = $v;
			}
			elseif ($group[$k] != $v)
				$update[$k] = $v;
		}
		if ($update)
		{
			$iblockElement->SetPropertyValuesEx($group['ID'], Utils::getIBlockIdByCode('y_groups'), $update);
			$updated = true;
		}

		if ($updated)
			self::getByDirectId($group['GroupId'], true);

		return $updated;
	}

}
