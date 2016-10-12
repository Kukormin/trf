<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Local\System\ExtCache;

/**
 * Наборы быстрых ссылок
 */
class Linkset
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/Linkset/';

	/**
	 * ID HL-блока
	 */
	const ENTITY_ID = 5;

	/**
	 * Ключ в урле
	 */
	const URL = 'links';

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
			$dataClass = $entity->getDataClass();
			$rsItems = $dataClass::getList(array(
				'filter' => array(
					'UF_PROJECT' => $projectId,
				),
			));
			$return = array();
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return[$id] = array(
					'ID' => $id,
					'NAME' => $item['UF_NAME'],
					'PROJECT' => intval($item['UF_PROJECT']),
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

	public static function getListHref($projectId)
	{
		return Project::getHref($projectId) . self::URL . '/';
	}

	public static function getAddHref($projectId)
	{
		return self::getListHref($projectId) . 'new/';
	}

	public static function getHref($set)
	{
		return self::getListHref($set['PROJECT']) . $set['ID'] . '/';
	}

	public static function add($newSet)
	{
		$projectId = $newSet['PROJECT'];
		$data = array();
		$data['UF_NAME'] = $newSet['NAME'];
		$data['UF_PROJECT'] = $projectId;
		$data['UF_DATA'] = json_encode($newSet['DATA'], JSON_UNESCAPED_UNICODE);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		self::getByProject($projectId, true);
		$set = self::getById($id, $projectId);
		$set['NEW'] = true;
		return $set;
	}

	public static function delete($set)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::delete($set['ID']);

		self::getByProject($set['PROJECT'], true);
	}

	public static function update($set, $newSet)
	{
		$update = array();
		if (isset($newSet['NAME']) && $newSet['NAME'] != $set['NAME'])
			$update['UF_NAME'] = $newSet['NAME'];
		if ($newSet['DATA'])
		{
			$newData = $set['DATA'];
			foreach ($newSet['DATA'] as $key => $value)
				$newData[$key] = $value;

			$encoded = json_encode($newData, JSON_UNESCAPED_UNICODE);
			if ($set['DATA_ORIG'] != $encoded)
				$update['UF_DATA'] = $encoded;
		}

		if ($update)
		{
			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$dataClass::update($set['ID'], $update);

			self::getByProject($set['PROJECT'], true);
			$set = self::getById($set['ID'], $set['PROJECT']);
			$set['UPDATED'] = true;
		}

		return $set;
	}

}
