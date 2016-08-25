<?
namespace Local\Direct;

use Local\Api\ApiException;
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
	 * Возвращает объявление по ID в Директе
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_ads'),
				'=XML_ID' => $directId,
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
			if ($item = $rsItems->Fetch())
			{
				$return = array(
					'ID' => $item['ID'],
					'AdId' => intval($item['PROPERTY_ID_VALUE']),
					'AdGroupId' => intval($item['PROPERTY_ADGROUPID_VALUE']),
					'CampaignId' => intval($item['PROPERTY_CAMPAIGNID_VALUE']),
					'Status' => $item['PROPERTY_STATUS_ENUM_ID'],
					'StatusName' => $item['PROPERTY_STATUS_VALUE'],
					'StatusClarification' => $item['PROPERTY_STATUSCLARIFICATION_VALUE'],
					'State' => $item['PROPERTY_STATE_ENUM_ID'],
					'StateName' => $item['PROPERTY_STATE_VALUE'],
					'AdCategories' => $item['PROPERTY_ADCATEGORIES_VALUE'],
					'AdCategoriesArray' => json_decode($item['PROPERTY_ADCATEGORIES_VALUE'], true),
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
			}
			else
				$extCache->abortDataCache();

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Добавляет или обновляет объявление
	 * @param $source
	 * @param $result
	 * @throws ApiException
	 */
	public static function check($source, &$result)
	{
		$ad = self::getByDirectId($source['Id']);
		if ($ad)
		{
			$res = self::update($ad, $source);
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
	 * Добавляет объявление на основнии данных из API
	 * @param $source
	 * @return bool
	 * @throws ApiException
	 */
	public static function add($source)
	{
		$categories = '';
		if (isset($source['AdCategories']['Items']))
			$categories = json_encode($source['AdCategories']['Items']);
		$status = Utils::getPropertyIdByXml($source['Status'], self::STATUS_PROP_ID);
		$state = Utils::getPropertyIdByXml($source['State'], self::STATE_PROP_ID);
		$href = array(
			'VALUE' => array(
				'TEXT' => $source['TextAd']['Href'],
				'TYPE' => 'text',
			),
		);
		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_ads'),
			'NAME' => $source['Id'] . ' - ' . $source['CampaignId'],
			'XML_ID' => $source['Id'],
			'PROPERTY_VALUES' => array(
				'Id' => $source['Id'],
				'AdGroupId' => $source['AdGroupId'],
				'CampaignId' => $source['CampaignId'],
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
		if (!$id)
			throw new ApiException('ad_add_error', 500, $iblockElement->LAST_ERROR);

		return $id;
	}

	/**
	 * Обновляет свойства объявления, если они изменились
	 * @param $ad
	 * @param $source
	 * @return bool
	 */
	public static function update($ad, $source)
	{
		$updated = false;

		$categories = '';
		if (isset($source['AdCategories']['Items']))
			$categories = json_encode($source['AdCategories']['Items']);
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
				if ($ad[$k] != $v['VALUE']['TEXT'])
					$update[$k] = $v;
			}
			elseif ($ad[$k] != $v)
				$update[$k] = $v;
		}
		if ($update)
		{
			$iblockElement = new \CIBlockElement();
			$iblockElement->SetPropertyValuesEx($ad['ID'], Utils::getIBlockIdByCode('y_ads'), $update);
			$updated = true;
		}

		if ($updated)
			self::getByDirectId($ad['AdId'], true);

		return $updated;
	}

}
