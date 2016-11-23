<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Local\System\ExtCache;

/**
 * Виртуальные визитки
 */
class Vcard
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/Vcard/';

	/**
	 * ID HL-блока
	 */
	const ENTITY_ID = 6;

	/**
	 * Ключ в урле
	 */
	const URL = 'vcards';

	/**
	 * Урл для быстрого просмотра
	 */
	const QURL = '/vcard/';

	public static function getByProject($projectId, $refreshCache = false)
	{
		$projectId = intval($projectId);
		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$projectId,
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

			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$rsItems = $dataClass::getList(array(
				'filter' => array(
					'UF_PROJECT' => $projectId,
				),
			));
			$return = array();
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return[$id] = array(
					'ID' => $id,
					'NAME' => $item['UF_NAME'],
					'PROJECT' => intval($item['UF_PROJECT']),
					'DATA_ORIG' => $item['UF_DATA'],
					'DATA' => json_decode($item['UF_DATA'], true),
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getById($id, $projectId)
	{
		$all = self::getByProject($projectId);
		return $all[$id];
	}

	public static function getListHref($projectId)
	{
		return Project::getHref($projectId) . self::URL . '/';
	}

	public static function getAddHref($projectId)
	{
		return self::getListHref($projectId) . 'new/';
	}

	public static function getHref($card)
	{
		return self::getListHref($card['PROJECT']) . $card['ID'] . '/';
	}

	public static function getYandexHref($cardId, $projectId)
	{
		return self::QURL . 'yandex/' . $projectId . '/' . $cardId . '/';
	}

	public static function getGoogleHref($cardId, $projectId)
	{
		return self::QURL . 'google/' . $projectId . '/' . $cardId . '/';
	}

	public static function add($newCard)
	{
		$projectId = $newCard['PROJECT'];
		$data = array();
		$data['UF_NAME'] = $newCard['NAME'];
		$data['UF_PROJECT'] = $projectId;
		$data['UF_DATA'] = json_encode($newCard['DATA'], JSON_UNESCAPED_UNICODE);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		self::getByProject($projectId, true);
		$card = self::getById($id, $projectId);
		$card['NEW'] = true;
		return $card;
	}

	public static function delete($card)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::delete($card['ID']);

		self::getByProject($card['PROJECT'], true);
	}

	public static function update($card, $newSet)
	{
		$update = array();
		if (isset($newSet['NAME']) && $newSet['NAME'] != $card['NAME'])
			$update['UF_NAME'] = $newSet['NAME'];
		if ($newSet['DATA'])
		{
			$newData = $card['DATA'];
			foreach ($newSet['DATA'] as $key => $value)
				$newData[$key] = $value;

			$encoded = json_encode($newData, JSON_UNESCAPED_UNICODE);
			if ($card['DATA_ORIG'] != $encoded)
				$update['UF_DATA'] = $encoded;
		}

		if ($update)
		{
			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$dataClass::update($card['ID'], $update);

			self::getByProject($card['PROJECT'], true);
			$card = self::getById($card['ID'], $card['PROJECT']);
			$card['UPDATED'] = true;
		}

		return $card;
	}

	public static function getRegimeParts($yandexRegime)
	{
		$return = array();

		$days = array('пн','вт','ср','чт','пт','сб','вс');
		$wt = explode(';', $yandexRegime);
		$l = count($wt);
		for ($i = 0; $i < $l; $i += 6)
		{
			$from = $wt[$i];
			$to = $wt[$i + 1];
			$t = $wt[$i + 2] . ':' . $wt[$i + 3] . '-' . $wt[$i + 4] . ':' . $wt[$i + 5];
			if ($from == $to)
				$d = $days[$from];
			else
				$d = $days[$from] . '-' . $days[$to];
			$return[] = array($d, $t);
		}

		return $return;
	}

	public static function getRegime($card)
	{
		$regime = self::getRegimeParts($card['DATA']['WorkTime']);
		$r = '';
		foreach ($regime as $item)
		{
			if ($r)
				$r .= ', ';
			$r .=  $item[0] . ' ' . $item[1];
		}
		return $r;
	}

	public static function getMondayRegime($card)
	{
		$regime = self::getRegimeParts($card['DATA']['WorkTime']);
		return $regime[0][1];
	}

	public static function getPhone($card)
	{
		$phone = $card['DATA']['Phone']['CountryCode'] .
			' (' . $card['DATA']['Phone']['CityCode'] . ') ' .
			$card['DATA']['Phone']['PhoneNumber'];
		if ($card['DATA']['Phone']['Extension'])
			$phone .= ' доб.' . $card['DATA']['Phone']['Extension'];

		return $phone;
	}

	public static function getAddress($card)
	{
		$address = $card['DATA']['Street'];
		if ($card['DATA']['House'])
		{
			if ($address)
				$address .= ', ';
			$address .= 'д.' . $card['DATA']['House'];
		}
		if ($card['DATA']['Building'])
		{
			if ($address)
				$address .= ', ';
			$address .= 'к.' . $card['DATA']['Building'];
		}
		if ($card['DATA']['City'])
		{
			if ($address)
				$address .= ', ';
			$address .= $card['DATA']['City'];
		}

		return $address;
	}

}
