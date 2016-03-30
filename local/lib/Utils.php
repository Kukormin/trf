<?
namespace Local;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Class Utils Утилиты проекта
 * @package Local
 */
class Utils
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Utils/';

	/**
	 * @var array HTTP статусы
	 */
	private static $statusByCode = array(
		200 => 'OK',
		400 => 'Bad Request',
		401 => 'Not Authorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		500 => 'Internal Server Error',
	);

	/**
	 * Возвращает HTTP статус по коду
	 * @param $code
	 * @return string
	 */
	public static function getHttpStatusByCode($code)
	{
		$s = self::$statusByCode[$code];
		if ($s)
			return $code . ' ' . $s;
		else
		{
			$code = 500;
			return $code . ' ' . self::$statusByCode[$code];
		}
	}

	/**
	 * Возвращает все инфоблоки
	 * @param bool|false $bRefreshCache сбросить кеш
	 * @return array
	 */
	public static function getAllIBlocks($bRefreshCache = false) {
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
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

			$iblock = new \CIBlock();
			$rsItems = $iblock->GetList(array(), Array(), false);
			while ($arItem = $rsItems->Fetch()) {

				$return['ITEMS'][$arItem['ID']] = array(
					'ID' => $arItem['ID'],
					'ACTIVE' => $arItem['ACTIVE'],
					'NAME' => $arItem['NAME'],
					'CODE' => $arItem['CODE'],
					'TYPE' => $arItem['IBLOCK_TYPE_ID'],
				);
				if ($arItem['CODE']) {
					$return['BY_CODE'][$arItem['CODE']] = $arItem['ID'];
				}
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает ID инфоблока по коду
	 * @param $code
	 * @return mixed
	 */
	public static function getIBlockIdByCode($code) {
		$iblocks = self::getAllIBlocks();
		return $iblocks['BY_CODE'][$code];
	}

	/**
	 * Возвращает инфоблок по коду
	 * @param $code
	 * @return mixed
	 */
	public static function getIBlockByCode($code) {
		$iblocks = self::getAllIBlocks();
		$id = $iblocks['BY_CODE'][$code];
		return $iblocks['ITEMS'][$id];
	}

	/**
	 * Возвращает инфоблок по ID
	 * @param $id
	 * @return mixed
	 */
	public static function getIBlockById($id) {
		$iblocks = self::getAllIBlocks();
		return $iblocks['ITEMS'][$id];
	}

	/**
	 * Форматирует дату в формате битрикса
	 * @param $date
	 * @return mixed
	 */
	public static function formatDateFull($date) {
		global $DB;
		return $DB->FormatDate($date);
	}

	/**
	 * Возвращает варианты значений списочного свойства
	 * @param $propId
	 * @param bool $bRefreshCache
	 * @return array|mixed
	 */
	public static function getPropertyEnums($propId, $bRefreshCache = false) {
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$propId,
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

			$iblockPropertyEnum = new \CIBlockPropertyEnum();
			$rsItems = $iblockPropertyEnum->GetList(array(), array(
				'PROPERTY_ID' => $propId,
			));
			while ($arItem = $rsItems->Fetch())
			{
				$return['ITEMS'][$arItem['ID']] = array(
					'ID' => $arItem['ID'],
					'VALUE' => $arItem['VALUE'],
					'XML_ID' => $arItem['XML_ID'],
				);
				$return['BY_XML_ID'][$arItem['XML_ID']] = $arItem['ID'];
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает ID варианта списочного свойства по XML_ID
	 * @param $xml
	 * @param $propId
	 * @return mixed
	 */
	public static function getPropertyIdByXml($xml, $propId)
	{
		$all = self::getPropertyEnums($propId);
		return $all['BY_XML_ID'][$xml];
	}

	/**
	 * Возвращает XML_ID варианта списочного свойства по ID
	 * @param $id
	 * @param $propId
	 * @return mixed
	 */
	public static function getPropertyXmlById($id, $propId)
	{
		$all = self::getPropertyEnums($propId);
		return $all['ITEMS'][$id]['XML_ID'];
	}

	/**
	 * Возвращает варианты значений свойства типа справочник
	 * @param $hlId
	 * @param bool $bRefreshCache
	 * @return array|mixed
	 */
	public static function getReferenceEnums($hlId, $bRefreshCache = false) {
		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$hlId,
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

			Loader::IncludeModule('highloadblock');

			$hlblock = HighloadBlockTable::getById($hlId)->fetch();
			$entity = HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();

			$rsItems = $entity_data_class::getList(array(
				"select" => array('*'),
			));
			while ($arItem = $rsItems->Fetch())
			{
				$return['ITEMS'][$arItem['ID']] = array(
					'ID' => $arItem['ID'],
					'VALUE' => $arItem['UF_NAME'],
					'XML_ID' => $arItem['UF_XML_ID'],
				);
				$return['BY_XML_ID'][$arItem['UF_XML_ID']] = $arItem['ID'];
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает ID варианта свойства типа справочник по XML_ID
	 * @param $xml
	 * @param $hlId
	 * @return mixed
	 */
	public static function getReferenceIdByXml($xml, $hlId)
	{
		$all = self::getReferenceEnums($hlId);
		return $all['BY_XML_ID'][$xml];
	}

	/**
	 * Возвращает XML_ID варианта свойства типа справочник по ID
	 * @param $id
	 * @param $hlId
	 * @return mixed
	 */
	public static function getReferenceXmlById($id, $hlId)
	{
		$all = self::getReferenceEnums($hlId);
		return $all['ITEMS'][$id]['XML_ID'];
	}
}
