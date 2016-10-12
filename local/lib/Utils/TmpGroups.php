<?
namespace Local\Utils;

use \Local\Direct\Api5;
use Local\System\ExtCache;

class TmpGroups {

	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Utils/TmpGroups/';

	/**
	 * ID инфоблока
	 */
	const IBLOCK_ID = 10;
	
	public static function getAll($refreshCache = false) {
		$return = array();

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

			$iblockElement = new \CIBlockElement();
			$rsItems = $iblockElement->GetList(array('TIMESTAMP_X' => 'ASC'), array(
				'IBLOCK_ID' => self::IBLOCK_ID,
			), false, false, array(
				'ID', 'NAME', 'CODE', 'TIMESTAMP_X', 'DETAIL_TEXT',
				'PROPERTY_GROUP',
				'PROPERTY_AD',
				'PROPERTY_KEYWORD',
			));
			while ($item = $rsItems->Fetch())
			{
				$key = $item['CODE'];
				$return[$key] = array(
					'KEY' => $key,
					'NAME' => $item['NAME'],
					'ELEMENT' => $item['ID'],
					'TIMESTAMP_X' => $item['TIMESTAMP_X'],
					'GROUP' => intval($item['PROPERTY_GROUP_VALUE']),
					'AD' => intval($item['PROPERTY_AD_VALUE']),
					'KEYWORD' => intval($item['PROPERTY_KEYWORD_VALUE']),
				    'DETAIL_TEXT' => $item['DETAIL_TEXT'],
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}
	
	public static function getByKey($key) {
		$all = self::getAll();
		return $all[$key];
	}
	
	public static function getOldest() {
		$all = self::getAll();
		return array_shift($all);
	}
	
	public static function add($key, $regions, $url, $keyword) {
	
		$campaignId = 20102952;
	
		$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'adgroups');
		$res = $api->method('add', array(
			'AdGroups' => array(
				array(
					'Name' => $key,
					'CampaignId' => $campaignId,
					'RegionIds' => $regions,
				),
			),
		));
		$groupId = intval($res['result']['AddResults'][0]['Id']);
		if (!$groupId)
			return 'Ошибка добавления группы';
		
		$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'ads');
		$res = $api->method('add', array(
			'Ads' => array(
				array(
					'TextAd' => array(
						'Text' => $keyword,
						'Title' => $keyword,
						'Href' => 'http://' . $url,
						'Mobile' => 'NO',
					),
					'AdGroupId' => $groupId,
				),
			),
		));
		$adId = intval($res['result']['AddResults'][0]['Id']);
		if (!$adId)
			return 'Ошибка добавления объявления';
			
		/*$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'keywords');
		$res = $api->method('add', array(
			'Keywords' => array(
				array(
					'Keyword' => $keyword,
					'AdGroupId' => $groupId,
				),
			),
		));
		$keywordId = intval($res['result']['AddResults'][0]['Id']);
		if (!$keywordId)
			return 'Ошибка добавления фразы';*/

		$iblockElement = new \CIBlockElement();
		$iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $keyword,
			'CODE' => $key,
			'PROPERTY_VALUES' => array(
				'GROUP' => $groupId,
				'AD' => $adId,
				//'KEYWORD' => $keywordId,
			),
		));
		
		self::getAll(true);
		return '';
	}
	
	public static function update($group, $key, $regions, $url, $keyword) {
	
		$tmp = explode('|', $group['KEY']);
		$oldUrl = $tmp[1];
		
		$groupId = $group['GROUP'];
		$adId = $group['AD'];

		if ($group['KEY'] != $key)
		{
			$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'adgroups');
			$res = $api->method('update', array(
				'AdGroups' => array(
					array(
						'Id' => $groupId,
						'Name' => $key,
						'RegionIds' => $regions,
					),
				),
			));
			$groupId = intval($res['result']['UpdateResults'][0]['Id']);
			if (!$groupId)
				return 'Ошибка изменения группы';
		}

		if ($oldUrl != $url)
		{
			$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'ads');
			$res = $api->method('update', array(
				'Ads' => array(
					array(
						'Id' => $adId,
						'TextAd' => array(
							'Text' => $keyword,
							'Title' => $keyword,
							'Href' => 'http://' . $url,
						),
					),
				),
			));
			$adId = intval($res['result']['UpdateResults'][0]['Id']);
			if (!$adId)
				return 'Ошибка изменения объявления';
		}
		
		/*if ($group['NAME'] != $keyword)
		{
			$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'keywords');
			$res = $api->method('update', array(
				'Keywords' => array(
					array(
						'Id' => $keywordId,
						'Keyword' => $keyword,
					),
				),
			));
			
			$keywordId = intval($res['result']['UpdateResults'][0]['Id']);
			if (!$keywordId)
				return 'Ошибка изменения фразы';
		}*/
			
		$iblockElement = new \CIBlockElement();
		$iblockElement->Update($group['ELEMENT'], array(
			'NAME' => $keyword,
			'CODE' => $key,
			'PROPERTY_VALUES' => array(
				'GROUP' => $groupId,
				'AD' => $adId,
				//'KEYWORD' => $keywordId,
			),
		));
		
		self::getAll(true);
		return '';
	}
	
	public static function updateKeyword($group, $keyword) {
		$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'keywords');
		$res = $api->method('update', array(
			'Keywords' => array(
				array(
					'Id' => $group['KEYWORD'],
					'Keyword' => $keyword,
				),
			),
		));

		$keywordId = intval($res['result']['UpdateResults'][0]['Id']);
		if (!$keywordId)
			return 'Ошибка изменения фразы';
			
		$iblockElement = new \CIBlockElement();
		$iblockElement->Update($group['ELEMENT'], array('NAME' => $keyword));
		$iblockElement->SetPropertyValuesEx($group['ELEMENT'], self::IBLOCK_ID, array('KEYWORD' => $keywordId));
			
		self::getAll(true);
		return '';
	}

	public static function addKeywords($group, $keywords) {
		$return = array();
		$groupId = $group['GROUP'];

		if ($group['DETAIL_TEXT'])
		{
			$Ids = explode(',', $group['DETAIL_TEXT']);
			$KeywordIds = array();
			foreach ($Ids as $id)
			{
				if ($id)
					$KeywordIds[] = $id;
			}
			if (count($KeywordIds))
			{
				$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'keywords');
				$api->method('delete', array(
					'SelectionCriteria' => array(
						'Ids' => $KeywordIds,
					),
				));
			}
		}

		$add = array();
		foreach ($keywords as $word)
		{
			$add[] = array(
				'Keyword' => $word,
				'AdGroupId' => $groupId,
			);
		}

		$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'keywords');
		$res = $api->method('add', array(
			'Keywords' => $add,
		));

		$Ids = array();
		foreach ($res['result']['AddResults'] as $item)
		{
			$id = intval($item['Id']);
			$Ids[] = $id;
		}

		$iblockElement = new \CIBlockElement();
		$iblockElement->Update($group['ELEMENT'], array('DETAIL_TEXT' => implode(',', $Ids)));
		self::getAll(true);

		$KeywordIds = array();
		foreach ($Ids as $id)
		{
			if ($id)
				$KeywordIds[] = $id;
		}

		$f = array();
		if ($KeywordIds) {
			$api = new Api5(AGENT_DIRECT_TOKEN, TEST_CLIENT_NAME, 'bids');
			$bids = $api->method('get', array(
				'SelectionCriteria' => array(
					'KeywordIds' => $KeywordIds,
				),
				'FieldNames' => array(
					'KeywordId',
					'Bid',
					//'ContextBid',
					//'CompetitorsBids',
					//'SearchPrices',
					//'ContextCoverage',
					//'MinSearchPrice',
					//'CurrentSearchPrice',
					'AuctionBids',
				),
			));

			foreach ($bids['result']['Bids'] as $res)
			{
				foreach ($res['AuctionBids'] as $item)
				{
					$f[$res['KeywordId']][$item['Position']] = number_format($item['Bid'] / 1000000, 2, ',', ' ');
				}
			}
		}

		foreach ($Ids as $k => $id)
		{
			$x = array();
			if ($id && $f[$id])
				$x = $f[$id];
			$return[$k] = $x;
		}

		return $return;
	}
	
}
