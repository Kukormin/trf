<?
namespace Local;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Query;

/**
 * Категория - рекламируемая страница или группа страниц (карточки товаров)
 */
class Category
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Category/';

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
	 * Рекурсивный метод перебора слов для составления фраз на основе базовых слов
	 * @param $prev
	 * @param $key
	 * @param $level
	 * @param $cnt
	 * @param $words
	 * @param $max
	 * @param $res
	 */
	private static function f($prev, $key, $level, $cnt, $words, $max, &$res)
	{
		if ($max > 0 && $cnt > $max)
			return;

		if ($level < count($words))
			foreach ($words[$level] as $i => $s)
			{
				$key[$level] = $i;
				if ($s)
				{
					$pre = $prev . ' ' . $s;
					self::f($pre, $key, $level + 1, $cnt + 1, $words, $max, $res);
				}
				else
				{
					self::f($prev, $key, $level + 1, $cnt, $words, $max, $res);
				}
			}
		else
		{
			$prev = trim($prev);
			if ($prev)
				$res[] = array($prev, $key);
		}

	}

	/**
	 * На основании базовых слов категории формирует ключевые фразы
	 * @param $category
	 * @return array
	 */
	public static function combo($category)
	{
		$words = array();
		foreach ($category['DATA']['BASE'] as $k => $item)
		{
			$words[$k] = $item['WORDS'];
			array_unshift($words[$k], '');
			if ($item['REQ'])
				unset($words[$k][0]);
		}

		$max = intval($category['DATA']['BASE_MAX']);
		if (!$max)
			$max = 4;

		// Результаты комбинирования базовых слов
		$res = array();
		self::f('', array(), 0, 0, $words, $max, $res);

		$clearCache = false;
		// существующие в категории ключевые слова
		$keygroups = Keygroup::getList($category['PROJECT'], $category['ID']);
		$byName = array();
		foreach ($keygroups['ITEMS'] as $kg)
			$byName[$kg['NAME']] = $kg['ID'];

		$result = array(
			'NEW' => count($res),
		    'EX' => count($byName),
		    'ADD' => 0,
		    'ACTIV' => 0,
		    'DEACTIV' => 0,
		    'TO_BASE' => 0,
		    'NO' => 0,
		    'USER' => 0,
		    'OLD' => 0,
		);

		foreach ($res as $item)
		{
			$kw = $item[0];
			$cols = $item[1];
			$base = implode(',', $cols);
			$baseCount = 0;
			foreach ($cols as $c)
				if ($c)
					$baseCount++;

			$id = $byName[$kw];
			if ($id)
			{
				unset($byName[$kw]);
				$kg = $keygroups['ITEMS'][$id];
				if ($base != $kg['BASE'])
				{
					Keygroup::updateBase($id, $base, $baseCount);
					$clearCache = true;
					if ($kg['BASE'] == '-1')
						$result['TO_BASE']++;
					elseif ($kg['BASE'] == '-2')
						$result['ACTIVE']++;
					else
						$result['NO']++;
				}
				else
					$result['NO']++;
			}
			else
			{
				Keygroup::add($category['PROJECT'], $category['ID'], $kw, $base, $baseCount);
				$clearCache = true;
				$result['ADD']++;
			}
		}

		foreach ($byName as $id)
		{
			$kg = $keygroups['ITEMS'][$id];
			if ($kg['BASE'] == '-1')
			{
				$result['NO']++;
				$result['USER']++;
			}
			elseif ($kg['BASE'] == '-2')
			{
				$result['NO']++;
				$result['OLD']++;
			}
			else
			{
				Keygroup::updateBase($id, '-2', '-2');
				$clearCache = true;
				$result['DEACTIV']++;
				$result['OLD']++;
			}
		}

		if ($clearCache)
			Keygroup::clearCache($category['PROJECT'], $category['ID']);

		return $result;
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
			'DEACTIV' => 0,
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
				if ($kg['BASE'] == '-1')
				{
					$result['NO']++;
					$result['WORDS'][] = $kw;
					unset($byName[$kw]);
				}
				elseif ($kg['BASE'] == '-2')
				{
					Keygroup::updateBase($id, '-1', '-1');
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
				Keygroup::add($category['PROJECT'], $category['ID'], $kw, '-1', '-1');
				$clearCache = true;
				$result['ADD']++;
				$result['WORDS'][] = $kw;
			}
		}

		foreach ($byName as $id)
		{
			$kg = $keygroups['ITEMS'][$id];
			if ($kg['BASE'] == '-1')
			{
				if ($deactive) {
					Keygroup::updateBase($id, '-2', '-2');
					$clearCache = true;
					$result['DEACTIV']++;
					$result['OLD']++;
				}
				else
				{
					$result['WORDS'][] = $kg['NAME'];
					$result['USER']++;
				}
			}
			elseif ($kg['BASE'] == '-2')
				$result['OLD']++;
			else
				$result['NEW']++;
		}

		if ($clearCache)
			Keygroup::clearCache($category['PROJECT'], $category['ID']);

		return $result;
	}

}
