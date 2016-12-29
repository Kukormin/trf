<?
namespace Local\System;

use Local\Main\User;

/**
 * Class Alerts Оповещения пользователей
 * @package Local\System
 */
class Alerts
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/System/Alerts/';

	/**
	 * ID инфоблока
	 */
	const IBLOCK_ID = 14;

	/**
	 * Возвращает все сообщения пользователя
	 * @param int $userId
	 * @param bool|false $bRefreshCache сбросить кеш
	 * @return array
	 */
	public static function getByUser($userId, $bRefreshCache = false)
	{
		$userId = intval($userId);

		$return = array();
		$extCache = new ExtCache(
			array(
				__FUNCTION__,
			    $userId,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			86400 * 20,
			false
		);
		if (!$bRefreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$iblockElement = new \CIBlockElement();
			$rsItems = $iblockElement->GetList(array('ID' => 'ASC'), array(
				'IBLOCK_ID' => self::IBLOCK_ID,
			    'CODE' => $userId,
			), false, false, array(
				'ID', 'NAME', 'ACTIVE', 'PREVIEW_TEXT', 'DATE_CREATE',
			));
			while ($item = $rsItems->Fetch())
			{
				$return[] = array(
					'ID' => intval($item['ID']),
					'ACTIVE' => $item['ACTIVE'] =='Y',
					'NAME' => $item['NAME'],
				    'TEXT' => $item['PREVIEW_TEXT'],
				    'DATE' => $item['DATE_CREATE'],
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function get()
	{
		$userId = User::getCurrentUserId();
		if (!$userId)
			return false;

		return self::getByUser($userId);
	}

	/**
	 * Возвращает активные сообщения пользователя
	 * @return array
	 */
	public static function getActive()
	{
		$return = array();
		$all = self::get();
		foreach ($all as $item)
		{
			if ($item['ACTIVE'])
				$return[] = $item;
		}
		return $return;
	}

	/**
	 * Добавляет сообщение
	 * @param $userId
	 * @param $name
	 * @param $text
	 * @return bool
	 */
	public static function add($userId, $name, $text)
	{
		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $name,
			'CODE' => $userId,
		    'PREVIEW_TEXT' => $text,
		));

		self::getByUser($userId, true);

		return $id;
	}

	/**
	 * Деактивирует сообщение
	 * @param $id
	 */
	public static function deactivate($id)
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->Update($id, array('ACTIVE' => 'N'));
	}

	/**
	 * Закрывает сообщение
	 * @param $id
	 * @return bool
	 */
	public static function close($id)
	{
		$userId = User::getCurrentUserId();
		if (!$userId)
			return false;

		$all = self::getByUser($userId);
		foreach ($all as $item)
			if ($item['ID'] == $id)
			{
				self::deactivate($id);
				self::getByUser($userId, true);
				return true;
			}

		return false;
	}

	public static function getHtml($alert)
	{
		ob_start();

		?>
		<div class="alert-box" id="alert_<?= $alert['ID'] ?>" data-id="<?= $alert['ID'] ?>">
			<div class="close">
				<span></span>
			</div>
			<div class="content">
				<div class="head"><?= $alert['DATE'] ?> <b><?= $alert['NAME'] ?></b></div>
				<div class="body"><?= $alert['TEXT'] ?></div>
			</div>
		</div><?

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

}
