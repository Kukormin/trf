<?

namespace Local\Utils;

use Local\Main\Keygroup;
use Local\Main\View;
use Local\System\UserTypeNYesNo;
use Local\System\Utils;

/**
 * Class Handlers Обработчики событий
 * @package Local\Utils
 */
class Handlers
{
	/**
	 * Добавление обработчиков
	 */
	public static function addEventHandlers() {
		static $added = false;
		if (!$added) {
			$added = true;
			AddEventHandler('iblock', 'OnBeforeIBlockElementDelete',
				array(__NAMESPACE__ . '\Handlers', 'beforeIBlockElementDelete'));
			AddEventHandler('iblock', 'OnBeforeIBlockElementUpdate',
				array(__NAMESPACE__ . '\Handlers', 'beforeIBlockElementUpdate'));
			AddEventHandler('iblock', 'OnAfterIBlockAdd',
				array(__NAMESPACE__ . '\Handlers', 'afterIBlockUpdate'));
			AddEventHandler('iblock', 'OnAfterIBlockUpdate',
				array(__NAMESPACE__ . '\Handlers', 'afterIBlockUpdate'));
			AddEventHandler('iblock', 'OnIBlockDelete',
				array(__NAMESPACE__ . '\Handlers', 'afterIBlockUpdate'));
			AddEventHandler('iblock', 'OnIBlockPropertyBuildList',
				array(__NAMESPACE__ . '\Handlers', 'iBlockPropertyBuildList'));
			AddEventHandler('main', 'OnProlog',
				array(__NAMESPACE__ . '\Handlers', 'prolog'));
			AddEventHandler('main', 'OnAfterUserAdd',
				array(__NAMESPACE__ . '\Handlers', 'userAdd'));
		}
	}

	/**
	 * Добавление пользовательских свойств
	 * @return array
	 */
	public static function iBlockPropertyBuildList() {
		return UserTypeNYesNo::GetUserTypeDescription();
	}

	/**
	 * Обработчик события перед удалением элемента, с возможностью отмены удаления
	 * @param $id
	 * @return bool
	 */
	public static function beforeIBlockElementDelete($id)
	{


		return true;
	}

	/**
	 * Обработчик события перед изменением элемента с возможностью отмены изменений
	 * @param $arFields
	 * @return bool
	 */
	public static function beforeIBlockElementUpdate(&$arFields)
	{


		return true;
	}

	/**
	 * обработчик на редактирование ИБ для сброса кеша
	 */
	public static function afterIBlockUpdate() {
		Utils::getAllIBlocks(true);
	}

	/**
	 * Перед выводом визуальной части
	 */
	public static function prolog() {
		Keygroup::checkYGSNonProlog();
	}

	/**
	 * После добавления новго пользователя
	 */
	public static function userAdd(&$arFields) {
		if ($arFields['ID'] > 0)
			View::addDefaulViews($arFields['ID']);
	}

}