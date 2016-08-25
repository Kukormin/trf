<?
namespace Local\Direct;

use Local\Api\ApiException;

/**
 * Импорт кампаний и других данных из Директа
 * Class Import
 * @package Local\Direct
 */
class Import
{

	/**
	 * Проверяет изменения в кампаниях с даты, хранящейся в клиенте
	 * и создает новые кампании, если добавились
	 * @param $client
	 * @return array|mixed
	 */
	public static function checkCampaigns($client)
	{
		$api = new Api5($client['TOKEN'], $client['NAME'], 'changes');

		$res = $api->method('checkCampaigns', array(
			'Timestamp' => $client['SYNC'],
		));

		$changesByCampaignId = array();
		if ($res['result']['Campaigns'])
		{
			$addIds = array();
			foreach ($res['result']['Campaigns'] as $changes)
			{
				$directCampaignId = $changes['CampaignId'];
				$campaign = Campaigns::getByDirectId($client['NAME'], $directCampaignId);
				if ($campaign)
					$changesByCampaignId[$campaign['ID']] = $changes['ChangesIn'];
				else
					$addIds[] = $directCampaignId;
			}

			// создаем в БД кампании с этими Id
			if ($addIds)
			{
				$api = new Api5($client['TOKEN'], $client['NAME'], 'campaigns');
				$resCampaigns = $api->method('get', array(
					'SelectionCriteria' => array(
						'Ids' => $addIds,
					),
					'FieldNames' => array(
						'Id', 'Name',
					),
				));
				foreach ($resCampaigns['result']['Campaigns'] as $directCampaign)
					Campaigns::add($client, $directCampaign);
			}

		}

		$campaigns = Campaigns::getByClient($client['NAME']);
		foreach ($campaigns['ITEMS'] as &$campaign)
		{
			$changes = $changesByCampaignId[$campaign['ID']];
			$campaign['CHANGES'] = $changes;
		}
		unset($campaign);

		return $campaigns;
	}

