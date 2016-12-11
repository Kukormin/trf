<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Class View Виды отображения сводной таблицы
 * @package Local
 */
class View
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/View/';

	/**
	 * ID инфоблока
	 */
	const IBLOCK_ID = 12;

	/**
	 * Ключ в урле
	 */
	const URL = 'view';

	/**
	 * Набор стобцов
	 * @var array
	 */
	public static $COLUMNS = array(
		'cb' => array(
			'NAME' => '',
		    'TITLE' => 'Чекбокс выбора',
		    'REQUIRED' => true,
		),
		'name' => array(
			'NAME' => 'Ключевая фраза',
			'REQUIRED' => true,
		),
		'ws' => array(
			'NAME' => 'W',
		    'TITLE' => 'Частотность wordstat.yandex.ru',
		),
		'mark' => array(
			'NAME' => 'Метки',
		),
		'title' => array(
			'NAME' => 'Заголовки',
		),
		'text' => array(
			'NAME' => 'Текст',
		),
		'preview' => array(
			'NAME' => 'Предварительный просмотр',
		),
		'action' => array(
			'NAME' => 'Действия',
			'REQUIRED' => true,
		),
	);

	public static function getColumnByCode($code)
	{
		return self::$COLUMNS[$code];
	}

	public static function getByUserId($userId, $refreshCache = false)
	{
		$return = array();
		$userId = intval($userId);
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
				'ID', 'NAME', 'CODE', 'DETAIL_TEXT',
			));
			while ($item = $rsItems->Fetch())
			{
				$data = json_decode($item['DETAIL_TEXT'], true);
				$showAd = false;
				foreach ($data['COLUMNS'] as $col)
				{
					if ($col == 'title' || $col == 'text' || $col == 'preview')
					{
						$showAd = true;
						break;
					}
				}
				$return[$item['ID']] = array(
					'ID' => intval($item['ID']),
					'NAME' => $item['NAME'],
					'CODE' => $item['CODE'],
					'DATA_ORIG' => $item['DETAIL_TEXT'],
					'DATA' => $data,
				    'SHOW_AD' => $showAd,
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getByCurrentUser($refreshCache = false)
	{
		$userId = User::getCurrentUserId();
		return self::getByUserId($userId, $refreshCache);
	}

	public static function getById($id, $refreshCache = false)
	{
		$items = self::getByCurrentUser($refreshCache);
		return $items[$id];
	}

	public static function getViewsHref()
	{
		return '/' . self::URL . '/';
	}

	public static function getNewHref()
	{
		return self::getViewsHref() . 'new/';
	}

	public static function getHref($id)
	{
		return self::getViewsHref() . $id . '/';
	}

	public static function add($view)
	{
		$return = array();
		$userId = User::getCurrentUserId();
		if (!$userId)
			return $return;

		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $view['NAME'],
			'DETAIL_TEXT' => json_encode($view['DATA'], JSON_UNESCAPED_UNICODE),
			'XML_ID' => $userId,
		));

		$mark = self::getById($id, true);
		$mark['NEW'] = true;

		return $mark;
	}

	public static function addDefaulViews($userId)
	{
		$iblockElement = new \CIBlockElement();

		$data = array(
			'COLUMNS' => array('cb', 'name', 'ws', 'mark', 'title', 'text', 'action'),
		);
		$iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => 'Заголовок и текст',
			'CODE' => 1,
			'DETAIL_TEXT' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'XML_ID' => $userId,
		));
		$data = array(
			'COLUMNS' => array('cb', 'name', 'ws', 'mark', 'preview', 'action'),
		);
		$iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => 'Предварительный просмотр',
			'CODE' => 2,
			'DETAIL_TEXT' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'XML_ID' => $userId,
		));

		return self::getByUserId($userId, true);
	}

	public static function delete($id)
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->Delete($id);

		self::getByCurrentUser(true);
	}

	public static function update($view, $newView)
	{
		if (!$view['ID'])
			return false;

		$update = array();
		if ($view['NAME'] != $newView['NAME'])
			$update['NAME'] = $newView['NAME'];
		$data = json_encode($newView['DATA'], JSON_UNESCAPED_UNICODE);
		if ($view['DATA_ORIG'] != $data)
			$update['DETAIL_TEXT'] = $data;

		if ($update)
		{
			$iblockElement = new \CIBlockElement();
			$iblockElement->Update($view['ID'], $update);
			$view = self::getById($view['ID'], true);
			$view['UPDATED'] = true;
		}

		return $view;
	}

	public static function getCurrentView()
	{
		$user = User::getCurrentUser();

		$view = array();
		$viewId = $_REQUEST['view'];
		if ($viewId)
			$view = self::getById($viewId);
		if (!$view)
		{
			$viewId = $user['DATA']['VIEW'];
			if ($viewId)
				$view = self::getById($viewId);
		}
		if (!$view)
		{
			$views = self::getByCurrentUser();
			$view = array_shift($views);
		}
		if (!$view)
		{
			$views = self::addDefaulViews($user['ID']);
			$view = array_shift($views);
		}

		if ($view['ID'] && $user['DATA']['VIEW'] != $view['ID'])
		{
			User::saveData(array(
				'VIEW' => $view['ID'],
			));
		}

		return $view;
	}

	/*public static function printDropdown()
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
	}*/

}
