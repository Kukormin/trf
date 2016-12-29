<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Query;
use Local\System\ExtCache;
use Local\Yandex\Wordstat;

/**
 * Категория - рекламируемая страница или группа страниц (карточки товаров)
 */
class Category
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/Category/';

	/**
	 * ID HL-блока с категориями
	 */
	const ENTITY_ID = 2;

	/**
	 * Ключ в урле
	 */
	const URL = 'c';

	private static $TARGETS = array(
		4 => 'near',
	    3 => 'target',
	);

	public static function getByProject($projectId, $refreshCache = false)
	{
		$projectId = intval($projectId);
		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$projectId,
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

			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);

			$query = new Query($entity);
			$query->setFilter(array('UF_PROJECT' => $projectId));
			$query->setSelect(array('*'));
			$return = array();
			$rsItems = $query->exec();
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return[$id] = array(
					'ID' => $id,
					'NAME' => $item['UF_NAME'],
					'PROJECT' => intval($item['UF_PROJECT']),
					'IS_YANDEX' => $item['UF_YANDEX'],
					'IS_SEARCH' => $item['UF_SEARCH'],
					'TARGET' => self::$TARGETS[$item['UF_TARGET']] ? self::$TARGETS[$item['UF_TARGET']] : '',
					'DATA_ORIG' => $item['UF_DATA'],
					'DATA' => json_decode($item['UF_DATA'], true),
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getById($id, $projectId)
	{
		$all = self::getByProject($projectId);
		return $all[$id];
	}

	public static function getNewHref($projectId)
	{
		return Project::getHref($projectId) . self::URL . '/new/';
	}

	public static function getHref($category)
	{
		return Project::getHref($category['PROJECT']) . self::URL . '/' . $category['ID'] . '/';
	}

	public static function add($name, $projectId)
	{
		$data = array(
			'UF_NAME' => $name,
			'UF_PROJECT' => $projectId,
		    'UF_DATA' => '{"NEW":true}',
		);
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$return = $dataClass::add($data);
		$id = $return->getId();

		self::getByProject($projectId, true);
		$category = self::getById($id, $projectId);
		$category['NEW'] = true;

		return $category;
	}

	public static function delete($id)
	{
	}

	public static function update($category, $newCategory)
	{
		$update = array();
		if (isset($newCategory['NAME']) && $newCategory['NAME'] != $category['NAME'])
			$update['UF_NAME'] = $newCategory['NAME'];
		if ($newCategory['DATA'])
		{
			$newData = $category['DATA'];
			foreach ($newCategory['DATA'] as $key => $value)
				$newData[$key] = $value;

			$encoded = json_encode($newData, JSON_UNESCAPED_UNICODE);
			if ($category['DATA_ORIG'] != $encoded)
				$update['UF_DATA'] = $encoded;
		}

		if ($update)
		{
			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$dataClass::update($category['ID'], $update);

			self::getByProject($category['PROJECT'], true);
			$category = self::getById($category['ID'], $category['PROJECT']);
			$category['UPDATED'] = true;
		}

		return $category;
	}

	/**
	 * Формирует фразу из базовых слов
	 * @param $item
	 * @return string
	 */
	public static function getWordFromBase($item)
	{
		$word = '';
		foreach ($item as $w)
		{
			if (!$w)
				continue;
			if ($word)
				$word .= ' ';
			$word .= $w;
		}
		return $word;
	}

	private static function getBaseStat($item)
	{
		$return = array(
			'KW' => self::getWordFromBase($item),
			'KEY' => '',
			'CNT' => 0,
		);
		if ($return['KW'])
		{
			foreach ($item as $i => $word)
				if ($word)
				{
					if ($return['KEY'])
						$return['KEY'] .= '|';
					$return['KEY'] .= $i . '#' . $word;
					$return['CNT']++;
				}
		}

		return $return;
	}

	/**
	 * Рекурсивный метод перебора слов для составления фраз на основе базовых слов
	 * @param $key
	 * @param $level
	 * @param $cnt
	 * @param $words
	 * @param $max
	 * @param $res
	 */
	private static function f($key, $level, $cnt, $words, $max, &$res)
	{
		if ($cnt > $max)
			return;

		if ($level < count($words))
			foreach ($words[$level] as $i => $s)
			{
				$key[$level] = $s;
				self::f($key, $level + 1, $cnt + ($s ? 1 : 0), $words, $max, $res);
			}
		else
			$res[] = $key;
	}

	/**
	 * Комбинирует базовые слова категории
	 * @param $category
	 * @return array
	 */
	public static function combo($category)
	{
		$words = array();
		foreach ($category['DATA']['BASE'] as $k => $item)
		{
			$col = array();
			foreach ($item['WORDS'] as $word)
			{
				$word = trim($word);
				if ($word)
					$col[$word] = $word;
			}
			if ($col)
			{
				$words[$k] = array_values($col);
				array_unshift($words[$k], '');
				if ($item['REQ'])
					unset($words[$k][0]);
			}
		}

		$max = intval($category['DATA']['BASE_MAX']);
		if ($max < 1)
			$max = 4;

		// Результаты комбинирования базовых слов
		$res = array();
		self::f(array(), 0, 0, $words, $max, $res);

		return $res;
	}

	/**
	 * Возвращает результат комбинирования базовых слов со статистикой
	 * @param $category
	 * @return array
	 */
	public static function comboReport($category)
	{
		$res = self::combo($category);

		// существующие в категории ключевые слова
		$keygroups = Keygroup::getList($category['PROJECT'], $category['ID']);
		$byName = array();
		foreach ($keygroups['ITEMS'] as $kg)
			$byName[$kg['NAME']] = $kg['ID'];

		$result = array(
			'GENERATE' => 0,
		    'CURRENT' => count($byName),
		    'EX_BASE' => 0,
		    'EX_ADD' => 0,
		    'NEW' => 0,
		    'ITEMS' => array(),
		);

		$ws = new Wordstat($category['ID']);

		foreach ($res as $item)
		{
			$base = self::getBaseStat($item);
			if (!$base['CNT'])
				continue;

			$id = intval($byName[$base['KW']]);
			$item = array(
				'KW' => $base['KW'],
				'BASE' => $base['KEY'],
			    'ID' => $id,
			    'WS' => $ws->getValue($base['KW']),
			);
			if ($id)
			{
				$kg = $keygroups['ITEMS'][$id];
				if ($base['KEY'] == $kg['BASE'])
					$item['TYPE'] = 'base';
				elseif (Keygroup::TYPE_BASE != $kg['TYPE'])
					$item['TYPE'] = 'add';
			}
			else
				$item['TYPE'] = 'new';

			$result['ITEMS'][] = $item;
			if ($item['TYPE'] == 'base')
				$result['EX_BASE']++;
			elseif ($item['TYPE'] == 'add')
				$result['EX_ADD']++;
			elseif ($item['TYPE'] == 'new')
				$result['NEW']++;
			$result['GENERATE']++;
		}

		return $result;
	}

	/**
	 * Добавляет в категорию отмеченные фразы
	 * @param $category
	 * @param $checked
	 */
	public static function comboAdd($category, $checked)
	{
		$res = self::combo($category);

		$clearCache = false;
		// существующие в категории ключевые слова
		$keygroups = Keygroup::getList($category['PROJECT'], $category['ID']);
		$byName = array();
		foreach ($keygroups['ITEMS'] as $kg)
			$byName[$kg['NAME']] = $kg['ID'];

		foreach ($res as $item)
		{
			$base = self::getBaseStat($item);
			if (!$base['CNT'])
				continue;

			if (!$checked[$base['KEY']])
				continue;

			$id = $byName[$base['KW']];
			if ($id)
				Keygroup::updateBaseType($id, $base['KEY'], $base['CNT'], Keygroup::TYPE_BASE);
			else
				Keygroup::add($category['PROJECT'], $category['ID'], $base['KW'], $base['KEY'], $base['CNT'],
					Keygroup::TYPE_BASE);

			$clearCache = true;
		}

		if ($clearCache)
			Keygroup::clearCache($category['PROJECT'], $category['ID']);
	}

	public static function wordstatTask($data)
	{
		$category = self::getById($data['CATEGORY'], $data['PROJECT']);
		if (!$category)
			return false;

		if ($data['TYPE'] == 'combo')
		{
			$items = self::combo($category);
			$ws = new Wordstat($category['ID']);
			$ws->checkBaseWords($items);
		}

		$href = self::getHref($category);
		$text = 'Проверка частотности завершена. Перейти в <a href="' . $href . '">категорию</a>';

		return $text;
	}

	public static function additionalWords($category, $add, $deactive)
	{
		// существующие в категории ключевые слова
		$keygroups = Keygroup::getList($category['PROJECT'], $category['ID']);
		$byName = array();
		foreach ($keygroups['ITEMS'] as $kg)
			$byName[$kg['NAME']] = $kg['ID'];

		$result = array(
			'NEW' => 0,
			'EX' => count($byName),
			'ADD' => 0,
			'ACTIV' => 0,
			'DELETE' => 0,
			'BASE' => 0,
			'NO' => 0,
			'USER' => 0,
			'OLD' => 0,
		    'WORDS' => array(),
		);

		$clearCache = false;
		foreach ($add as $kw)
		{
			$id = $byName[$kw];
			if ($id)
			{
				$kg = $keygroups['ITEMS'][$id];
				if ($kg['TYPE'] == Keygroup::TYPE_MANUAL)
				{
					$result['NO']++;
					$result['WORDS'][] = $kw;
					unset($byName[$kw]);
				}
				elseif ($kg['TYPE'] == Keygroup::TYPE_DEACTIVE)
				{
					Keygroup::updateBaseType($id, '', 0, Keygroup::TYPE_MANUAL);
					$clearCache = true;
					$result['ACTIV']++;
					$result['WORDS'][] = $kw;
					unset($byName[$kw]);
				}
				else
				{
					$result['BASE']++;
				}
			}
			else
			{
				Keygroup::add($category['PROJECT'], $category['ID'], $kw, '', 0, Keygroup::TYPE_MANUAL);
				$clearCache = true;
				$result['ADD']++;
				$result['WORDS'][] = $kw;
			}
		}

		foreach ($byName as $id)
		{
			$kg = $keygroups['ITEMS'][$id];
			if ($kg['TYPE'] == Keygroup::TYPE_MANUAL)
			{
				if ($deactive) {
					Keygroup::delete($id);
					$clearCache = true;
					$result['DELETE']++;
				}
				else
				{
					$result['WORDS'][] = $kg['NAME'];
					$result['USER']++;
				}
			}
			elseif ($kg['TYPE'] == Keygroup::TYPE_DEACTIVE)
				$result['OLD']++;
			else
				$result['NEW']++;
		}

		if ($clearCache)
			Keygroup::clearCache($category['PROJECT'], $category['ID']);

		return $result;
	}

}
