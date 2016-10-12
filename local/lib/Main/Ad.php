<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ExpressionField;
use Local\System\ExtCache;

/**
 * Объявление
 */
class Ad
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/Ad/';

	/**
	 * ID HL-блока
	 */
	const ENTITY_ID = 0;

	/**
	 * сколько секунд фраза считается новой
	 */
	const NEW_TS = 7200;

	/**
	 * Ключ в урле
	 */
	const URL = 'kg';

	public static function createByTempl($keyword, $templ, $category, $project)
	{
		debugmessage($keyword);
		debugmessage($templ);
		debugmessage($category);
		debugmessage($project);
	}
}
