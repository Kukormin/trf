<?
namespace Local\Direct;

use Local\ExtCache;
use Local\Utils;

/**
 * Объявления Директа
 */
class Ads
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Direct/Ads/';

	/**
	 * ID свойства "Статус"
	 */
	const STATUS_PROP_ID = 42;

	/**
	 * ID свойства "Состояние"
	 */
	const STATE_PROP_ID = 44;

	/**
	 * ID справочника со статусами "Extensionmoderation"
	 */
	const EXTENSIONMODERATION_HL_ID = 1;

	/**
	 * Возвращает объявления клиента
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_ads'),
			    'PROPERTY_Login' => $clientLogin,
			), false, false, array(
				'ID',
			    'PROPERTY_Id',
			    'PROPERTY_AdGroupId',
			    'PROPERTY_CampaignId',
			    'PROPERTY_Status',
			    'PROPERTY_StatusClarification',
			    'PROPERTY_State',
			    'PROPERTY_AdCategories',
			    'PROPERTY_AgeLabel',
			    'PROPERTY_Type',
			    'PROPERTY_Title',
			    'PROPERTY_Text',
			    'PROPERTY_Href',
			    'PROPERTY_DisplayDomain',
			    'PROPERTY_Mobile',
			    'PROPERTY_VCardId',
			    'PROPERTY_SitelinkSetId',
			    'PROPERTY_AdImageHash',
			    'PROPERTY_VCardModeration',
			    'PROPERTY_VCardModerationClarification',
			    'PROPERTY_SitelinksModeration',
			    'PROPERTY_SitelinksModerationClarification',
			    'PROPERTY_AdImageModeration',
			    'PROPERTY_AdImageModerationClarification',
			));
			while ($item = $rsItems->Fetch())
			{
				$return['ITEMS'][$item['ID']] = array(
					'_ID' => $item['ID'],
					'Id' => intval($item['PROPERTY_ID_VALUE']),
					'AdGroupId' => intval($item['PROPERTY_ADGROUPID_VALUE']),
					'CampaignId' => intval($item['PROPERTY_CAMPAIGNID_VALUE']),
					'Status' => $item['PROPERTY_STATUS_ENUM_ID'],
					'StatusName' => $item['PROPERTY_STATUS_VALUE'],
					'StatusClarification' => $item['PROPERTY_STATUSCLARIFICATION_VALUE'],
					'State' => $item['PROPERTY_STATE_ENUM_ID'],
					'StateName' => $item['PROPERTY_STATE_VALUE'],
					'AdCategories' => $item['PROPERTY_ADCATEGORIES_VALUE'] ?
						unserialize($item['PROPERTY_ADCATEGORIES_VALUE']) : '',
					'AgeLabel' => $item['PROPERTY_AGELABEL_VALUE'],
					'Type' => $item['PROPERTY_TYPE_VALUE'],
					'Title' => $item['PROPERTY_TITLE_VALUE'],
					'Text' => $item['PROPERTY_TEXT_VALUE'],
					'Href' => $item['PROPERTY_HREF_VALUE']['TEXT'],
					'DisplayDomain' => $item['PROPERTY_DISPLAYDOMAIN_VALUE'],
					'Mobile' => $item['PROPERTY_MOBILE_VALUE'] == 1,
					'VCardId' => intval($item['PROPERTY_VCARDID_VALUE']),
					'SitelinkSetId' => intval($item['PROPERTY_SITELINKSETID_VALUE']),
					'AdImageHash' => $item['PROPERTY_ADIMAGEHASH_VALUE'],
					'VCardModeration' => $item['PROPERTY_VCARDMODERATION_VALUE'],
					'VCardModerationClarification' => $item['PROPERTY_VCARDMODERATIONCLARIFICATION_VALUE'],
					'SitelinksModeration' => $item['PROPERTY_SITELINKSMODERATION_VALUE'],
					'SitelinksModerationClarification' => $item['PROPERTY_SITELINKSMODERATIONCLARIFICATION_VALUE'],
					'AdImageModeration' => $item['PROPERTY_ADIMAGEMODERATION_VALUE'],
					'AdImageModerationClarification' => $item['PROPERTY_ADIMAGEMODERATIONCLARIFICATION_VALUE'],
				);
				$return['DIRECT'][$item['PROPERTY_ID_VALUE']] = $item['ID'];
				$return['IDS'][] = $item['PROPERTY_ID_VALUE'];
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает объявление по ID
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
	 * Возвращает объявление по ID в Директе
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
	 * Добавляет или обновляет объявление
	 * @param $clientLogin
	 * @param $source
	 * @return bool
	 */
	public static function check($clientLogin, $source)
	{
		$ad = self::getByDirectId($clientLogin, $source['Id']);
		if ($ad)
			$updated = self::update($ad, $source);
		else
			$updated = self::add($clientLogin, $source);

		return $updated;
	}

	/**
	 * Добавляет объявление на основнии данных из API
	 * @param $clientLogin
	 * @param $source
	 * @return bool
	 */
	public static function add($clientLogin, $source)
	{
		$categories = '';
		if (isset($source['AdCategories']['Items']))
			$categories = serialize($source['AdCategories']['Items']);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$state = Utils::getPropertyIdByXml($source['State'], self::STATE_PROP_ID);
		$href = array(
			'VALUE' => array(
				'TEXT' => $source['TextAd']['Href'],
				'TYPE' => 'text',
			),
		);
		$iblockElement = new \CIBlockElement();
		$iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_ads'),
			'NAME' => $source['Id'] . ' - ' . $source['CampaignId'],
			'PROPERTY_VALUES' => array(
				'Id' => $source['Id'],
				'AdGroupId' => $source['AdGroupId'],
				'CampaignId' => $source['CampaignId'],
				'Login' => $clientLogin,
				'Status' => $status,
				'StatusClarification' => $source['StatusClarification'],
				'State' => $state,
				'AdCategories' => $categories,
				'AgeLabel' => $source['AgeLabel'],
				'Type' => $source['Type'],
				'Title' => $source['TextAd']['Title'],
				'Text' => $source['TextAd']['Text'],
				'Href' => $href,
				'DisplayDomain' => $source['TextAd']['DisplayDomain'],
				'Mobile' => $source['TextAd']['Mobile'] == 'YES' ? 1 : 0,
				'VCardId' => $source['TextAd']['VCardId'],
				'SitelinkSetId' => $source['TextAd']['SitelinkSetId'],
				'AdImageHash' => $source['TextAd']['AdImageHash'],
				'VCardModeration' => strtolower($source['TextAd']['VCardModeration']['Status']),
			    'VCardModerationClarification' => $source['TextAd']['VCardModeration']['StatusClarification'],
			    'SitelinksModeration' => strtolower($source['TextAd']['SitelinksModeration']['Status']),
			    'SitelinksModerationClarification' => $source['TextAd']['SitelinksModeration']['StatusClarification'],
			    'AdImageModeration' => strtolower($source['TextAd']['AdImageModeration']['Status']),
			    'AdImageModerationClarification' => $source['TextAd']['AdImageModeration']['StatusClarification'],
			),
		));
		return true;
	}

	/**
	 * Обновляет свойства объявления, если они изменились
	 * @param $adGroup
	 * @param $source
	 * @return bool
	 */
	public static function update($adGroup, $source)
	{
		$updated = false;

		$categories = '';
		if (isset($source['AdCategories']['Items']))
			$categories = serialize($source['AdCategories']['Items']);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$state = Utils::getPropertyIdByXml($source['State'], self::STATE_PROP_ID);
		$href = array(
			'VALUE' => array(
				'TEXT' => $source['TextAd']['Href'],
				'TYPE' => 'text',
			),
		);

		$propValues = array(
			'Status' => $status,
			'StatusClarification' => $source['StatusClarification'],
			'State' => $state,
			'AdCategories' => $categories,
			'AgeLabel' => $source['AgeLabel'],
			'Type' => $source['Type'],
			'Title' => $source['TextAd']['Title'],
			'Text' => $source['TextAd']['Text'],
			'Href' => $href,
			'DisplayDomain' => $source['TextAd']['DisplayDomain'],
			'Mobile' => $source['TextAd']['Mobile'] == 'YES' ? 1 : 0,
			'VCardId' => $source['TextAd']['VCardId'],
			'SitelinkSetId' => $source['TextAd']['SitelinkSetId'],
			'AdImageHash' => $source['TextAd']['AdImageHash'],
			'VCardModeration' => strtolower($source['TextAd']['VCardModeration']['Status']),
			'VCardModerationClarification' => $source['TextAd']['VCardModeration']['StatusClarification'],
			'SitelinksModeration' => strtolower($source['TextAd']['SitelinksModeration']['Status']),
			'SitelinksModerationClarification' => $source['TextAd']['SitelinksModeration']['StatusClarification'],
			'AdImageModeration' => strtolower($source['TextAd']['AdImageModeration']['Status']),
			'AdImageModerationClarification' => $source['TextAd']['AdImageModeration']['StatusClarification'],
		);

		$update = array();
		foreach ($propValues as $k => $v)
		{
			if ($k == 'Href')
			{
				if ($adGroup[$k] != $v['VALUE']['TEXT'])
					$update[$k] = $v;
			}
			elseif ($adGroup[$k] != $v)
				$update[$k] = $v;
		}
		if ($update)
		{
			$iblockElement = new \CIBlockElement();
			$iblockElement->SetPropertyValuesEx($adGroup['_ID'], Utils::getIBlockIdByCode('y_ads'), $update);
			$updated = true;
		}

		return $updated;
	}

}
