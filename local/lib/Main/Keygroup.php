<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ExpressionField;
use Local\System\ExtCache;

/**
 * Группа, содержащая ключевое слово и от одного до нескольких объявлений
 */
class Keygroup
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/Keygroup/';

	/**
	 * ID HL-блока
	 */
	const ENTITY_ID = 3;

	/**
	 * сколько секунд фраза считается новой
	 */
	const NEW_TS = 7200;

	/**
	 * Ключ в урле
	 */
	const URL = 'kg';

	/**
	 * Фраза создана вручную
	 */
	const TYPE_MANUAL = 0;
	/**
	 * Фраза создана вручную
	 */
	const TYPE_BASE = 1;
	/**
	 * Фраза создана вручную
	 */
	const TYPE_DEACTIVE = 2;

	/**
	 * @var array настройки панели фильтров
	 */
	private static $FILTER_SETTINGS = array(
		'txt' => array(
			'NAME' => 'Содержит в себе текст',
			'TYPE' => 'text',
		    'VALUE' => '',
		),
	    'ad_count' => array(
		    'NAME' => 'Количество объявлений',
		    'TYPE' => 'radio',
	        'VARS' => array(
		        '0' => 'любое',
		        '1' => '0',
		        '2' => '1',
		        '3' => '2+',
	        ),
	        'VALUE' => '0',
	    ),
	    'ws' => array(
		    'NAME' => 'Частотность',
		    'TYPE' => 'radio',
		    'VARS' => array(
			    '0' => 'любая',
			    '1' => 'не проверена',
			    '2' => '=0',
			    '3' => '>0',
		    ),
		    'VALUE' => '0',
	    ),
	    'new' => array(
		    'NAME' => 'Новизна',
		    'TYPE' => 'radio',
		    'VARS' => array(
			    '0' => 'не важно',
			    '1' => 'новые',
			    '2' => 'старые',
		    ),
		    'VALUE' => '0',
	    ),
	    'base' => array(
		    'NAME' => 'Базовые слова',
		    'TYPE' => 'checkbox',
		    'VARS' => array(
			    '1' => 'сгенерирована из базовых слов',
			    '2' => 'деактивирована <i class="help" data-placement="top" data-original-title="Фраза деактивирована"
			            data-content="Сгенерирована из базовых слов, но база поменялась"></i>',
			    '3' => 'задана вручную',
		    ),
		    'VALUE' => array(),
	    ),
	    'base_count' => array(
		    'NAME' => 'Количество базовых столбцов',
		    'TYPE' => 'radio',
		    'VARS' => array(
			    '0' => 'любое',
			    '1' => '<=3',
			    '2' => '<=4',
			    '3' => '<=5',
			    '4' => '>=3',
			    '5' => '>=4',
			    '6' => '>=5',
		    ),
		    'VALUE' => '0',
	    ),
	    'mark' => array(
		    'NAME' => 'Метки',
		    'TYPE' => 'checkbox',
		    'VARS' => array(),
		    'VALUE' => array(),
	    ),
	);

	/**
	 * @var array Количество элементов на странице
	 */
	private static $PAGE_SIZES = array(
		'20' => '20',
		'50' => '50',
		'100' => '100',
		'500' => '500',
		'all' => 'все',
	);

	/**
	 * @var array настройки вида ключевых фраз
	 */
	private static $YGSN = array(
		'y' => array(
			'NAME' => 'Яндекс',
			'VALUE' => 1,
		),
		'g' => array(
			'NAME' => 'Google',
			'VALUE' => 1,
		),
		's' => array(
			'NAME' => 'Поиск',
			'VALUE' => 1,
		),
		'n' => array(
			'NAME' => 'Сети',
			'VALUE' => 1,
		),
	);

	public static function getPageSizes()
	{
		$user = User::getCurrentUser();

		$size = intval($_REQUEST['size']);
		if (!$size)
			$size = $user['DATA']['PAGE_SIZE'];
		if (!$size)
			foreach (self::$PAGE_SIZES as $k => $v)
			{
				$size = $k;
				break;
			}

		if ($size && $user['DATA']['PAGE_SIZE'] != $size)
		{
			User::saveData(array(
				'PAGE_SIZE' => $size,
			));
		}

		$return = array(
			'SIZE' => $size,
		);
		foreach (self::$PAGE_SIZES as $k => $v)
		{
			$return['ITEMS'][$k] = array(
				'NAME' => $v,
			    'SELECTED' => $k == $size,
			);
		}

		return $return;
	}

	public static function getFilters()
	{
		$user = User::getCurrentUser();

		$filtersSelected = array();
		$filters = array();
		foreach (self::$FILTER_SETTINGS as $code => $item)
		{
			if ($code == 'mark')
			{
				$marks = Mark::getByCurrentUser();
				foreach ($marks as $mark)
					$item['VARS'][$mark['ID']] = $mark['NAME'] . ' ' . $mark['HTML'];
			}

			if ($_REQUEST['mode'] == 'ajax')
			{
				if ($item['TYPE'] == 'text')
				{
					$item['VALUE'] = htmlspecialchars($_REQUEST[$code]);
				}
				elseif ($item['TYPE'] == 'checkbox')
				{
					foreach ($item['VARS'] as $k => $v)
						$item['VALUE'][$k] = $_REQUEST[$code][$k] ? 1 : 0;
				}
				else
				{
					if (isset($item['VARS'][$_REQUEST[$code]]))
						$item['VALUE'] = $_REQUEST[$code];
				}

				$filtersSelected[$code] = $item['VALUE'];
			}
			else
			{
				if (isset($user['DATA']['FILTERS_SELECTED'][$code]))
					$item['VALUE'] = $user['DATA']['FILTERS_SELECTED'][$code];
			}

			$item['ACTIVE'] = $user['DATA']['FILTERS_ACTIVE'][$code] || $code == 'type';
			$filters[$code] = $item;
		}

		$ygsnSelected = array();
		$ygsn = array();
		foreach (self::$YGSN as $code => $item)
		{
			if ($_REQUEST['mode'] == 'ajax')
			{
				$item['VALUE'] = $_REQUEST[$code] ? 1 : 0;
				$ygsnSelected[$code] = $item['VALUE'];
			}
			else
			{
				if (isset($user['DATA']['YGSN'][$code]))
					$item['VALUE'] = $user['DATA']['YGSN'][$code];
			}

			$ygsn[$code] = $item;
		}

		if ($_REQUEST['mode'] == 'ajax')
			User::saveData(array(
				'FILTERS_SELECTED' => $filtersSelected,
				'YGSN' => $ygsnSelected,
			));

		return array(
			'FILTERS' => $filters,
		    'YGSN' => $ygsn,
		);
	}

	public static function checkYGSNonProlog()
	{
		// Обработка входящих параметров
		if (isset($_GET['ygsn']))
		{
			$ygsn = array(
				'y' => $_GET['ygsn'][0] == '1' ? 1 : 0,
				'g' => $_GET['ygsn'][1] == '1' ? 1 : 0,
				's' => $_GET['ygsn'][2] == '1' ? 1 : 0,
				'n' => $_GET['ygsn'][3] == '1' ? 1 : 0,
			);
			User::saveData(array('YGSN' => $ygsn));
			/** global CMain $APPLICATION */
			global $APPLICATION;
			LocalRedirect($APPLICATION->GetCurDir());
		}
	}

	public static function getParamsForGetList($filtersData)
	{
		$filters = $filtersData['FILTERS'];
		$size = $filtersData['PAGE_SIZE'];

		$filter = array();
		$order = array();

		$ts = time();

		if ($filters['txt']['ACTIVE'])
			if ($filters['txt']['VALUE'])
				$filter['UF_NAME'] = '%' . $filters['txt']['VALUE'] . '%';

		if ($filters['ad_count']['ACTIVE'])
			if ($filters['ad_count']['VALUE'] == 1)
				$filter['=UF_AD_COUNT'] = 0;
			elseif ($filters['ad_count']['VALUE'] == 2)
				$filter['=UF_AD_COUNT'] = 1;
			elseif ($filters['ad_count']['VALUE'] == 3)
				$filter['>UF_AD_COUNT'] = 1;

		if ($filters['ws']['ACTIVE'])
			if ($filters['ws']['VALUE'] == 1)
				$filter['=UF_WORDSTAT'] = -1;
			elseif ($filters['ws']['VALUE'] == 2)
				$filter['=UF_WORDSTAT'] = 0;
			elseif ($filters['ws']['VALUE'] == 3)
				$filter['>UF_WORDSTAT'] = 0;

		if ($filters['new']['ACTIVE'])
			if ($filters['new']['VALUE'] == 1)
				$filter['>=UF_TS'] = $ts - self::NEW_TS;
			elseif ($filters['new']['VALUE'] == 2)
				$filter['<UF_TS'] = $ts - self::NEW_TS;

		if ($filters['base']['ACTIVE'])
			if ($filters['base']['VALUE'])
			{
				if ($filters['base']['VALUE'][1] && $filters['base']['VALUE'][2])
					$filter['>UF_CREATE_TYPE'] = 0;
				elseif ($filters['base']['VALUE'][1] && $filters['base']['VALUE'][3])
					$filter['<UF_CREATE_TYPE'] = 2;
				elseif ($filters['base']['VALUE'][2] && $filters['base']['VALUE'][3])
					$filter['!UF_CREATE_TYPE'] = 1;
				elseif ($filters['base']['VALUE'][1])
					$filter['=UF_CREATE_TYPE'] = 1;
				elseif ($filters['base']['VALUE'][2])
					$filter['=UF_CREATE_TYPE'] = 2;
				elseif ($filters['base']['VALUE'][3])
					$filter['=UF_CREATE_TYPE'] = 0;
			}

		if ($filters['mark']['ACTIVE'])
			if ($filters['mark']['VALUE'])
				$filter['=UF_MARK'] = array_keys($filters['mark']['VALUE']);

		if ($filters['base_count']['ACTIVE'])
			if ($filters['base_count']['VALUE'] == 1)
				$filter['<=UF_BASE_COL_COUNT'] = 3;
			elseif ($filters['base_count']['VALUE'] == 2)
				$filter['<=UF_BASE_COL_COUNT'] = 4;
			elseif ($filters['base_count']['VALUE'] == 3)
				$filter['<=UF_BASE_COL_COUNT'] = 5;
			elseif ($filters['base_count']['VALUE'] == 4)
				$filter['>=UF_BASE_COL_COUNT'] = 3;
			elseif ($filters['base_count']['VALUE'] == 5)
				$filter['>=UF_BASE_COL_COUNT'] = 4;
			elseif ($filters['base_count']['VALUE'] == 6)
				$filter['>=UF_BASE_COL_COUNT'] = 5;

		/*if ($view['sort']['VALUE'] == 'ida')
			$order['ID'] = 'ASC';
		elseif ($view['sort']['VALUE'] == 'idd')
			$order['ID'] = 'DESC';
		elseif ($view['sort']['VALUE'] == 'wsa')
			$order['UF_WORDSTAT'] = 'ASC';
		elseif ($view['sort']['VALUE'] == 'wsd')
			$order['UF_WORDSTAT'] = 'DESC';*/
		$order['ID'] = 'ASC';

		$return = array(
			'filter' => $filter,
			'order' => $order,
		);

		if ($size != 'all')
		{
			$return['limit'] = $size;
			if ($_REQUEST['page'] > 1)
			{
				$return['page'] = $_REQUEST['page'];
				$return['offset'] = $return['limit'] * ($_REQUEST['page'] - 1);
			}
		}

		return $return;
	}

	public static function getList($projectId, $categoryId = 0, $params = array(), $refreshCache = false)
	{
		if (!$projectId)
			return false;

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$projectId,
				$categoryId,
				$params,
			),
			static::CACHE_PATH . __FUNCTION__ . '/' . $projectId . '/' . $categoryId . '/',
			86400,
			false
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			if ($categoryId)
				$params['filter']['=UF_CATEGORY'] = $categoryId;
			elseif ($projectId)
				$params['filter']['=UF_PROJECT'] = $projectId;

			$page = 1;
			if ($params['page'])
			{
				$page = $params['page'];
				unset($params['page']);
			}

			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$rsItems = $dataClass::getList($params);

			$return['ITEMS'] = array();
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);

				$base = array();
				if ($item['UF_BASE'])
				{
					foreach (explode('|', $item['UF_BASE']) as $tmp)
					{
						$ar = explode('#', $tmp);
						$col = $ar[0];
						$word = $ar[1];
						$base[$col] = $word;
					}
				}

				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $item['UF_NAME'],
					'PROJECT' => $item['UF_PROJECT'],
					'CATEGORY' => $item['UF_CATEGORY'],
					'TYPE' => $item['UF_CREATE_TYPE'],
					'BASE' => $item['UF_BASE'],
					'BASE_ARRAY' => $base,
					'BASE_COL_COUNT' => $item['UF_BASE_COL_COUNT'],
					'TS' => $item['UF_TS'],
					'AD_COUNT' => $item['UF_AD_COUNT'],
					'WORDSTAT' => $item['UF_WORDSTAT'],
					'WORDSTAT_TS' => $item['UF_WORDSTAT_TS'],
					'MARKS' => $item['UF_MARK'],
					'TEMPLATES' => $item['UF_TEMPLATE'],
					'DATA_ORIG' => $item['UF_DATA'],
					'DATA' => json_decode($item['UF_DATA'], true),
				);
			}

			if (!$params['limit'] || $page == 1 && count($return['ITEMS']) < $params['limit'])
			{
				$return['NAV'] = array(
					'ITEMS_COUNT' => count($return['ITEMS']),
					'CURRENT_PAGE' => 1,
					'PAGE_COUNT' => 1,
				);
			}
			else
			{
				$rsCount = $dataClass::getList([
					'select' => array(new ExpressionField('CNT', 'COUNT(*)')),
					'filter' => $params['filter'],
				]);
				$itemsCount = $rsCount->fetch()['CNT'];
				$return['NAV'] = array(
					'ITEMS_COUNT' => $itemsCount,
					'CURRENT_PAGE' => $page > 1 ? $page : 1,
					'PAGE_COUNT' => ceil($itemsCount / $params['limit']),
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getById($id, $categoryId, $projectId)
	{
		$res = self::getList($projectId, $categoryId, array(
			'filter' => array(
				'ID' => $id,
			),
		));
		return $res['ITEMS'][$id];
	}

	public static function getHref($category, $keygroup)
	{
		$categoryHref = Category::getHref($category);
		return $categoryHref . self::URL . '/' . $keygroup['ID'] . '/';
	}

	public static function add($projectId, $categoryId, $keyword, $base, $baseCount, $type)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::add(array(
			'UF_NAME' => $keyword,
			'UF_PROJECT' => $projectId,
			'UF_CATEGORY' => $categoryId,
			'UF_CREATE_TYPE' => $type,
			'UF_BASE' => $base,
			'UF_BASE_COL_COUNT' => $baseCount,
		    'UF_TS' => time(),
		    'UF_AD_COUNT' => 0,
		    'UF_WORDSTAT' => -1,
		    'UF_WORDSTAT_TS' => 0,
		    'UF_DATA' => '{}',
		));
	}

	public static function updateBaseType($keygroupId, $base, $baseCount, $type)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::update($keygroupId, array(
			'UF_CREATE_TYPE' => $type,
			'UF_BASE' => $base,
			'UF_BASE_COL_COUNT' => $baseCount,
		));
	}

	public static function deactivate($keygroupId)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::update($keygroupId, array(
			'UF_CREATE_TYPE' => self::TYPE_DEACTIVE,
		));
	}

	public static function update($keygroup, $newKeygroup)
	{
		$update = array();
		if (isset($newKeygroup['NAME']) && $newKeygroup['NAME'] != $keygroup['NAME'])
			$update['UF_NAME'] = $newKeygroup['NAME'];

		// Метки
		if (isset($newKeygroup['MARKS']))
		{
			$ex = array();
			$new = array();
			$needUpdate = false;
			foreach ($keygroup['MARKS'] as $mark)
				$ex[$mark] = true;
			foreach ($newKeygroup['MARKS'] as $mark)
			{
				$new[$mark] = true;
				if ($ex[$mark])
					unset($ex[$mark]);
				else
					$needUpdate = true;
			}
			if ($ex)
				$needUpdate = true;

			if ($needUpdate)
				$update['UF_MARK'] = array_keys($new);
		}
		// Шаблоны
		if (isset($newKeygroup['TEMPLATES']))
		{
			$ex = array();
			$new = array();
			$needUpdate = false;
			foreach ($keygroup['TEMPLATES'] as $templ)
				$ex[$templ] = true;
			foreach ($newKeygroup['TEMPLATES'] as $templ)
			{
				$new[$templ] = true;
				if ($ex[$templ])
					unset($ex[$templ]);
				else
					$needUpdate = true;
			}
			if ($ex)
				$needUpdate = true;

			if ($needUpdate)
				$update['UF_TEMPLATE'] = array_keys($new);
		}
		if ($newKeygroup['DATA'])
		{
			$newData = $keygroup['DATA'];
			foreach ($newKeygroup['DATA'] as $key => $value)
				$newData[$key] = $value;

			$encoded = json_encode($newData, JSON_UNESCAPED_UNICODE);
			if ($keygroup['DATA_ORIG'] != $encoded)
				$update['UF_DATA'] = $encoded;
		}

		if ($update)
		{
			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$dataClass::update($keygroup['ID'], $update);

			self::clearCache($keygroup['PROJECT'], $keygroup['CATEGORY']);
			$keygroup = self::getById($keygroup['ID'], $keygroup['CATEGORY'], $keygroup['PROJECT']);
			$keygroup['UPDATED'] = true;
		}

		return $keygroup;
	}

	public static function delete($keygroupId)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::delete($keygroupId);
	}

	public static function addMark($keygroup, $mark)
	{
		foreach ($keygroup['MARKS'] as $m)
			if ($m == $mark)
				return false;

		$update['UF_MARK'] = $keygroup['MARKS'];
		$update['UF_MARK'][] = $mark;

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::update($keygroup['ID'], $update);

		return true;
	}

	public static function removeMark($keygroup, $mark)
	{

		$newMarks = array();
		$ex = false;
		foreach ($keygroup['MARKS'] as $m)
			if ($m == $mark)
				$ex = true;
			else
				$newMarks[] = $m;

		if (!$ex)
			return false;

		$update['UF_MARK'] = $newMarks;

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::update($keygroup['ID'], $update);

		return true;
	}

	public static function removeAllMark($keygroup)
	{
		if (!count($keygroup['MARKS']))
			return false;

		$update['UF_MARK'] = array();

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::update($keygroup['ID'], $update);

		return true;
	}

	/**
	 * Очищает кеш заданного проекта и категории
	 * @param int $projectId
	 * @param int $categoryId
	 */
	public static function clearCache($projectId, $categoryId = 0)
	{
		$path = self::CACHE_PATH . 'getList';
		if ($projectId)
			$path .= '/' . $projectId;
		if ($categoryId)
			$path .= '/' . $categoryId;
		$phpCache = new \CPHPCache();
		$phpCache->CleanDir($path);
	}

}
