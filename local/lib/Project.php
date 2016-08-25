<?
namespace Local;
use Local\Direct\Clients;
use Local\Google\Aw;

/**
 * Проекты
 */
class Project
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Project/';

	/**
	 * ID инфоблока с проектами
	 */
	const IBLOCK_ID = 8;

	private static function getByFilter($filter)
	{
		$return = array();
		$filter['IBLOCK_ID'] = self::IBLOCK_ID;
		$iblockElement = new \CIBlockElement();
		$rsItems = $iblockElement->GetList(array(), $filter, false, false, array(
			'ID', 'NAME', 'DETAIL_TEXT',
			'PROPERTY_USER',
			'PROPERTY_STEP',
			'PROPERTY_URL',
			'PROPERTY_ESTORE',
			'PROPERTY_YML',
			'PROPERTY_EMAIL',
			'PROPERTY_PRODUCT_TYPE',
			'PROPERTY_YANDEX_SEARCH',
			'PROPERTY_YANDEX_NET',
			'PROPERTY_GOOGLE_SEARCH',
			'PROPERTY_GOOGLE_NET',
			'PROPERTY_YANDEX_CLIENT',
			'PROPERTY_YANDEX_CLIENT_FIO',
			'PROPERTY_YANDEX_CLIENT_EMAIL',
			'PROPERTY_YANDEX_CLIENT_PASSWORD',
			'PROPERTY_GOOGLE_CLIENT',
			'PROPERTY_GOOGLE_CLIENT_ID',
			'PROPERTY_METRIKA_TOKEN',
			'PROPERTY_ANALYTICS_TOKEN',
		));
		while ($item = $rsItems->Fetch())
		{
			$data = json_decode($item['DETAIL_TEXT'], true);
			if (!$data)
				$data = array();
			$return[$item['ID']] = array(
				'ID' => intval($item['ID']),
				'NAME' => $item['NAME'],
				'PREVIEW_TEXT' => $item['PREVIEW_TEXT'],
				'DETAIL_TEXT' => $item['DETAIL_TEXT'],
				'STEP' => intval($item['PROPERTY_STEP_VALUE']),
			    'URL' => $item['PROPERTY_URL_VALUE'],
			    'ESTORE' => intval($item['PROPERTY_ESTORE_VALUE']),
			    'PRODUCT_TYPE' => intval($item['PROPERTY_PRODUCT_TYPE_VALUE']),
			    'YML' => $item['PROPERTY_YML_VALUE'],
			    'EMAIL' => $item['PROPERTY_EMAIL_VALUE'],
			    'DATA' => $data,
			    'YANDEX_SEARCH' => intval($item['PROPERTY_YANDEX_SEARCH_VALUE']),
			    'YANDEX_NET' => intval($item['PROPERTY_YANDEX_NET_VALUE']),
			    'GOOGLE_SEARCH' => intval($item['PROPERTY_GOOGLE_SEARCH_VALUE']),
			    'GOOGLE_NET' => intval($item['PROPERTY_GOOGLE_NET_VALUE']),
			    'YANDEX_CLIENT' => $item['PROPERTY_YANDEX_CLIENT_VALUE'],
			    'YANDEX_CLIENT_FIO' => $item['PROPERTY_YANDEX_CLIENT_FIO_VALUE'],
			    'YANDEX_CLIENT_EMAIL' => $item['PROPERTY_YANDEX_CLIENT_EMAIL_VALUE'],
			    'YANDEX_CLIENT_PASSWORD' => $item['PROPERTY_YANDEX_CLIENT_PASSWORD_VALUE'],
			    'GOOGLE_CLIENT' => $item['PROPERTY_GOOGLE_CLIENT_VALUE'],
			    'GOOGLE_CLIENT_ID' => $item['PROPERTY_GOOGLE_CLIENT_ID_VALUE'],
			    'METRIKA_TOKEN' => $item['PROPERTY_METRIKA_TOKEN_VALUE'],
			    'ANALYTICS_TOKEN' => $item['PROPERTY_ANALYTICS_TOKEN_VALUE'],
			);
		}
		return $return;
	}

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
			static::CACHE_PATH . __FUNCTION__ . '/',
			864000,
			false
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$return = self::getByFilter(array(
				'=PROPERTY_USER' => $userId,
				'=PROPERTY_STEP' => 0,
			));

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getById($id)
	{
		$projects = self::getByCurrentUser();
		return $projects[$id];
	}

	public static function getAdding()
	{
		$return = array();
		$userId = ExtUser::getCurrentUserId();
		if (!$userId)
			return $return;

		$return = self::getByFilter(array(
			'=PROPERTY_USER' => $userId,
			'>PROPERTY_STEP' => 0,
		));

		return array_shift($return);
	}

	public static function add($url)
	{
		$return = array();
		$userId = ExtUser::getCurrentUserId();
		if (!$userId)
			return $return;

		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $url,
			'PROPERTY_VALUES' => array(
				'USER' => $userId,
				'STEP' => 20,
				'URL' => $url,
			    'YANDEX_SEARCH' => 1,
			    'YANDEX_NET' => 1,
			    'GOOGLE_SEARCH' => 1,
			    'GOOGLE_NET' => 1,
			),
		));

		return $id;
	}

	public static function update($project, $data)
	{
		if (!$project['ID'])
			return false;

		$fields = array();
		$props = array();
		$detail = $project['DATA'];
		foreach ($data as $k => $v)
		{
			if ($data[$k] == $project[$k])
				continue;

			if ($k == 'STEP')
			{
				if ($project['STEP'] < $v || $v == 0)
					$props[$k] = $v;
			}
			elseif ($k == 'NAME')
				$fields[$k] = $v;
			elseif ($k == 'DATA')
				$detail = array_merge($detail, $v);
			else
				$props[$k] = $v;
		}

		$json = json_encode($detail, JSON_UNESCAPED_UNICODE);
		if ($project['DETAIL_TEXT'] != $json)
			$fields['DETAIL_TEXT'] = $json;

		if ($fields || $props)
		{
			$iblockElement = new \CIBlockElement();
			if ($fields)
			{
				$iblockElement->Update($project['ID'], $fields);
			}
			if ($props)
			{
				$iblockElement->SetPropertyValuesEx($project['ID'], self::IBLOCK_ID, $props);
			}
			if ($project['STEP'] == 0 || $props['STEP'] === 0)
				self::getByCurrentUser(true);

			return true;
		}

		return false;
	}

	public static function addFormSubmit()
	{
		$return = array(
			'errors' => array(),
		);
		$userId = ExtUser::getCurrentUserId();
		if (!$userId)
		{
			$return['errors'][] = 'Ошибка авторизации';
			return $return;
		}

		$project = self::getAdding();
		$return['step'] = $project['STEP'];

		$step = intval($_REQUEST['step']);
		if ($step == 10)
		{
			if ($_REQUEST['url'])
			{
				if ($project)
					self::update($project, array(
						'STEP' => 20,
						'URL' => $_REQUEST['url'],
					));
				else
				{
					self::add($_REQUEST['url']);
					$return['new_project'] = true;
				}
			}
		}
		elseif ($step == 20)
		{
			if ($_REQUEST['estore'] && $project)
			{
				self::update($project, array(
					'STEP' => 30,
					'ESTORE' => $_REQUEST['estore'],
					'YML' => $_REQUEST['yml'],
				));
			}
		}
		elseif ($step == 30)
		{
			if ($project)
			{
				self::update($project, array(
					'STEP' => 40,
					'NAME' => $_REQUEST['name'],
					'EMAIL' => $_REQUEST['email'],
					'DATA' => array(
						'INFO' => array(
							'company' => $_REQUEST['company'],
							'country' => $_REQUEST['country'],
							'city' => $_REQUEST['city'],
							'phone_prefix' => $_REQUEST['phone_prefix'],
							'phone_code' => $_REQUEST['phone_code'],
							'phone_number' => $_REQUEST['phone_number'],
							'phone_add' => $_REQUEST['phone_add'],
							'regime' => $_REQUEST['regime'],
							'street' => $_REQUEST['street'],
							'house' => $_REQUEST['house'],
							'build' => $_REQUEST['build'],
							'apart' => $_REQUEST['apart'],
						),
					),
				));
			}
		}
		elseif ($step == 40)
		{
			if ($project)
			{
				self::update($project, array(
					'STEP' => 50,
					'PRODUCT_TYPE' => $_REQUEST['product_type'],
					'DATA' => array(
						'PRODUCT' => array(
							'prod1' => $_REQUEST['prod1'],
							'prod2' => $_REQUEST['prod2'],
							'prod3' => $_REQUEST['prod3'],
							'prod4' => $_REQUEST['prod4'],
							'prod5' => $_REQUEST['prod5'],
							'prod6' => $_REQUEST['prod6'],
							'prod7' => $_REQUEST['prod7'],
							'prod8' => $_REQUEST['prod8'],
							'prod9' => $_REQUEST['prod9'],
							'prod10' => $_REQUEST['prod10'],
							'prod11' => $_REQUEST['prod11'],
							'prod12' => $_REQUEST['prod12'],
						),
					),
				));
			}
		}
		elseif ($step == 50)
		{
			if ($project)
			{
				$fields = array(
					'STEP' => 70,
					'YANDEX_SEARCH' => $_REQUEST['yandex-search'],
					'YANDEX_NET' => $_REQUEST['yandex-net'],
					'GOOGLE_SEARCH' => $_REQUEST['google-search'],
					'GOOGLE_NET' => $_REQUEST['google-net'],
					'DATA' => array(
						'TIME' => array(
							'ITEMS' => $_REQUEST['time'],
						),
						'REGIONS' => $_REQUEST['regions'],
					),
				);
				if ($_REQUEST['yandex-search'] || $_REQUEST['yandex-net'])
				{
					if (!$project['YANDEX_CLIENT'])
					{
						$directClient = Clients::createSubClient($project);
						if ($directClient)
						{
							$fields['YANDEX_CLIENT'] = $directClient['Login'];
							$fields['YANDEX_CLIENT_FIO'] = $directClient['FIO'];
							$fields['YANDEX_CLIENT_EMAIL'] = $directClient['Email'];
							$fields['YANDEX_CLIENT_PASSWORD'] = $directClient['Password'];
						}
						else
						{
							$return['errors'][] = 'Ошибка создания пользователя в Яндекс.Директ';
							$fields['STEP'] = 50;
						}
					}
				}
				if ($_REQUEST['google-search'] || $_REQUEST['google-net'])
				{
					if (!$project['GOOGLE_CLIENT'])
					{
						$awClient = Aw::createSubAccount($project);
						if ($awClient)
						{
							$fields['GOOGLE_CLIENT'] = $awClient->name;
							$fields['GOOGLE_CLIENT_ID'] = $awClient->customerId;
						}
						else
						{
							$return['errors'][] = 'Ошибка создания пользователя в Google.Adwords';
							$fields['STEP'] = 50;
						}
					}
				}
				self::update($project, $fields);

			}
		}
		elseif ($step == 70)
		{
			if ($project)
			{
				$fields = array(
					'STEP' => 80,
				);
				self::update($project, $fields);
			}
		}
		elseif ($step == 80)
		{
			if ($project)
			{
				$fields = array(
					'STEP' => 100,
				);
				self::update($project, $fields);
			}
		}
		elseif ($step == 100)
		{
			if ($project)
			{
				$fields = array(
					'STEP' => 0,
				);
				self::update($project, $fields);
				self::getByCurrentUser(true);
			}
		}

		$project = self::getAdding();
		if ($project)
			$return['step'] = $project['STEP'];
		else
			$return['to_projects'] = true;

		return $return;
	}

	public static function addMetrikaToken($token)
	{
		$userId = ExtUser::getCurrentUserId();
		if (!$userId)
			return 'Ошибка авторизации';

		$project = self::getAdding();
		if ($project)
			self::update($project, array(
				'METRIKA_TOKEN' => $token,
			));

		return '';
	}

	public static function addAnalyticsToken($token)
	{
		$userId = ExtUser::getCurrentUserId();
		if (!$userId)
			return 'Ошибка авторизации';

		$project = self::getAdding();
		if ($project)
			self::update($project, array(
				'ANALYTICS_TOKEN' => $token,
			));

		return '';
	}

}
