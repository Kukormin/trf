<?
namespace Local;

use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Шаблоны объявлений
 */
class Templ
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Template/';

	/**
	 * ID HL-блока
	 */
	const ENTITY_ID = 7;

	/**
	 * Ключ в урле
	 */
	const URL = 'templates';

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

	public static function getHref($templ)
	{
		return self::getListHref($templ['PROJECT']) . $templ['ID'] . '/';
	}

	public static function add($newTempl)
	{
		$projectId = $newTempl['PROJECT'];
		$data = array();
		$data['UF_NAME'] = $newTempl['NAME'];
		$data['UF_PROJECT'] = $projectId;
		$data['UF_DATA'] = json_encode($newTempl['DATA'], JSON_UNESCAPED_UNICODE);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		self::getByProject($projectId, true);
		$template = self::getById($id, $projectId);
		$template['NEW'] = true;
		return $template;
	}

	public static function delete($templ)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::delete($templ['ID']);

		self::getByProject($templ['PROJECT'], true);
	}

	public static function update($templ, $newTempl)
	{
		$update = array();
		if (isset($newTempl['NAME']) && $newTempl['NAME'] != $templ['NAME'])
			$update['UF_NAME'] = $newTempl['NAME'];
		if ($newTempl['DATA'])
		{
			$newData = $templ['DATA'];
			foreach ($newTempl['DATA'] as $key => $value)
				$newData[$key] = $value;

			$encoded = json_encode($newData, JSON_UNESCAPED_UNICODE);
			if ($templ['DATA_ORIG'] != $encoded)
				$update['UF_DATA'] = $encoded;
		}

		if ($update)
		{
			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$dataClass::update($templ['ID'], $update);

			self::getByProject($templ['PROJECT'], true);
			$templ = self::getById($templ['ID'], $templ['PROJECT']);
			$templ['UPDATED'] = true;
		}

		return $templ;
	}

}
