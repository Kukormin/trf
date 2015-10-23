<?
namespace Local\Direct;

/**
 * Утилиты для работы с Директом
 */
class Common
{
	/**
	 * Преобразует даты из формата Директа в формат сайта
	 * @param $date
	 * @return mixed
	 */
	public static function convertDate($date)
	{
		return $GLOBALS['DB']->FormatDate($date, 'YYYY-MM-DD');
	}
}
