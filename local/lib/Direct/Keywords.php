<?
namespace Local\Direct;

use Local\Api\ApiException;
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
	 * Возвращает ключевую фразу по ID в Директе
	 * @param int $keywordId
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getByDirectId($keywordId, $refreshCache = false)
	{
		$keywordId = intval($keywordId);
		if (!$keywordId)
			return false;

		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$keywordId,
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_keywords'),
				'=XML_ID' => $keywordId,
			), false, false, array(
				'ID', 'PREVIEW_TEXT',
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
				'PROPERTY_Productivity',
				'PROPERTY_ProductivityReferences',
				'PROPERTY_StatisticsSearchClicks',
				'PROPERTY_StatisticsSearchImpressions',
				'PROPERTY_StatisticsNetworkClicks',
				'PROPERTY_StatisticsNetworkImpressions',
			));
			if ($item = $rsItems->Fetch())
			{
				$return = array(
					'ID' => $item['ID'],
					'Keyword' => $item['PREVIEW_TEXT'],
					'KeywordId' => intval($item['PROPERTY_ID_VALUE']),
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
					'Productivity' => floatval($item['PROPERTY_PRODUCTIVITY_VALUE']),
					'ProductivityReferences' => $item['PROPERTY_PRODUCTIVITYREFERENCES_VALUE'],
					'ProductivityReferencesArray' => json_decode($item['PROPERTY_PRODUCTIVITYREFERENCES_VALUE'], true),
					'StatisticsSearchClicks' => intval($item['PROPERTY_STATISTICSSEARCHCLICKS_VALUE']),
					'StatisticsSearchImpressions' => intval($item['PROPERTY_STATISTICSSEARCHIMPRESSIONS_VALUE']),
					'StatisticsNetworkClicks' => intval($item['PROPERTY_STATISTICSNETWORKCLICKS_VALUE']),
					'StatisticsNetworkImpressions' => intval($item['PROPERTY_STATISTICSNETWORKIMPRESSIONS_VALUE']),
				);
			}
			else
				$extCache->abortDataCache();

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Добавляет или обновляет ключевую фразу
	 * @param $source
	 */
	public static function check($source, &$result)
	{
		$keyword = self::getByDirectId($source['Id']);
		if ($keyword)
		{
			$res = self::update($keyword, $source);
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
	 * Добавляет ключевую фразу на основнии данных из API
	 * @param $source
	 * @return bool
	 * @throws ApiException
	 */
	public static function add($source)
	{
		$priority = Utils::getPropertyIdByXml($source['StrategyPriority'], self::PRIORITY_PROP_ID);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$state = Utils::getPropertyIdByXml($source['State'], self::STATE_PROP_ID);
		$pr = $source['Productivity']['References'] ? json_encode($source['Productivity']['References']) : '';
		$name = $source['Keyword'];
		if (strlen($name) > 250)
			$name = substr($name, 0, 250);

		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_keywords'),
			'NAME' => $name,
			'PREVIEW_TEXT' => $source['Keyword'],
			'XML_ID' => $source['Id'],
			'PROPERTY_VALUES' => array(
				'Id' => $source['Id'],
				'AdGroupId' => $source['AdGroupId'],
				'CampaignId' => $source['CampaignId'],
				'UserParam1' => $source['UserParam1'],
				'UserParam2' => $source['UserParam2'],
				'Bid' => $source['Bid'],
				'ContextBid' => $source['ContextBid'],
				'StrategyPriority' => $priority,
				'Status' => $status,
				'State' => $state,
				'Productivity' => floatval($source['Productivity']['Value']),
				'ProductivityReferences' => $pr,
				'StatisticsSearchClicks' => intval($source['StatisticsSearch']['Clicks']),
				'StatisticsSearchImpressions' => intval($source['StatisticsSearch']['Impressions']),
				'StatisticsNetworkClicks' => intval($source['StatisticsNetwork']['Clicks']),
				'StatisticsNetworkImpressions' => intval($source['StatisticsNetwork']['Impressions']),
			),
		));
		if (!$id)
			throw new ApiException('keyword_add_error', 500, $iblockElement->LAST_ERROR);

		return $id;
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

		$priority = Utils::getPropertyIdByXml($source['StrategyPriority'], self::PRIORITY_PROP_ID);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$state = Utils::getPropertyIdByXml($source['State'], self::STATE_PROP_ID);
		$pr = $source['Productivity']['References'] ? json_encode($source['Productivity']['References']) : '';

		$iblockElement = new \CIBlockElement();
		if ($keyword['Keyword'] != $source['Keyword'])
		{
			$name = $source['Keyword'];
			if (strlen($name) > 250)
				$name = substr($name, 0, 250);
			$iblockElement->Update($keyword['ID'], array(
				'NAME' => $name,
				'PREVIEW_TEXT' => $source['Keyword'],
			));
			$updated = true;
		}

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
			'Productivity' => floatval($source['Productivity']['Value']),
			'ProductivityReferences' => $pr,
			'StatisticsSearchClicks' => intval($source['StatisticsSearch']['Clicks']),
			'StatisticsSearchImpressions' => intval($source['StatisticsSearch']['Impressions']),
			'StatisticsNetworkClicks' => intval($source['StatisticsNetwork']['Clicks']),
			'StatisticsNetworkImpressions' => intval($source['StatisticsNetwork']['Impressions']),
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
			$iblockElement->SetPropertyValuesEx($keyword['ID'], Utils::getIBlockIdByCode('y_keywords'), $update);
			$updated = true;
		}

		if ($updated)
			self::getByDirectId($keyword['KeywordId'], true);

		return $updated;
	}

}
