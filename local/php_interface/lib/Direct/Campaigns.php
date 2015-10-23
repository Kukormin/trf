<?
namespace Local\Direct;

use Local\ExtCache;
use Local\Utils;

/**
 * Кампании Директа
 */
class Campaigns
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Direct/Campaigns/';

	/**
	 * Возвращает все кампании клиента
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_campaigns'),
			    'PROPERTY_Login' => $clientLogin,
			), false, false, array(
				'ID', 'NAME',
			    'PROPERTY_CampaignID',
			    'PROPERTY_Login',
			    'PROPERTY_AgencyName',
			    'PROPERTY_IsActive',
			    'PROPERTY_Status',
			    'PROPERTY_StatusArchive',
			    'PROPERTY_StatusShow',
			    'PROPERTY_StatusModerate',
			    'PROPERTY_StatusActivating',
			    'PROPERTY_StartDate',
			    'PROPERTY_Sum',
			    'PROPERTY_SumAvailableForTransfer',
			    'PROPERTY_Clicks',
			    'PROPERTY_Shows',
			    'PROPERTY_ManagerName',
			));
			while ($item = $rsItems->Fetch())
			{
				$return['ITEMS'][$item['ID']] = array(
					'ID' => $item['ID'],
					'NAME' => $item['NAME'],
					'CampaignID' => intval($item['PROPERTY_CAMPAIGNID_VALUE']),
					'Login' => $item['PROPERTY_LOGIN_VALUE'],
					'AgencyName' => $item['PROPERTY_AGENCYNAME_VALUE'],
					'IsActive' => $item['PROPERTY_ISACTIVE_VALUE'] == 1,
					'Status' => $item['PROPERTY_STATUS_VALUE'],
					'StatusArchive' => $item['PROPERTY_STATUSARCHIVE_VALUE'],
					'StatusShow' => $item['PROPERTY_STATUSSHOW_VALUE'] == 1,
					'StatusModerate' => $item['PROPERTY_STATUSMODERATE_VALUE'],
					'StatusActivating' => $item['PROPERTY_STATUSACTIVATING_VALUE'] == 1,
					'StartDate' => Utils::formatDateFull($item['PROPERTY_STARTDATE_VALUE']),
					'Sum' => floatval($item['PROPERTY_SUM_VALUE']),
					'SumAvailableForTransfer' => floatval($item['PROPERTY_SUMAVAILABLEFORTRANSFER_VALUE']),
					'Clicks' => intval($item['PROPERTY_CLICKS_VALUE']),
					'Shows' => intval($item['PROPERTY_SHOWS_VALUE']),
					'ManagerName' => $item['PROPERTY_MANAGERNAME_VALUE'],
				);
				$return['DIRECT'][$item['PROPERTY_CAMPAIGNID_VALUE']] = $item['ID'];
				$return['IDS'][] = $item['PROPERTY_CAMPAIGNID_VALUE'];
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает кампанию по ID
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
	 * Возвращает кампанию по ID в Директе
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
	 * Добавляет или обновляет кампанию
	 * @param $clientLogin
	 * @param $source
	 * @return bool
	 */
	public static function check($clientLogin, $source)
	{
		$campaign = self::getByDirectId($clientLogin, $source['CampaignID']);
		if ($campaign)
			$updated = self::update($campaign, $source);
		else
			$updated = self::add($source);

		return $updated;
	}

	/**
	 * Добавляет кампании на основнии данных из API
	 * @param $source
	 * @return bool
	 */
	public static function add($source)
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_campaigns'),
			'NAME' => $source['Name'],
			'PROPERTY_VALUES' => array(
				'CampaignID' => $source['CampaignID'],
				'Login' => $source['Login'],
				'AgencyName' => $source['AgencyName'],
				'IsActive' => $source['IsActive'] == 'Yes' ? 1 : 0,
				'Status' => $source['Status'],
				'StatusArchive' => $source['StatusArchive'],
				'StatusShow' => $source['StatusShow'] == 'Yes' ? 1 : 0,
				'StatusModerate' => $source['StatusModerate'],
				'StatusActivating' => $source['StatusActivating'] == 'Yes' ? 1 : 0,
				'StartDate' => Common::convertDate($source['StartDate']),
				'Sum' => $source['Sum'],
				'SumAvailableForTransfer' => $source['SumAvailableForTransfer'],
				'Clicks' => $source['Clicks'],
				'Shows' => $source['Shows'],
				'ManagerName' => $source['ManagerName'],
			),
		));
		return true;
	}

	/**
	 * Обновляет свойства кампании, если они изменились
	 * @param $campaign
	 * @param $source
	 * @return bool
	 */
	public static function update($campaign, $source)
	{
		$updated = false;

		$iblockElement = new \CIBlockElement();
		if ($campaign['NAME'] != $source['Name'])
		{
			$iblockElement->Update($campaign['ID'], array(
				'NAME' => $source['Name'],
			));
			$updated = true;
		}

		$propValues = array(
			'CampaignID' => $source['CampaignID'],
			'Login' => $source['Login'],
			'AgencyName' => $source['AgencyName'],
			'IsActive' => $source['IsActive'] == 'Yes' ? 1 : 0,
			'Status' => $source['Status'],
			'StatusArchive' => $source['StatusArchive'],
			'StatusShow' => $source['StatusShow'] == 'Yes' ? 1 : 0,
			'StatusModerate' => $source['StatusModerate'],
			'StatusActivating' => $source['StatusActivating'] == 'Yes' ? 1 : 0,
			'StartDate' => Common::convertDate($source['StartDate']),
			'Sum' => $source['Sum'],
			'SumAvailableForTransfer' => $source['SumAvailableForTransfer'],
			'Clicks' => $source['Clicks'],
			'Shows' => $source['Shows'],
			'ManagerName' => $source['ManagerName'],
		);

		$update = array();
		foreach ($propValues as $k => $v)
		{
			if ($campaign[$k] != $v)
				$update[$k] = $v;
		}
		if ($update)
		{
			$iblockElement->SetPropertyValuesEx($campaign['ID'], Utils::getIBlockIdByCode('y_campaigns'), $update);
			$updated = true;
		}

		return $updated;
	}

}
