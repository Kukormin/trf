<?
namespace Local\Direct;

use Local\ExtCache;
use Local\Utils;

/**
 * Ключевые фразы Директа
 */
class Keywords
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Direct/Keywords/';

	/**
	 * Для получения рублей
	 */
	const KF = 100000;

	/**
	 * ID свойства "Приоритет"
	 */
	const PRIORITY_PROP_ID = 71;

	/**
	 * ID свойства "Статус"
	 */
	const STATUS_PROP_ID = 72;

	/**
	 * ID свойства "Состояние"
	 */
	const STATE_PROP_ID = 73;

	/**
	 * Возвращает ключевые фразы клиента
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_keywords'),
			    'PROPERTY_Login' => $clientLogin,
			), false, false, array(
				'ID', 'NAME',
			    'PROPERTY_Id',
			    'PROPERTY_AdGroupId',
			    'PROPERTY_CampaignId',
			    'PROPERTY_UserParam1',
			    'PROPERTY_UserParam2',
			    'PROPERTY_Bid',
			    'PROPERTY_ContextBid',
			    'PROPERTY_StrategyPriority',
			    'PROPERTY_Status',
			    'PROPERTY_State',
			));
			while ($item = $rsItems->Fetch())
			{
				$return['ITEMS'][$item['ID']] = array(
					'_ID' => $item['ID'],
					'NAME' => $item['NAME'],
					'Id' => intval($item['PROPERTY_ID_VALUE']),
					'AdGroupId' => intval($item['PROPERTY_ADGROUPID_VALUE']),
					'CampaignId' => intval($item['PROPERTY_CAMPAIGNID_VALUE']),
					'UserParam1' => $item['PROPERTY_USERPARAM1_VALUE'],
					'UserParam2' => $item['PROPERTY_USERPARAM2_VALUE'],
					'Bid' => intval($item['PROPERTY_BID_VALUE']),
					'BidFormatted' => number_format(intval($item['PROPERTY_BID_VALUE']) / self::KF, 2, '.', ' '),
					'ContextBid' => intval($item['PROPERTY_CONTEXTBID_VALUE']),
					'ContextBidFormatted' => number_format(intval($item['PROPERTY_CONTEXTBID_VALUE']) / self::KF,
						2, '.', ' '),
					'StrategyPriority' => $item['PROPERTY_STRATEGYPRIORITY_ENUM_ID'],
					'Status' => $item['PROPERTY_STATUS_ENUM_ID'],
					'StatusName' => $item['PROPERTY_STATUS_VALUE'],
					'State' => $item['PROPERTY_STATE_ENUM_ID'],
					'StateName' => $item['PROPERTY_STATE_VALUE'],
				);
				$return['DIRECT'][$item['PROPERTY_ID_VALUE']] = $item['ID'];
				$return['IDS'][] = $item['PROPERTY_ID_VALUE'];
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает ключевые фразы группы
	 * @param int $groupId
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getByGroup($groupId, $refreshCache = false)
	{
		$groupId = intval($groupId);
		if (!$groupId)
			return false;
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$groupId,
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_keywords'),
				'PROPERTY_AdGroupId' => $groupId,
			), false, false, array(
				'ID', 'NAME',
				'PROPERTY_Id',
				'PROPERTY_AdGroupId',
				'PROPERTY_CampaignId',
				'PROPERTY_UserParam1',
				'PROPERTY_UserParam2',
				'PROPERTY_Bid',
				'PROPERTY_ContextBid',
				'PROPERTY_StrategyPriority',
				'PROPERTY_Status',
				'PROPERTY_State',
			));
			while ($item = $rsItems->Fetch())
			{
				$return['ITEMS'][$item['ID']] = array(
					'_ID' => $item['ID'],
					'NAME' => $item['NAME'],
					'Id' => intval($item['PROPERTY_ID_VALUE']),
					'AdGroupId' => intval($item['PROPERTY_ADGROUPID_VALUE']),
					'CampaignId' => intval($item['PROPERTY_CAMPAIGNID_VALUE']),
					'UserParam1' => $item['PROPERTY_USERPARAM1_VALUE'],
					'UserParam2' => $item['PROPERTY_USERPARAM2_VALUE'],
					'Bid' => intval($item['PROPERTY_BID_VALUE']),
					'BidFormatted' => number_format(intval($item['PROPERTY_BID_VALUE']) / self::KF, 2, '.', ' '),
					'ContextBid' => intval($item['PROPERTY_CONTEXTBID_VALUE']),
					'ContextBidFormatted' => number_format(intval($item['PROPERTY_CONTEXTBID_VALUE']) / self::KF,
						2, '.', ' '),
					'StrategyPriority' => $item['PROPERTY_STRATEGYPRIORITY_ENUM_ID'],
					'Status' => $item['PROPERTY_STATUS_ENUM_ID'],
					'StatusName' => $item['PROPERTY_STATUS_VALUE'],
					'State' => $item['PROPERTY_STATE_ENUM_ID'],
					'StateName' => $item['PROPERTY_STATE_VALUE'],
				);
				$return['DIRECT'][$item['PROPERTY_ID_VALUE']] = $item['ID'];
				$return['IDS'][] = $item['PROPERTY_ID_VALUE'];
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает ключевую фразу по ID
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
	 * Возвращает ключевые фразы по ID в Директе
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
	 * Добавляет или обновляет ключевую фразу
	 * @param $clientLogin
	 * @param $source
	 * @return bool
	 */
	public static function check($clientLogin, $source)
	{
		$keyword = self::getByDirectId($clientLogin, $source['Id']);
		if ($keyword)
			$updated = self::update($keyword, $source);
		else
			$updated = self::add($clientLogin, $source);

		return $updated;
	}

	/**
	 * Добавляет ключевую фразу на основнии данных из API
	 * @param $clientLogin
	 * @param $source
	 * @return bool
	 */
	public static function add($clientLogin, $source)
	{
		$priority = Utils::getPropertyIdByXml($source['StrategyPriority'], self::PRIORITY_PROP_ID);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$state = Utils::getPropertyIdByXml($source['State'], self::STATE_PROP_ID);

		$iblockElement = new \CIBlockElement();
		$iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_keywords'),
			'NAME' => $source['Keyword'],
			'PROPERTY_VALUES' => array(
				'Id' => $source['Id'],
				'AdGroupId' => $source['AdGroupId'],
				'CampaignId' => $source['CampaignId'],
				'Login' => $clientLogin,
				'UserParam1' => $source['UserParam1'],
				'UserParam2' => $source['UserParam2'],
				'Bid' => $source['Bid'],
				'ContextBid' => $source['ContextBid'],
				'StrategyPriority' => $priority,
				'Status' => $status,
				'State' => $state,
			),
		));
		return true;
	}

	/**
	 * Обновляет свойства ключевой фразы, если они изменились
	 * @param $keyword
	 * @param $source
	 * @return bool
	 */
	public static function update($keyword, $source)
	{
		$updated = false;

		$iblockElement = new \CIBlockElement();
		if ($keyword['NAME'] != $source['Keyword'])
		{
			$iblockElement->Update($keyword['_ID'], array(
				'NAME' => $source['Keyword'],
			));
			$updated = true;
		}

		$priority = Utils::getPropertyIdByXml($source['StrategyPriority'], self::PRIORITY_PROP_ID);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$state = Utils::getPropertyIdByXml($source['State'], self::STATE_PROP_ID);

		$propValues = array(
			'AdGroupId' => $source['AdGroupId'],
			'CampaignId' => $source['CampaignId'],
			'UserParam1' => $source['UserParam1'],
			'UserParam2' => $source['UserParam2'],
			'Bid' => $source['Bid'],
			'ContextBid' => $source['ContextBid'],
			'StrategyPriority' => $priority,
			'Status' => $status,
			'State' => $state,
		);

		$update = array();
		foreach ($propValues as $k => $v)
		{
			if ($keyword[$k] != $v)
				$update[$k] = $v;
		}
		if ($update)
		{
			$iblockElement = new \CIBlockElement();
			$iblockElement->SetPropertyValuesEx($keyword['_ID'], Utils::getIBlockIdByCode('y_keywords'), $update);
			$updated = true;
		}

		return $updated;
	}

}
