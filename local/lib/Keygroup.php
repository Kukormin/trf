<?
namespace Local;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;

/**
 * Группа, содержащая ключевое слово и от одного до нескольких объявлений
 */
class Keygroup
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Keygroup/';

	/**
	 * ID HL-блока
	 */
	const ENTITY_ID = 3;

	/**
	 * сколько секунд фраза считается новой
	 */
	const NEW_TS = 7200;

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
			            data-content="1) сгенерирована из базовых слов, но база поменялась; 2) была задана вручную, но потом деактивирована"></i>',
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
		    'VALUE' => array(),
	    ),
	    'target' => array(
		    'NAME' => 'Целевая',
		    'TYPE' => 'radio',
		    'VARS' => array(
			    '0' => 'не важно',
			    '1' => 'целевая',
			    '2' => 'околоцелевая',
		    ),
		    'VALUE' => '0',
	    ),
	    'sort' => array(
		    'NAME' => 'Сортировать:',
		    'TYPE' => 'select',
		    'VARS' => array(
			    'ida' => 'по дате создания (сначала старые)',
			    'idd' => 'по дате создания (сначала новые)',
			    'wsa' => 'по возрастанию частотности',
			    'wsd' => 'по убыванию частотности',
		    ),
		    'VALUE' => '0',
	    ),
	    'size' => array(
		    'NAME' => 'Элементов на странице:',
		    'TYPE' => 'select',
		    'VARS' => array(
			    '20' => '20',
			    '50' => '50',
			    '100' => '100',
			    '500' => '500',
			    'all' => 'все',
		    ),
		    'VALUE' => '20',
	    ),
	);

	public static function getFilters()
	{
		$return = array();
		foreach (self::$FILTER_SETTINGS as $code => $item)
		{
			if ($item['TYPE'] == 'text')
			{
				$item['VALUE'] = htmlspecialchars($_REQUEST[$code]);
			}
			elseif ($item['TYPE'] == 'checkbox')
			{
				foreach ($item['VARS'] as $k => $v)
				{
					if (in_array($k, $_REQUEST[$code]))
						$item['VALUE'][$k] = true;
				}
			}
			else
			{
				if (isset($item['VARS'][$_REQUEST[$code]]))
					$item['VALUE'] = $_REQUEST[$code];
			}
			$return[$code] = $item;
		}
		return $return;
	}

	public static function getParamsForGetList()
	{
		$filter = array();
		$order = array();

		$filters = self::getFilters();
		$ts = time();

		if ($filters['txt']['VALUE'])
			$filter['UF_NAME'] = '%' . $filters['txt']['VALUE'] . '%';

		if ($filters['ad_count']['VALUE'] == 1)
			$filter['=UF_AD_COUNT'] = 0;
		elseif ($filters['ad_count']['VALUE'] == 2)
			$filter['=UF_AD_COUNT'] = 1;
		elseif ($filters['ad_count']['VALUE'] == 3)
			$filter['>UF_AD_COUNT'] = 1;

		if ($filters['ws']['VALUE'] == 1)
			$filter['=UF_WORDSTAT'] = -1;
		elseif ($filters['ws']['VALUE'] == 2)
			$filter['=UF_WORDSTAT'] = 0;
		elseif ($filters['ws']['VALUE'] == 3)
			$filter['>UF_WORDSTAT'] = 0;

		if ($filters['new']['VALUE'] == 1)
			$filter['>=UF_TS'] = $ts - self::NEW_TS;
		elseif ($filters['new']['VALUE'] == 2)
			$filter['<UF_TS'] = $ts - self::NEW_TS;

		if (!$filters['base']['VALUE'][1] || !$filters['base']['VALUE'][2] || !$filters['base']['VALUE'][3])
		{
			if ($filters['base']['VALUE'][1] && $filters['base']['VALUE'][2])
				$filter['!UF_BASE'] = -1;
			elseif ($filters['base']['VALUE'][1] && $filters['base']['VALUE'][3])
				$filter['!UF_BASE'] = -2;
			elseif ($filters['base']['VALUE'][2] && $filters['base']['VALUE'][3])
				$filter['<UF_BASE'] = 0;
			elseif ($filters['base']['VALUE'][1])
				$filter['>UF_BASE'] = 0;
			elseif ($filters['base']['VALUE'][2])
				$filter['=UF_BASE'] = -2;
			elseif ($filters['base']['VALUE'][3])
				$filter['=UF_BASE'] = -1;
		}

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

		if ($filters['target']['VALUE'] == 1)
			$filter['=UF_TARGET'] = 1;
		elseif ($filters['target']['VALUE'] == 2)
			$filter['!=UF_TARGET'] = 1;

		if ($filters['sort']['VALUE'] == 'ida')
			$order['ID'] = 'ASC';
		elseif ($filters['sort']['VALUE'] == 'idd')
			$order['ID'] = 'DESC';
		elseif ($filters['sort']['VALUE'] == 'wsa')
			$order['UF_WORDSTAT'] = 'ASC';
		elseif ($filters['sort']['VALUE'] == 'wsd')
			$order['UF_WORDSTAT'] = 'DESC';

		$return = array(
			'filter' => $filter,
			'order' => $order,
		);

		if ($filters['size']['VALUE'] != 'all')
		{
			$return['limit'] = $filters['size']['VALUE'];
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
				$return['ITEMS'][$id] = array(
					'ID' => $id,
					'NAME' => $item['UF_NAME'],
					'PROJECT' => $item['UF_PROJECT'],
					'CATEGORY' => $item['UF_CATEGORY'],
					'BASE' => $item['UF_BASE'],
					'BASE_COL_COUNT' => $item['UF_BASE_COL_COUNT'],
					'TARGET' => $item['UF_TARGET'],
					'TS' => $item['UF_TS'],
					'AD_COUNT' => $item['UF_AD_COUNT'],
					'WORDSTAT' => $item['UF_WORDSTAT'],
					'WORDSTAT_TS' => $item['UF_WORDSTAT_TS'],
					'DATA_ORIG' => $item['UF_DATA'],
					'DATA' => json_decode($item['UF_DATA'], true),
				);
			}

			$rsCount = $dataClass::getList([
				'select' => array(new ExpressionField('CNT', 'COUNT(*)')),
				'filter' => $params['filter'],
			]);
			$itemsCount = $rsCount->fetch()['CNT'];
			$return['NAV'] = array(
				'ITEMS_COUNT' => $itemsCount,
				'CURRENT_PAGE' => $page > 1 ? $page : 1,
				'PAGE_SIZE' => $params['limit'],
			    'PAGE_COUNT' => ceil($itemsCount / $params['limit']),
			);

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getById($id)
	{
		//$projects = self::getList(1);
		return $projects[$id];
	}

	public static function add($projectId, $categoryId, $keyword, $base, $baseCount)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::add(array(
			'UF_NAME' => $keyword,
			'UF_PROJECT' => $projectId,
			'UF_CATEGORY' => $categoryId,
			'UF_BASE' => $base,
			'UF_BASE_COL_COUNT' => $baseCount,
			'UF_TARGET' => 1,
		    'UF_TS' => time(),
		    'UF_AD_COUNT' => 0,
		    'UF_WORDSTAT' => -1,
		    'UF_WORDSTAT_TS' => 0,
		    'UF_DATA' => '{}',
		));
	}

	public static function delete($id)
	{
	}

	public static function updateBase($keygroupId, $base, $baseCount)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::update($keygroupId, array(
			'UF_BASE' => $base,
			'UF_BASE_COL_COUNT' => $baseCount,
		));
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
