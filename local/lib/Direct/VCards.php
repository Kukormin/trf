<?
namespace Local\Direct;

use Local\Api\ApiException;
use Local\ExtCache;
use Local\Utils;

/**
 * Виртуальные визитки Директа
 */
class VCards
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Direct/VCards/';

	/**
	 * Возвращает визитки кампании
	 * @param $campaignId
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getByCampaign($campaignId, $refreshCache = false)
	{
		$campaignId = intval($campaignId);
		if (!$campaignId)
			return false;

		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$campaignId,
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_vcards'),
				'PROPERTY_CampaignId' => $campaignId,
			), false, false, array(
				'ID', 'DETAIL_TEXT',
				'PROPERTY_Id',
				'PROPERTY_CampaignId',
			));
			while ($item = $rsItems->Fetch())
			{
				$return['ITEMS'][$item['ID']] = array(
					'ID' => $item['ID'],
					'VCardId' => intval($item['PROPERTY_ID_VALUE']),
					'CampaignId' => intval($item['PROPERTY_CAMPAIGNID_VALUE']),
					'SOURCE' => json_decode($item['DETAIL_TEXT'], true),
				);
				$return['DIRECT'][$item['PROPERTY_ID_VALUE']] = $item['ID'];
				$return['IDS'][] = $item['PROPERTY_ID_VALUE'];
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает визитку по ID
	 * @param $campaignId
	 * @param $id
	 * @return mixed
	 */
	public static function getById($campaignId, $id)
	{
		$all = self::getByCampaign($campaignId);
		return $all['ITEMS'][$id];
	}

	/**
	 * Возвращает визитку по ID в Директе
	 * @param $campaignId
	 * @param $directId
	 * @return mixed
	 */
	public static function getByDirectId($campaignId, $directId)
	{
		$all = self::getByCampaign($campaignId);
		$id = $all['DIRECT'][$directId];
		return $all['ITEMS'][$id];
	}

	/**
	 * Добавляет визитку
	 * @param $campaignId
	 * @param $source
	 * @param $result
	 * @return bool
	 * @throws ApiException
	 */
	public static function check($campaignId, $source, &$result)
	{
		$ad = self::getByDirectId($campaignId, $source['Id']);
		if (!$ad)
		{
			self::add($source);
			$result['add']++;
			return true;
		}
		else
		{
			$result['same']++;
			return false;
		}
	}

	/**
	 * Добавляет визитку на основнии данных из API
	 * @param $source
	 * @return bool
	 * @throws ApiException
	 */
	public static function add($source)
	{
		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_vcards'),
			'NAME' => $source['Id'],
			'DETAIL_TEXT' => json_encode($source, JSON_UNESCAPED_UNICODE),
			'PROPERTY_VALUES' => array(
				'Id' => $source['Id'],
				'CampaignId' => $source['CampaignId'],
			),
		));
		if (!$id)
			throw new ApiException('vcard_add_error', 500, $iblockElement->LAST_ERROR);

		return $id;
	}

}
