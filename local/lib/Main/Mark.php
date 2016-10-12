<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Class Mark Метки для ключевых групп
 * @package Local
 */
class Mark
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/Mark/';

	/**
	 * ID инфоблока с метками
	 */
	const IBLOCK_ID = 11;

	/**
	 * Ключ в урле
	 */
	const URL = 'mark';

	public static function getByCurrentUser($refreshCache = false)
	{
		$return = array();
		$userId = User::getCurrentUserId();
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

			$iblockElement = new \CIBlockElement();
			$rsItems = $iblockElement->GetList(array(), array(
				'IBLOCK_ID' => self::IBLOCK_ID,
				'XML_ID' => $userId,
			), false, false, array(
				'ID', 'NAME', 'CODE',
			));
			while ($item = $rsItems->Fetch())
			{
				$bg = $item['CODE'] ? '#' . $item['CODE'] : 'none';
				$return[$item['ID']] = array(
					'ID' => intval($item['ID']),
					'NAME' => $item['NAME'],
					'COLOR' => $item['CODE'],
				    'HTML' => '<i class="mark" title="' . $item['NAME'] . '" style="background:' . $bg . ';"></i>',
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getById($id, $refreshCache = false)
	{
		$items = self::getByCurrentUser($refreshCache);
		return $items[$id];
	}

	public static function getMarksHref()
	{
		return '/' . self::URL . '/';
	}

	public static function getNewHref()
	{
		return self::getMarksHref() . 'new/';
	}

	public static function getHref($id)
	{
		return self::getMarksHref() . $id . '/';
	}

	public static function add($name, $color)
	{
		$return = array();
		$userId = User::getCurrentUserId();
		if (!$userId)
			return $return;

		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $name,
			'CODE' => $color,
			'XML_ID' => $userId,
		));

		$mark = self::getById($id, true);
		$mark['NEW'] = true;

		return $mark;
	}

	public static function delete($id)
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->Delete($id);
	}

	public static function update($mark, $newMark)
	{
		if (!$mark['ID'])
			return false;

		$update = array();
		if ($mark['NAME'] != $newMark['NAME'])
			$update['NAME'] = $newMark['NAME'];
		if ($mark['COLOR'] != $newMark['COLOR'])
			$update['CODE'] = $newMark['COLOR'];

		if ($update)
		{
			$iblockElement = new \CIBlockElement();
			$iblockElement->Update($mark['ID'], $update);
			$mark['UPDATED'] = true;
		}

		return $mark;
	}

	public static function isNotEmpty() {
		$items = self::getByCurrentUser();
		return count($items) > 0;
	}

	public static function printDropdown()
	{
		?>
		<ul class="dropdown-menu"><?
			$items = self::getByCurrentUser();
			foreach ($items as $item)
			{
				?>
				<li>
				<a data-id="<?= $item['ID'] ?>" href="javascript:void(0)"><?= $item['NAME'] ?> <?=
					$item['HTML'] ?></a>
				</li><?
			}
			?>
		</ul><?
	}

}
