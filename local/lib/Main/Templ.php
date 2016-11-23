<?
namespace Local\Main;

use Bitrix\Highloadblock\HighloadBlockTable;
use Local\System\ExtCache;

/**
 * Шаблоны объявлений
 */
class Templ
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/Template/';

	/**
	 * ID HL-блока
	 */
	const ENTITY_ID = 7;

	/**
	 * Ключ в урле
	 */
	const URL = 'templates';

	public static function getByCategory($categoryId, $refreshCache = false)
	{
		$categoryId = intval($categoryId);
		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$categoryId,
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
					'UF_CATEGORY' => $categoryId,
				),
			));
			$return = array();
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return[$id] = array(
					'ID' => $id,
					'NAME' => $item['UF_NAME'],
					'YANDEX' => intval($item['UF_YANDEX']),
					'SEARCH' => intval($item['UF_SEARCH']),
					'CATEGORY' => intval($item['UF_CATEGORY']),
					'DATA_ORIG' => $item['UF_DATA'],
					'DATA' => json_decode($item['UF_DATA'], true),
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getById($id, $categoryId)
	{
		$all = self::getByCategory($categoryId);
		return $all[$id];
	}

	public static function getListHref($category)
	{
		return Category::getHref($category) . self::URL . '/';
	}

	public static function getAddYandexHref($category)
	{
		return self::getListHref($category) . 'ynew/';
	}

	public static function getAddGoogleHref($category)
	{
		return self::getListHref($category) . 'gnew/';
	}

	public static function getHref($templ, $category)
	{
		return self::getListHref($category) . $templ['ID'] . '/';
	}

	public static function add($newTempl)
	{
		$categoryId = $newTempl['CATEGORY'];
		$data = array();
		$data['UF_NAME'] = $newTempl['NAME'];
		$data['UF_YANDEX'] = $newTempl['YANDEX'];
		$data['UF_CATEGORY'] = $categoryId;
		$data['UF_DATA'] = json_encode($newTempl['DATA'], JSON_UNESCAPED_UNICODE);

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$result = $dataClass::add($data);
		$id = $result->getId();

		self::getByCategory($categoryId, true);
		$template = self::getById($id, $categoryId);
		$template['NEW'] = true;
		return $template;
	}

	public static function delete($templ)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::delete($templ['ID']);

		self::getByCategory($templ['CATEGORY'], true);
	}

	public static function update($templ, $newTempl)
	{
		$update = array();

		if (isset($newTempl['NAME']) && $newTempl['NAME'] != $templ['NAME'])
			$update['UF_NAME'] = $newTempl['NAME'];
		if (isset($newTempl['YANDEX']) && $newTempl['YANDEX'] != $templ['YANDEX'])
			$update['UF_YANDEX'] = $newTempl['YANDEX'];
		if (isset($newTempl['SEARCH']) && $newTempl['SEARCH'] != $templ['SEARCH'])
			$update['UF_SEARCH'] = $newTempl['SEARCH'];

		if ($newTempl['DATA'])
		{
			$newData = $templ['DATA'];
			foreach ($newTempl['DATA'] as $key => $value)
				$newData[$key] = $value;

			$encoded = json_encode($newData, JSON_UNESCAPED_UNICODE);
			if ($templ['DATA_ORIG'] != $encoded)
				$update['UF_DATA'] = $encoded;
		}

		if ($update)
		{
			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$dataClass::update($templ['ID'], $update);

			self::getByCategory($templ['CATEGORY'], true);
			$templ = self::getById($templ['ID'], $templ['CATEGORY']);
			$templ['UPDATED'] = true;
		}

		return $templ;
	}

	public static function printDropdown($categoryId)
	{
		?>
		<ul class="dropdown-menu"><?
		$items = self::getByCategory($categoryId);
		foreach ($items as $item)
		{
			?>
			<li><a data-id="<?= $item['ID'] ?>" href="javascript:void(0)"><?= $item['NAME'] ?></a></li><?
		}
		?>
		</ul><?
	}

	private static function addPart($s, $part, $max)
	{
		$f = $part[0];
		$punct = $f == '!' || $f == '.' || $f == '?' || $f == ',' || $f == ':' || $f == ';';
		$add = '';
		if ($s && !$punct)
			$add = ' ';
		$add .= $part;
		$new = $s . $add;
		if (strlen($new) > $max)
			return false;

		return $new;
	}

	public static function constructParts($keygroup, $parts, $max)
	{
		$return = '';

		$base = $keygroup['BASE_ARRAY'];
		if (!$base)
			$base = explode(' ', $keygroup['NAME']);

		foreach ($parts as $part)
		{
			if ($part['KEY'] == 'text')
			{
				$return = self::addPart($return, $part['D'], $max);
			}
			elseif ($part['KEY'] == 'keyword')
			{
				foreach ($base as $word)
					$return = self::addPart($return, $word, $max);
			}
			else
			{
				// Фраза сгенерирована из базовых слов
				if ($keygroup['TYPE'] != 0)
				{
					$col = substr($part['KEY'], 3);
					$word = $base[$col];
					if ($part['D'][$word])
						$word = $part['D'][$word];
					if ($word)
						$return = self::addPart($return, $word, $max);
				}
			}
		}

		return $return;
	}

}
