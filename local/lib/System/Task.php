<?
namespace Local\System;

use Local\Main\Category;
use Local\Main\User;

/**
 * Class Task Длительные задачи
 * @package Local\System
 */
class Task
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/System/Task/';

	/**
	 * ID инфоблока
	 */
	const IBLOCK_ID = 13;

	public static function check()
	{
		$all = self::getAll();
		$item = $all[0];
		if ($item && !$item['PROCESS'])
		{
			self::start($item['ID']);
			$text = '';
			if ($item['NAME'] == 'cws')
				$text = Category::wordstatTask($item['DATA']);
			self::complete($item['ID']);
			if ($item['USER'])
				Alerts::add($item['USER'], 'Задача выполнена', $text);
		}
	}

	/**
	 * Возвращает все задачи
	 * @param bool|false $bRefreshCache сбросить кеш
	 * @return array
	 */
	public static function getAll($bRefreshCache = false)
	{
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			86400 * 20
		);
		if (!$bRefreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$iblockElement = new \CIBlockElement();
			$rsItems = $iblockElement->GetList(array('ID' => 'ASC'), array(
				'IBLOCK_ID' => self::IBLOCK_ID,
			    'ACTIVE' => 'Y',
			), false, false, array(
				'ID', 'NAME', 'CODE', 'XML_ID', 'DETAIL_TEXT',
			));
			while ($item = $rsItems->Fetch())
			{
				$data = json_decode($item['DETAIL_TEXT'], true);
				$return[] = array(
					'ID' => intval($item['ID']),
					'USER' => intval($item['CODE']),
					'NAME' => $item['NAME'],
				    'DATA' => $data,
				    'PROCESS' => $item['XML_ID'] == 'p',
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает первую задачу пользователя
	 * @param $userId
	 * @return bool
	 */
	public static function getByUser($userId)
	{
		$all = self::getAll();
		foreach ($all as $item)
		{
			if ($item['USER'] == $userId)
				return $item['ID'];
		}
		return false;
	}

	/**
	 * Возвращает первую задачу текущего пользователя
	 * @return bool
	 */
	public static function getByCurrentUser()
	{
		$userId = User::getCurrentUserId();
		if (!$userId)
			return false;

		return self::getByUser($userId);
	}

	/**
	 * Добавляет задачу
	 * @param $name
	 * @param array $data
	 * @param int $userId
	 * @return bool
	 */
	public static function add($name, $data = array(), $userId = 0)
	{
		if ($userId)
		{
			$task = self::getByUser($userId);
			if ($task)
				return false;
		}

		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $name,
			'CODE' => $userId,
		    'DETAIL_TEXT' => json_encode($data, JSON_UNESCAPED_UNICODE),
		));

		self::getAll(true);

		return $id;
	}

	public static function addCategoryWordstat($categoryId, $projectId, $type)
	{
		$userId = User::getCurrentUserId();
		if (!$userId)
			return false;

		$id = self::add('cws', array(
			'CATEGORY' => $categoryId,
			'PROJECT' => $projectId,
			'TYPE' => $type,
		), $userId);

		return $id;
	}

	/**
	 * Старт выполнения задачи
	 * @param $id
	 */
	public static function start($id)
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->Update($id, array('XML_ID' => 'p'));
		self::getAll(true);
	}

	/**
	 * Задача выполнена (деактивация элемента)
	 * @param $id
	 */
	public static function complete($id)
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->Update($id, array('ACTIVE' => 'N'));
		self::getAll(true);
	}

}
