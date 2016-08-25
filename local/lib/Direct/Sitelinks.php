<?
namespace Local\Direct;

use Local\Api\ApiException;
use Local\ExtCache;
use Local\Utils;

/**
 * Наборы быстрых ссылок Директа
 */
class Sitelinks
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Direct/Sitelinks/';

	/**
	 * Возвращает набор ссылок по Id в директе
	 * @param $directId
	 * @param bool $refreshCache
	 * @return array|mixed
	 */
	public static function getByDirectId($directId, $refreshCache = false)
	{
		$directId = intval($directId);
		if (!$directId)
			return false;

		$return = array();

		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$directId,
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
				'IBLOCK_ID' => Utils::getIBlockIdByCode('y_sitelinks'),
				'=NAME' => $directId,
			), false, false, array(
				'ID', 'DETAIL_TEXT',
			));
			if ($item = $rsItems->Fetch())
				$return = json_decode($item['DETAIL_TEXT'], true);
			else
				$extCache->abortDataCache();

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Добавляет набор ссылок
	 * @param $source
	 * @param $result
	 * @return bool
	 * @throws ApiException
	 */
	public static function check($source, &$result)
	{
		$sitelinks = self::getByDirectId($source['Id']);
		if (!$sitelinks)
		{
			self::add($source);
			$result['add']++;
			return true;
		}
		else
		{
			$result['same']++;
			return false;
		}
	}

	/**
	 * Добавляет набор ссылок на основнии данных из API
	 * @param $source
	 * @return bool
	 * @throws ApiException
	 */
	public static function add($source)
	{
		$iblockElement = new \CIBlockElement();
		$id = $iblockElement->Add(array(
			'IBLOCK_ID' => Utils::getIBlockIdByCode('y_sitelinks'),
			'NAME' => $source['Id'],
			'DETAIL_TEXT' => json_encode($source, JSON_UNESCAPED_UNICODE),
		));
		if (!$id)
			throw new ApiException('sitelinks_add_error', 500, $iblockElement->LAST_ERROR);

		return $id;
	}

}