	/**
	 * Получает свойства кампании по API и обновляет БД на основании полученных данных
	 * @param $clientId
	 * @param int $campaignId Если кампания не задана, то работает по всем кампаниям клиента
	 * @return array
	 * @throws ApiException
	 */
	public static function campaign($clientId, $campaignId = 0)
	{
		$client = Clients::getById($clientId);
		if (!$client)
			throw new ApiException('wrong_client', 400);

		$campaignIds = array();
		if ($campaignId)
		{
			$campaign = Campaigns::getById($client['NAME'], $campaignId);
			if (!$campaign)
				throw new ApiException('wrong_campaign', 400);

			$campaigns = array($campaign);
			$campaignIds[] = $campaign['CampaignID'];
			$timestamp = $campaign['SYNC'];
		}
		else
		{
			$campaigns = Campaigns::getByClient($client['Login']);
			foreach ($campaigns['ITEMS'] as $campaign)
			{
				if ($campaign['ACTIVE'] == 'Y')
					$campaignIds[] = $campaign['CampaignID'];
			}
			$timestamp = $client['SYNC'];
		}

		$api = new Api5($client['TOKEN'], $client['NAME'], 'changes');
		$res = $api->method('check', array(
			'Timestamp' => $timestamp,
			'CampaignIds' => $campaignIds,
			'FieldNames' => array('CampaignIds', 'CampaignsStat', 'AdGroupIds', 'AdIds'),
		));
		if ($res['error'])
			throw new ApiException($res['error'], 500);

		$vCardIds = array();
		$sitelinksIds = array();
		$modified = $res['result']['Modified'];
		$dtSync = $res['result']['Timestamp'];
		$result = array(
			'direct' => array(
				'CampaignIds' => count($modified['CampaignIds']),
				'AdGroupIds' => count($modified['AdGroupIds']),
				'AdIds' => count($modified['AdIds']),
			),
			'campaign' => array(
				'all' => 0,
				'add' => 0,
				'update' => 0,
				'same' => 0,
			),
			'group' => array(
				'all' => 0,
				'add' => 0,
				'update' => 0,
			    'same' => 0,
			),
			'keyword' => array(
				'all' => 0,
				'add' => 0,
				'update' => 0,
				'same' => 0,
			),
			'ad' => array(
				'all' => 0,
				'add' => 0,
				'update' => 0,
				'same' => 0,
			),
			'vcard' => array(
				'all' => 0,
				'add' => 0,
				'update' => 0,
				'same' => 0,
			),
			'link' => array(
				'all' => 0,
				'add' => 0,
				'update' => 0,
				'same' => 0,
			),
		);


		//
		// Импорт кампаний
		//
		if ($modified['CampaignIds'])
		{
			$api = new Api5($client['TOKEN'], $client['NAME'], 'campaigns');
			$res = $api->method('get', array(
				'SelectionCriteria' => array(
					'Ids' => $modified['CampaignIds'],
				),
				'FieldNames' => array(
					'Id',
					'Name',
					'ClientInfo',
					'StartDate',
					'EndDate',
					'TimeTargeting',
					'TimeZone',
					'NegativeKeywords',
					'BlockedIps',
					'ExcludedSites',
					'DailyBudget',
					'Notification',
					'Type',
					'Status',
					'State',
					'StatusPayment',
					'StatusClarification',
					'SourceId',
					'Statistics',
					'Currency',
					'Funds',
					'RepresentedBy',
				),
				'TextCampaignFieldNames' => array(
					'BiddingStrategy',
					'Settings',
					'CounterIds',
					'RelevantKeywords',
				),
			));
			if ($res['error'])
				throw new ApiException($res['error'], 500);

			foreach ($res['result']['Campaigns'] as $directCampaign)
			{
				// Обновляет или добавляет кампанию
				Campaigns::check($directCampaign, $result['campaign']);
				$result['campaign']['all']++;
			}

		}

		//
		// Импорт групп объявлений
		//
		if ($modified['AdGroupIds'])
		{
			$api = new Api5($client['TOKEN'], $client['NAME'], 'adgroups');
			$offset = 0;
			do
			{
				$res = $api->method('get', array(
					'SelectionCriteria' => array(
						'Ids' => $modified['AdGroupIds'],
					),
					'FieldNames' => array(
						'Id',
						'Name',
						'CampaignId',
						'RegionIds',
						'NegativeKeywords',
						'TrackingParams',
						'Status',
						'Type',
					),
					'Page' => array(
						'Offset' => $offset,
					),
				));
				if ($res['error'])
					throw new ApiException($res['error'], 500);

				foreach ($res['result']['AdGroups'] as $directGroup)
				{
					// Обновляет или добавляет группу объявлений
					AdGroups::check($directGroup, $result['group']);
					$result['group']['all']++;
				}

				$offset = $res['result']['LimitedBy'];
			} while ($offset);

			//
			// Импорт ключевых фраз
			//
			$api = new Api5($client['TOKEN'], $client['NAME'], 'keywords');
			$offset = 0;
			do
			{
				$res = $api->method('get', array(
					'SelectionCriteria' => array(
						'AdGroupIds' => $modified['AdGroupIds'],
					),
					'FieldNames' => array(
						'Id',
						'AdGroupId',
						'CampaignId',
						'Keyword',
						'State',
						'Status',
						'Bid',
						'ContextBid',
						'StrategyPriority',
						'UserParam1',
						'UserParam2',
						'Productivity',
						'StatisticsSearch',
						'StatisticsNetwork',
					),
					'Page' => array(
						'Offset' => $offset,
					),
				));
				if ($res['error'])
					throw new ApiException($res['error'], 500);

				foreach ($res['result']['Keywords'] as $directKeyword)
				{
					// Обновляет или добавляет ключевую фразу
					Keywords::check($directKeyword, $result['keyword']);
					$result['keyword']['all']++;
				}

				$offset = $res['result']['LimitedBy'];
			} while ($offset);
		}

		/*$api = new Api5($client['TOKEN'], $client['NAME'], 'keywords');
		$res = $api->method('get', array(
			'SelectionCriteria' => array(
				'Ids' => array(3473423415),
			),
			'FieldNames' => array(
				'Id',
				'AdGroupId',
				'CampaignId',
				'Keyword',
				'State',
				'Status',
				'Bid',
				'ContextBid',
				'StrategyPriority',
				'UserParam1',
				'UserParam2',
				'Productivity',
				'StatisticsSearch',
				'StatisticsNetwork',
			),
		));
		debugmessage($res);*/


		//
		// Импорт объявлений
		//
		if ($modified['AdIds'])
		{
			$api = new Api5($client['TOKEN'], $client['NAME'], 'ads');
			$offset = 0;
			do
			{
				$res = $api->method('get', array(
					'SelectionCriteria' => array(
						'Ids' => $modified['AdIds'],
					),
					'FieldNames' => array(
						'Id',
						'CampaignId',
						'AdGroupId',
						'Status',
						'State',
						'StatusClarification',
						'AdCategories',
					    'AgeLabel',
					    'Type',
					),
					'TextAdFieldNames' => array(
						'Title',
						'Text',
					    'Href',
					    'DisplayDomain',
					    'Mobile',
					    'VCardId',
					    'VCardModeration',
					    'SitelinkSetId',
					    'SitelinksModeration',
					    'AdImageHash',
					    'AdImageModeration',
					),
					'Page' => array(
						'Offset' => $offset,
					),
				));
				if ($res['error'])
					throw new ApiException($res['error'], 500);

				foreach ($res['result']['Ads'] as $directAd)
				{
					// Обновляет или добавляет объявление
					Ads::check($directAd, $result['ad']);
					$result['ad']['all']++;

					// Собираем массив Id виртуальных визиток
					$vCard = intval($directAd['TextAd']['VCardId']);
					if ($vCard)
						$vCardIds[$vCard] = $vCard;
					// Собираем массив Id наборов быстрых ссылок
					$sitelinks = intval($directAd['TextAd']['SitelinkSetId']);
					if ($sitelinks)
						$sitelinksIds[$sitelinks] = $sitelinks;
				}

				$offset = $res['result']['LimitedBy'];
			} while ($offset);
		}

		//
		// Импорт визиток
		//
		if ($vCardIds)
		{
			$api = new Api5($client['TOKEN'], $client['NAME'], 'vcards');
			$offset = 0;
			$refrechCache = array();
			do
			{
				$res = $api->method('get', array(
					'SelectionCriteria' => array(
						'Ids' => array_values($vCardIds),
					),
					'FieldNames' => array(
						'Id',
						'CampaignId',
						'Country',
						'City',
						'WorkTime',
						'Phone',
						'Street',
						'House',
						'Building',
						'Apartment',
						'InstantMessenger',
						'CompanyName',
						'ExtraMessage',
						'ContactEmail',
						'Ogrn',
						'MetroStationId',
						'PointOnMap',
						'ContactPerson',
					),
					'Page' => array(
						'Offset' => $offset,
					),
				));
				if ($res['error'])
					throw new ApiException($res['error'], 500);

				foreach ($res['result']['VCards'] as $directVCard)
				{
					// Добавляет визитку
					$updated = VCards::check($directVCard['CampaignId'], $directVCard, $result['vcard']);
					if ($updated)
						$refrechCache[$directVCard['CampaignId']] = $directVCard['CampaignId'];
					$result['vcard']['all']++;
				}

				$offset = $res['result']['LimitedBy'];
			} while ($offset);

			// Сбрасываем кеш, если были изменения
			foreach ($refrechCache as $cId)
				VCards::getByCampaign($cId, true);
		}

		//
		// Импорт быстрых ссылок
		//
		if ($sitelinksIds)
		{
			$api = new Api5($client['TOKEN'], $client['NAME'], 'sitelinks');
			$offset = 0;
			do
			{
				$res = $api->method('get', array(
					'SelectionCriteria' => array(
						'Ids' => array_values($sitelinksIds),
					),
					'FieldNames' => array(
						'Id',
						'Sitelinks',
					),
					'Page' => array(
						'Offset' => $offset,
					),
				));
				if ($res['error'])
					throw new ApiException($res['error'], 500);

				foreach ($res['result']['SitelinksSets'] as $directSitelinks)
				{
					// Добавляет набор быстрых ссылок
					Sitelinks::check($directSitelinks, $result['link']);
					$result['link']['all']++;
				}

				$offset = $res['result']['LimitedBy'];
			} while ($offset);
		}

		//
		// Обновляем время синхронизации
		//
		if ($modified)
		{
			if (!$campaignId)
				Clients::updateSync($client, $dtSync);
			foreach ($campaigns as $campaign)
				Campaigns::updateSync($campaign, $dtSync);
		}

		//
		// Формируем html результата
		//
		if ($modified)
		{
			$html = '<ul>';
			if ($result['campaign']['update'])
				$html .= '<li>Обновлено кампаний: ' . $result['campaign']['update'] . '</li>';
			if ($result['group']['add'])
				$html .= '<li>Добавлено групп: ' . $result['group']['add'] . '</li>';
			if ($result['group']['update'])
				$html .= '<li>Обновлено групп: ' . $result['group']['update'] . '</li>';
			if ($result['keyword']['add'])
				$html .= '<li>Добавлено ключевых фраз: ' . $result['keyword']['add'] . '</li>';
			if ($result['keyword']['update'])
				$html .= '<li>Обновлено ключевых фраз: ' . $result['keyword']['update'] . '</li>';
			if ($result['ad']['add'])
				$html .= '<li>Добавлено объявлений: ' . $result['ad']['add'] . '</li>';
			if ($result['ad']['update'])
				$html .= '<li>Обновлено объявлений: ' . $result['ad']['update'] . '</li>';
			if ($result['vcard']['add'])
				$html .= '<li>Добавлено виртуальных визиток: ' . $result['vcard']['add'] . '</li>';
			if ($result['vcard']['update'])
				$html .= '<li>Обновлено виртуальных визиток: ' . $result['vcard']['update'] . '</li>';
			if ($result['link']['add'])
				$html .= '<li>Добавлено наборов быстрых ссылок: ' . $result['link']['add'] . '</li>';
			if ($result['link']['update'])
				$html .= '<li>Обновлено наборов быстрых ссылок: ' . $result['link']['update'] . '</li>';
			$html .= '</ul>';
		}
		else
		{
			$html = '<p>Изменений нет</p>';
		}
		$result['html'] = $html;

		return $result;
	}
}
