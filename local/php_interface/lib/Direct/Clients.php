<?
namespace Local\Direct;

use Local\ExtCache;
use Local\ExtUser;
use Local\Utils;

/**
 * Клиенты Директа
 */
class Clients
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Direct/Clients/';

	/**
	 * Возвращает всех клиентов Директа, прикрепленных к пользователю битрикса
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getByCurrentUser($refreshCache = false)
	{
		$return = array();
		$userId = ExtUser::getCurrentUserId();
		if (!$userId)
			return $return;

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$userId,
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_clients'),
			    'PROPERTY_USER' => $userId,
			), false, false, array(
				'ID', 'NAME', 'CODE',
			    'PROPERTY_USER',
			    'PROPERTY_RoleAgent',
			    'PROPERTY_Email',
			    'PROPERTY_FIO',
			    'PROPERTY_Phone',
			    'PROPERTY_SmsPhone',
			    'PROPERTY_DateCreate',
			    'PROPERTY_Discount',
			    'PROPERTY_AllowEditCampaigns',
			    'PROPERTY_AllowTransferMoney',
			    'PROPERTY_AllowImportXLS',
			    'PROPERTY_StatusArch',
			    'PROPERTY_SendNews',
			    'PROPERTY_NonResident',
			    'PROPERTY_SendWarn',
			    'PROPERTY_SendAccNews',
			));
			while ($item = $rsItems->Fetch())
			{
				$return[$item['NAME']] = array(
					'ID' => $item['ID'],
					'NAME' => $item['NAME'],
					'TOKEN' => $item['CODE'],
					'RoleAgent' => $item['PROPERTY_ROLEAGENT_VALUE'] == 1,
					'Email' => $item['PROPERTY_EMAIL_VALUE'],
					'FIO' => $item['PROPERTY_FIO_VALUE'],
					'Phone' => $item['PROPERTY_PHONE_VALUE'],
					'SmsPhone' => $item['PROPERTY_SMSPHONE_VALUE'],
					'DateCreate' => $item['PROPERTY_DATECREATE_VALUE'],
					'Discount' => $item['PROPERTY_DISCOUNT_VALUE'],
					'AllowEditCampaigns' => $item['PROPERTY_ALLOWEDITCAMPAIGNS_VALUE'] == 1,
					'AllowTransferMoney' => $item['PROPERTY_ALLOWTRANSFERMONEY_VALUE'] == 1,
					'AllowImportXLS' => $item['PROPERTY_ALLOWIMPORTXLS_VALUE'] == 1,
					'StatusArch' => $item['PROPERTY_STATUSARCH_VALUE'] == 1,
					'SendNews' => $item['PROPERTY_SENDNEWS_VALUE'] == 1,
					'NonResident' => $item['PROPERTY_NONRESIDENT_VALUE'] == 1,
					'SendWarn' => $item['PROPERTY_SENDWARN_VALUE'] == 1,
					'SendAccNews' => $item['PROPERTY_SENDACCNEWS_VALUE'] == 1,
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает клиента по Логину. Клиент должен быть прикреплен к текущему пользователю
	 * @param $clientLogin
	 * @return mixed
	 */
	public static function getByLogin($clientLogin)
	{
		$all = self::getByCurrentUser();
		return $all[$clientLogin];
	}

	/**
	 * Добавление OAuth-токена
	 * @param string $token
	 * @return string
	 */
	public static function addToken($token)
	{
		$clients = self::getByCurrentUser();
		foreach ($clients as $client)
		{
			if ($client['TOKEN'] == $token)
				return 'Токен уже добавлен (' . $client['NAME'] . ')';
		}

		$api = new Api4($token);
		$directClient = $api->method('GetClientInfo');
		if (!isset($directClient['data'][0]))
			return 'Ошибка получения клиента';

		$directClient = $directClient['data'][0];

		foreach ($clients as $client)
		{
			if ($client['NAME'] == $directClient['Login'])
			{
				if ($client['TOKEN'] != $token)
				{
					self::addTokenToExistClient($token, $client['ID']);
					return 'Изменен токен для клиента ' . $client['NAME'];
				}
				else
					// Такой ситуации теоретически быть не должно
					return 'Токен уже существует';
			}
		}

		self::addClient($token, $directClient);
		return '';
	}

	/**
	 * Обновляет OAuth-токена для клиента
	 * @param $token
	 * @param $clientId
	 */
	public static function addTokenToExistClient($token, $clientId)
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->Update($clientId, array(
			'CODE' => $token,
		));
	}

	/**
	 * Добавляет клиента на основнии данных из API
	 * @param $token
	 * @param $directClient
	 */
	public static function addClient($token, $directClient)
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_clients'),
			'NAME' => $directClient['Login'],
			'CODE' => $token,
			'PROPERTY_VALUES' => array(
				'USER' => ExtUser::getCurrentUserId(),
				'RoleAgent' => $directClient['Role'] == 'Agent' ? 1 : 0,
				'Email' => $directClient['Email'],
				'FIO' => $directClient['FIO'],
				'Phone' => $directClient['Phone'],
				'SmsPhone' => $directClient['SmsPhone'],
				'DateCreate' => Common::convertDate($directClient['DateCreate']),
				'Discount' => $directClient['Discount'],
				'StatusArch' => $directClient['StatusArch'] == 'Yes' ? 1 : 0,
				'SendNews' => $directClient['SendNews'] == 'Yes' ? 1 : 0,
				'NonResident' => $directClient['NonResident'] == 'Yes' ? 1 : 0,
				'SendWarn' => $directClient['SendWarn'] == 'Yes' ? 1 : 0,
				'SendAccNews' => $directClient['SendAccNews'] == 'Yes' ? 1 : 0,
			),
		));
		self::getByCurrentUser(true);
	}

	/**
	 * Обновляет информацию по всем клиентам текущего пользователя
	 */
	public static function updateAll()
	{
		$clients = self::getByCurrentUser();
		foreach ($clients as $client)
		{
			/*$token = $client['TOKEN'];
			$api = new Api($token);

			// Получаем текущую дату
			$resDate = $api->method('GetChanges', array(
				'Timestamp' => '2015-10-01T23:59:59Z',
			));
			//$tsDate = $resDate['data']['Timestamp'];
			debugmessage($resDate);*/
		}
	}

	public static function getCampaigns($clientLogin)
	{
		$client = self::getByLogin($clientLogin);
		if (!$client)
			return false;

		$token = $client['TOKEN'];
		$api = new Api4($token);
		$resCampaigns = $api->method('GetCampaignsList');
		if (!isset($resCampaigns['data']))
			return false;

		$directCampaigns = $resCampaigns['data'];
		$refreshCache = false;
		foreach ($directCampaigns as $directCampaign)
		{
			$updated = Campaigns::check($clientLogin, $directCampaign);
			if ($updated)
				$refreshCache = true;
		}

		if ($refreshCache)
			Campaigns::getByClient($clientLogin, true);
	}

	public static function getAdGroups($clientLogin)
	{
		$client = self::getByLogin($clientLogin);
		if (!$client)
			return false;

		$campaigns = Campaigns::getByClient($client['NAME']);
		$ids = $campaigns['IDS'];

		$api = new Api5($client['TOKEN'], $client['NAME'], 'adgroups');
		$res = $api->method('get', array(
			'SelectionCriteria' => array(
				'CampaignIds' => $ids,
			),
			'FieldNames' => array(
				'Id',
				'Name',
				'CampaignId',
				'Status',
				'RegionIds',
				'NegativeKeywords',
			),
		));

		if (!isset($res['result']))
			return false;

		$directAdGroups = $res['result']['AdGroups'];
		$refreshCache = false;
		foreach ($directAdGroups as $directAdGroup)
		{
			$updated = AdGroups::check($clientLogin, $directAdGroup);
			if ($updated)
				$refreshCache = true;
		}

		if ($refreshCache)
			AdGroups::getByClient($clientLogin, true);
	}

	public static function getAds($clientLogin)
	{
		$client = self::getByLogin($clientLogin);
		if (!$client)
			return false;

		$campaigns = Campaigns::getByClient($client['NAME']);
		$ids = $campaigns['IDS'];

		$api = new Api5($client['TOKEN'], $client['NAME'], 'ads');
		$res = $api->method('get', array(
			'SelectionCriteria' => array(
				'CampaignIds' => $ids,
			),
			'FieldNames' => array(
				'AdCategories',
				'AgeLabel',
				'AdGroupId',
				'CampaignId',
				'Id',
				'State',
				'Status',
				'StatusClarification',
				'Type',
			),
			'TextAdFieldNames' => array(
				'AdImageHash',
				'DisplayDomain',
				'Href',
				'SitelinkSetId',
				'Text',
				'Title',
				'Mobile',
				'VCardId',
				'AdImageModeration',
				'SitelinksModeration',
				'VCardModeration',
			),
		));

		if (!isset($res['result']))
			return false;

		$directAds = $res['result']['Ads'];
		$refreshCache = false;
		foreach ($directAds as $directAd)
		{
			$updated = Ads::check($clientLogin, $directAd);
			if ($updated)
				$refreshCache = true;
		}

		if ($refreshCache)
			Ads::getByClient($clientLogin, true);
	}

	public static function getKeywords($clientLogin)
	{
		$client = self::getByLogin($clientLogin);
		if (!$client)
			return false;

		$campaigns = Campaigns::getByClient($client['NAME']);
		$ids = $campaigns['IDS'];

		$api = new Api5($client['TOKEN'], $client['NAME'], 'keywords');
		$res = $api->method('get', array(
			'SelectionCriteria' => array(
				'CampaignIds' => $ids,
			),
			'FieldNames' => array(
				'Id',
				'Keyword',
				'State',
				'Status',
				'AdGroupId',
				'CampaignId',
				'Bid',
				'ContextBid',
				'StrategyPriority',
				'UserParam1',
				'UserParam2',
				//'Productivity"',
			),
		));

		if (!isset($res['result']))
			return false;

		$directKeywords = $res['result']['Keywords'];
		$refreshCache = false;
		foreach ($directKeywords as $directKeyword)
		{
			$updated = Keywords::check($clientLogin, $directKeyword);
			if ($updated)
				$refreshCache = true;
		}

		if ($refreshCache)
			Keywords::getByClient($clientLogin, true);
	}

	/**
	 * tmp -------------------------------
	 */
	public static function tmp()
	{
		$clients = self::getByCurrentUser();
		foreach ($clients as $client)
		{
			//self::getCampaigns($client['NAME']);
			//self::getAdGroups($client['NAME']);
			//self::getAds($client['NAME']);
			self::getKeywords($client['NAME']);

			/*$token = $client['TOKEN'];
			$api = new Api($token);
			$res = $api->method('CreateOrUpdateCampaign', array(
				'CampaignID' => 106243,
				'Name' => 'Другое имя',
				'Login' => 'mcc-odiseo',
				'FIO' => 'Тестер',
				"Strategy" => array(
					"StrategyName" => "WeeklyBudget",
					"WeeklySumLimit" => 400,
					"MaxPrice" => 8
				),
				"EmailNotification" => array(
					"MoneyWarningValue" => 20,
					"SendAccNews" => "Yes",
					"WarnPlaceInterval" => 60,
					"SendWarn" => "Yes",
					"Email" => "test@test.ru"
				)
			));
			debugmessage($res);*/

			/*$token = $client['TOKEN'];
			$api = new Api4($token);
			$res = $api->method('GetChanges', array(
				'Timestamp' => '2015-10-16T01:10:00Z',
			    "Logins" => array(
				    $client['NAME'],
			    ),
			));
			debugmessage($res);*/

			/*$campaigns = Campaigns::getByClient($client['NAME']);
			$ids = $campaigns['IDS'];

			$api = new Api5($client['TOKEN'], $client['NAME'], 'adgroups');
			$res = $api->method('get', array(
				'SelectionCriteria' => array(
					'CampaignIds' => $ids,
				),
				'FieldNames' => array(
					'Id',
					'Name',
					'CampaignId',
					'Status',
					'RegionIds',
					'NegativeKeywords',
				),
			));

			$directAdGroups = $res['result'];
			$refreshCache = false;
			foreach ($directAdGroups as $directAdGroup)
			{
				$updated = AdGroups::check($clientId, $directAdGroup);
				if ($updated)
					$refreshCache = true;
			}

			if ($refreshCache)
				AdGroups::getByClient($clientId, true);*/
		}
	}
}
