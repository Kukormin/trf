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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_campaigns'),
			    'PROPERTY_Login' => $clientLogin,
			), false, false, array(
				'ID', 'NAME', 'ACTIVE', 'DETAIL_TEXT',
			    'PROPERTY_CampaignID',
			    'PROPERTY_SYNC',
			    'PROPERTY_Login',
			));
			while ($item = $rsItems->Fetch())
			{
				$directId = intval($item['PROPERTY_CAMPAIGNID_VALUE']);
				$return['ITEMS'][$item['ID']] = array(
					'ID' => $item['ID'],
					'NAME' => $item['NAME'],
					'ACTIVE' => $item['ACTIVE'],
					'CampaignID' => $directId,
					'Login' => $item['PROPERTY_LOGIN_VALUE'],
					'SYNC' => $item['PROPERTY_SYNC_VALUE'],
				    'SOURCE' => json_decode($item['DETAIL_TEXT'], true),
				);
				$return['DIRECT'][$directId] = $item['ID'];
				$return['IDS'][] = $directId;
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
	 * @param $source
	 * @param $result
	 */
	public static function check($source, &$result)
	{
		$campaign = self::getByDirectId($source['Login'], $source['Id']);
		if ($campaign)
		{
			$res = self::update($campaign, $source);
			if ($res)
				$result['update']++;
			else
				$result['same']++;
		}
	}

	/**
	 * Добавляет кампанию привязанную к клиенту на основании данных $source
	 * @param $client
	 * @param $source
	 * @return mixed
	 */
	public static function add($client, $source)
	{
		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_campaigns'),
			'NAME' => $source['Name'],
			'ACTIVE' => 'N',
			'DETAIL_TEXT' => '{}',
			'PROPERTY_VALUES' => array(
				'CampaignID' => $source['Id'],
				'Login' => $client['NAME'],
				'SYNC' => '1970-01-01T00:00:00Z',
			),
		));

		$campaign = self::getById($client['NAME'], $id, true);
		return $campaign;
	}

	/**
	 * Обновляет свойства кампании, если они изменились
	 * @param $campaign
	 * @param $source
	 * @return bool
	 */
	public static function update($campaign, $source)
	{
		$iblockElement = new \CIBlockElement();
		$update = array();
		if ($campaign['ACTIVE'] != 'Y')
			$update['ACTIVE'] = 'Y';
		if ($campaign['NAME'] != $source['Name'])
			$update['NAME'] = $source['Name'];
		if ($campaign['SOURCE'] != $source)
			$update['DETAIL_TEXT'] = json_encode($source, JSON_UNESCAPED_UNICODE);

		if ($update)
		{
			$iblockElement->Update($campaign['ID'], $update);
			self::getByClient($campaign['Login'], true);
			return true;
		}

		return false;
	}

	/**
	 * Обновляет время синхронизации кампании
	 * @param $campaign
	 * @param $dtSync
	 * @return bool
	 */
	public static function updateSync($campaign, $dtSync)
	{
		if ($dtSync != $campaign['SYNC'])
		{
			$iblockElement = new \CIBlockElement();
			$iblockElement->SetPropertyValuesEx($campaign['ID'], Utils::getIBlockIdByCode('y_campaigns'), array(
				'SYNC' => $dtSync,
			));
			self::getByClient($campaign['Login'], true);
			return true;
		}
		else
			return false;
	}

}
