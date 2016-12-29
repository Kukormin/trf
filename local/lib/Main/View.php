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
		    'FIXED' => true,
		),
		'name' => array(
			'NAME' => 'Ключевая фраза',
			'REQUIRED' => true,
			'FIXED' => true,
		),
		'ws' => array(
			'NAME' => 'W',
		    'TITLE' => 'Частотность wordstat.yandex.ru',
		),
		'mark' => array(
			'NAME' => 'М',
			'TITLE' => 'Метки',
		),
		'ad' => array(
			'NAME' => 'Столбцы объявления',
		),
		'pr' => array(
			'NAME' => 'Объявления',
			'TITLE' => 'Иконки предварительного просмотра',
		),
		'action' => array(
			'NAME' => '',
			'TITLE' => 'Действия',
			'REQUIRED' => true,
		),
	);

	/**
	 * Набор стобцов
	 * @var array
	 */
	public static $AD_COLUMNS = array(
		'cb' => array(
			'NAME' => '',
			'TITLE' => 'Чекбокс выбора',
			'REQUIRED' => true,
			'FIXED' => true,
		),
		'platform' => array(
			'NAME' => '',
			'TITLE' => 'Платформа',
		),
		'title' => array(
			'NAME' => 'Заголовок',
		),
		'title2' => array(
			'NAME' => 'Заголовок 2',
		),
		'text' => array(
			'NAME' => 'Текст',
		),
		'url' => array(
			'NAME' => 'Ссылка',
		),
		'link' => array(
			'NAME' => 'ОС',
			'TITLE' => 'Отображаемая ссылка',
		),
		'link2' => array(
			'NAME' => 'ОС 2',
			'TITLE' => 'Отображаемая ссылка 2',
		),
		'preview' => array(
			'NAME' => 'Предварительный просмотр',
		    'HIDE_EDIT_MODE' => true,
		),
	);

	/**
	 * Наборы колонок по-умолчанию
	 * @var array
	 */
	public static $DEFAULT_COLUMNS = array('cb', 'name', 'ws', 'mark', 'pr', 'action');
	public static $DEFAULT_COLUMNS_EM = array('cb', 'name', 'ad', 'action');
	public static $DEFAULT_AD_COLUMNS = array('cb', 'platform', 'title', 'text');

	public static function getColumnByCode($code)
	{
		return self::$COLUMNS[$code];
	}

	public static function getAdColumnByCode($code)
	{
		return self::$AD_COLUMNS[$code];
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
				$showAd = in_array('ad', $data['COLUMNS']);
				$return[$item['ID']] = array(
					'ID' => intval($item['ID']),
					'NAME' => $item['NAME'],
					'CODE' => $item['CODE'],
					'DATA_ORIG' => $item['DETAIL_TEXT'],
					'DATA' => $data,
				    'SHOW_AD' => $showAd,
				    'EDIT_MODE' => $item['CODE'] == 2 || $item['CODE'] == 12,
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

	public static function getEditNewHref()
	{
		return self::getViewsHref() . 'enew/';
	}

	public static function getHref($id)
	{
		return self::getViewsHref() . $id . '/';
	}

	public static function add($view, $editMode)
	{
		$return = array();
		$userId = User::getCurrentUserId();
		if (!$userId)
			return $return;

		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => $view['NAME'],
			'CODE' => $editMode ? 12 : 11,
			'DETAIL_TEXT' => json_encode($view['DATA'], JSON_UNESCAPED_UNICODE),
			'XML_ID' => $userId,
		));

		$view = self::getById($id, true);
		$view['NEW'] = true;

		return $view;
	}

	public static function addDefaulViews($userId)
	{
		$iblockElement = new \CIBlockElement();

		$data = array(
			'COLUMNS' => self::$DEFAULT_COLUMNS,
			'AD_COLUMNS' => self::$DEFAULT_AD_COLUMNS,
		);
		$iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => 'По-умолчанию',
			'CODE' => 1,
			'DETAIL_TEXT' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'XML_ID' => $userId,
		));
		$data = array(
			'COLUMNS' => self::$DEFAULT_COLUMNS_EM,
			'AD_COLUMNS' => self::$DEFAULT_AD_COLUMNS,
		);
		$iblockElement->Add(array(
			'IBLOCK_ID' => self::IBLOCK_ID,
			'NAME' => 'По-умолчанию',
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

		$editMode = $user['DATA']['EDIT'];
		if (isset($_REQUEST['em']))
			$editMode = intval($_REQUEST['em']);

		$key = $editMode ? 'VIEW_EM' : 'VIEW';

		$view = array();
		$viewId = $_REQUEST['view'];
		if ($viewId)
			$view = self::getById($viewId);
		if (!$view)
		{
			$viewId = $user['DATA'][$key];
			if ($viewId)
				$view = self::getById($viewId);
		}
		if (!$view)
		{
			$views = self::getByCurrentUser();
			if (!$views)
				$views = self::addDefaulViews($user['ID']);
			foreach ($views as $v)
			{
				if ($v['EDIT_MODE'] && $editMode ||
					!$v['EDIT_MODE'] && !$editMode)
				{
					$view = $v;
					break;
				}
			}
		}

		if ($view['ID'] && $user['DATA'][$key] != $view['ID'] || $editMode != $user['DATA']['EDIT'])
		{
			User::saveData(array(
				$key => $view['ID'],
			    'EDIT' => $editMode,
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
