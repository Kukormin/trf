<?
namespace Local\Main;
use Bitrix\Highloadblock\HighloadBlockTable;
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
	const ENTITY_ID = 4;

	/**
	 * Ключ в урле
	 */
	const URL = 'ad';

	public static function getByKeygroup($keygroupId, $refreshCache = false)
	{
		$keygroupId = intval($keygroupId);
		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$keygroupId,
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
					'UF_GROUP' => $keygroupId,
				),
			));
			$return = array();
			while ($item = $rsItems->Fetch())
			{
				$id = intval($item['ID']);
				$return[$id] = array(
					'ID' => $id,
					'YANDEX' => intval($item['UF_YANDEX']),
					'SEARCH' => intval($item['UF_SEARCH']),
					'TITLE' => $item['UF_TITLE'],
					'TITLE_2' => $item['UF_TITLE_2'],
					'TEXT' => $item['UF_TEXT'],
					'URL' => $item['UF_URL'],
					'LINK' => $item['UF_LINK'],
					'LINK_2' => $item['UF_LINK_2'],
					'LINKSET' => $item['UF_LINKSET'],
					'VCARD' => $item['UF_VCARD'],
					'GROUP' => intval($item['UF_GROUP']),
					'CATEGORY' => intval($item['UF_CATEGORY']),
					'PROJECT' => intval($item['UF_PROJECT']),
				);
			}

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getById($id, $keygroupId, $refreshCache = false)
	{
		$all = self::getByKeygroup($keygroupId, $refreshCache);
		return $all[$id];
	}

	public static function getAddYandexHref($category, $keygroup)
	{
		return Keygroup::getHref($category, $keygroup) . self::URL . '/ynew/';
	}

	public static function getAddGoogleHref($category, $keygroup)
	{
		return Keygroup::getHref($category, $keygroup) . self::URL . '/gnew/';
	}

	public static function getAddTemplHref($category, $keygroup, $templ)
	{
		return Keygroup::getHref($category, $keygroup) . self::URL . '/new/' . $templ['ID'] . '/';
	}

	public static function getHref($category, $keygroup, $ad)
	{
		return Keygroup::getHref($category, $keygroup) . self::URL . '/' . $ad['ID'] . '/';
	}

	public static function add($ad)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$fields = array();
		foreach ($ad as $key => $value)
			$fields['UF_' . $key] = $value;
		$result = $dataClass::add($fields);
		$id = $result->getId();

		$ad = self::getById($id, $ad['GROUP'], true);
		$ad['NEW'] = true;
		return $ad;
	}

	public static function update($ad, $newAd)
	{
		$update = array();
		foreach ($newAd as $key => $value)
		{
			if ($ad[$key] != $value)
				$update['UF_' . $key] = $value;
		}

		if ($update)
		{
			$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
			$entity = HighloadBlockTable::compileEntity($entityInfo);
			$dataClass = $entity->getDataClass();
			$dataClass::update($ad['ID'], $update);

			$ad = self::getById($ad['ID'], $ad['GROUP'], true);
			$ad['UPDATED'] = true;
		}

		return $ad;
	}

	/*public static function createByTempl($keygroup, $templ, $category, $project)
	{
		debugmessage($category['DATA']['TITLE_PLUS']);
		$options = $templ['DATA'];
		debugmessage($options);

		$text = '';

		// Заголовок задан вручную
		if ($options['TITLE_TYPE'] == 2)
		{
			$title = $options['TITLE_MANUAL'];
		}
		// Формируем из ключевой фразы
		else
		{
			//$parts = array('расширим', 'еще тест');
			$parts = array('еще тест');
			//$parts = array();
			if ($keygroup['BASE'] > 0)
			{
				$baseParts = explode(',', $keygroup['BASE']);
				foreach ($baseParts as $i => $part)
				{
					if ($part)
					{
						$word = $category['DATA']['BASE'][$i]['WORDS'][$part - 1];
						if ($word)
							$parts[] =  $word;
					}
				}
				if ($options['REPLACE'] && $category['DATA']['REPLACE'])
				{
					foreach ($parts as $i => $part)
						foreach ($category['DATA']['REPLACE'] as $from => $to)
						{
							$part = str_replace($from, $to, $part);
							$parts[$i] = $part;
						}
				}
				$keyword = implode(' ', $parts);
			}
			else
			{
				$keyword = $keygroup['NAME'];
				if ($options['REPLACE'] && $category['DATA']['REPLACE'])
				{
					foreach ($category['DATA']['REPLACE'] as $from => $to)
						$keyword = str_replace($from, $to, $keyword);
				}
				$parts = explode(' ', $keyword);
			}

			$l = strlen($keyword);
			if ($l > 33)
			{
				$i = count($parts);
				$sum = 0;
				foreach ($parts as $i => $s)
				{
					$cur = strlen($s) + ($i ? 1 : 0);
					if ($sum + $cur > 33)
						break;
					else
						$sum += $cur;
				}
				$title = implode(' ', array_slice($parts, 0, $i));
				$l = strlen($title);

				// Если можно расширить заголовок до 56 символов
				if ($options['TITLE_56'])
				{
					$parts = array_slice($parts, $i);
					$keyword = implode(' ', $parts);
					$x = 52 - $l;
					if (strlen($keyword) > $x)
					{
						$i = count($parts);
						$sum = 0;
						foreach ($parts as $i => $s)
						{
							$cur = strlen($s) + ($i ? 1 : 0);
							if ($sum + $cur > $x)
								break;
							else
								$sum += $cur;
						}
						$text = implode(' ', array_slice($parts, 0, $i)) . '!';
					}
					else
					{
						$text = $keyword . '!';

						// Добавки
						if ($options['TITLE_TYPE'] == 1 && $category['DATA']['TITLE_PLUS'])
						{
							$x -= strlen($text) + 1;
							foreach ($category['DATA']['TITLE_PLUS'] as $plus)
							{
								if (strlen($plus) <= $x)
								{
									$text .= ' ' . $plus;
									break;
								}
							}
						}
					}
				}
				else
					if ($l < 33)
						$title .= '!';
			}
			else
			{
				$title = $keyword;
				if ($l < 33)
					$title .= '!';

				// Добавки
				if ($options['TITLE_TYPE'] == 1 && $category['DATA']['TITLE_PLUS'])
				{
					$x = 32 - strlen($title);
					$y = $x + 20;
					foreach ($category['DATA']['TITLE_PLUS'] as $plus)
					{
						$l = strlen($plus);
						if ($l <= $x)
						{
							$title .= ' ' . $plus;
							break;
						}
						elseif ($options['TITLE_56'] && $l <= $y)
						{
							$text = $plus;
							break;
						}
					}
				}
			}

		}

		debugmessage($title);
		debugmessage(strlen($title));
		debugmessage($text);
		debugmessage(strlen($text));
		debugmessage(strlen($text) + strlen($title));


	}

	public static function getFields($keyword, $options)
	{
		// Заголовок и текст - вручную
		if ($options['TYPE'] == 'manual')
		{
			$title = $options['TITLE_MANUAL'];
			$text = $options['TEXT_MANUAL'];
		}
		// Формируем из ключевой фразы
		else
		{
			$text = '';
			$parts = explode(' ', $keyword);

			$titlePlus = explode("\n", $options['TITLE_PLUS']);
			$textPlus = explode("\n", $options['TEXT_PLUS']);

			$l = strlen($keyword);
			if ($l > 33)
			{
				$i = count($parts);
				$sum = 0;
				foreach ($parts as $i => $s)
				{
					$cur = strlen($s) + ($i ? 1 : 0);
					if ($sum + $cur > 33)
						break;
					else
						$sum += $cur;
				}
				$title = implode(' ', array_slice($parts, 0, $i));
				$l = strlen($title);

				// Если можно расширить заголовок до 56 символов
				if ($options['TITLE_LEN'] = 56)
				{
					$parts = array_slice($parts, $i);
					$keyword = implode(' ', $parts);
					$x = 53 - $l;
					if (strlen($keyword) > $x)
					{
						$i = count($parts);
						$sum = 0;
						foreach ($parts as $i => $s)
						{
							$cur = strlen($s) + ($i ? 1 : 0);
							if ($sum + $cur > $x)
								break;
							else
								$sum += $cur;
						}
						$text = implode(' ', array_slice($parts, 0, $i));
						$total = $l + strlen($text) + 3;
						if ($total < 56)
							$text .= '!';
						else
							$text .= '.';
					}
					else
					{
						$text = $keyword . '!';

						// Добавки
						if ($titlePlus)
						{
							$x -= strlen($text) + 1;
							foreach ($titlePlus as $plus)
							{
								if (strlen($plus) <= $x)
								{
									$text .= ' ' . $plus;
									break;
								}
							}
						}
					}
				}
				else
					if ($l < 33)
						$title .= '!';
			}
			else
			{
				$title = $keyword;
				if ($l < 33)
					$title .= '!';

				// Добавки
				if ($titlePlus)
				{
					$x = 32 - strlen($title);
					$y = $x + 20;
					foreach ($titlePlus as $plus)
					{
						$l = strlen($plus);
						if ($l <= $x)
						{
							$title .= ' ' . $plus;
							break;
						}
						elseif ($options['TITLE_LEN'] == 56 && $l <= $y)
						{
							$text = $plus;
							break;
						}
					}
				}
			}

			// Текст
			if ($textPlus)
			{
				$x = 74 - strlen($text);
				foreach ($textPlus as $plus)
				{
					if ($l <= $x)
					{
						$text .= ' ' . $plus;
						break;
					}
				}
			}

		}

		return array(
			'TITLE' => $title,
		    'TEXT' => $text,
		);
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

	public static function getTitle($keygroup, $templ)
	{
		$title = '';
		if ($keygroup['BASE'] < 0)
			return $title;

		$max = $templ['DATA']['TITLE_56'] ? 56 : 33;

		$base = explode(',', $keygroup['BASE']);
		foreach ($templ['DATA']['TITLE'] as $part)
		{
			if ($part['KEY'] == 'text')
			{
				$title = self::addPart($title, $part['D'], $max);
			}
			else
			{
				$col = substr($part['KEY'], 3);
				$i = $base[$col];
				$word = $part['D'][$i];
				if ($word)
				{
					$title = self::addPart($title, $word, $max);
				}
			}
		}
		return $title;
	}*/

	public static function generateByTemplate($keygroup, $templ, $category)
	{
		$ad = array(
			'YANDEX' => $templ['YANDEX'],
			'SEARCH' => $templ['SEARCH'],
			'URL' => $category['DATA']['PATH'],
			'LINKSET' => $templ['LINKSET'],
			'VCARD' => $templ['VCARD'],
		);
		if ($ad['YANDEX'])
		{
			$max = 33;
			if ($templ['SEARCH'] && $templ['DATA']['TITLE_56'])
				$max = 56;
			$ad['TITLE'] = Templ::constructParts($keygroup, $templ['DATA']['CONSTRUCT']['TITLE'], $max);
			$ad['TEXT'] = Templ::constructParts($keygroup, $templ['DATA']['CONSTRUCT']['TEXT'], 75);
			$ad['LINK'] = Templ::constructParts($keygroup, $templ['DATA']['CONSTRUCT']['LINK'], 20);
		}
		else
		{
			$max = 30;
			$ad['TITLE'] = Templ::constructParts($keygroup, $templ['DATA']['CONSTRUCT']['TITLE'], $max);
			$ad['TITLE_2'] = Templ::constructParts($keygroup, $templ['DATA']['CONSTRUCT']['TITLE_2'], $max);
			$ad['TEXT'] = Templ::constructParts($keygroup, $templ['DATA']['CONSTRUCT']['TEXT'], 80);
			$ad['LINK'] = Templ::constructParts($keygroup, $templ['DATA']['CONSTRUCT']['LINK'], 15);
			$ad['LINK_2'] = Templ::constructParts($keygroup, $templ['DATA']['CONSTRUCT']['LINK_2'], 15);
		}

		return $ad;
	}

	public static function getYandexFields($fields)
	{
		$title = $fields['TITLE'];
		$x = strlen($title) + 3;

		$text = $fields['TEXT'];
		$l = strlen($text);
		for ($i = 0; $i < $l; $i++)
		{
			if ($text[$i] == '!' || $text[$i] == '?')
			{
				if ($x + $i < 56)
				{
					$title .= ' - ' . substr($text, 0, $i);
					$text = substr($text, $i);
					break;
				}
			}
			if ($text[$i] == '.')
			{
				if ($x + $i < 57)
				{
					$title .= ' - ' . substr($text, 0, $i);
					$text = substr($text, $i);
					break;
				}
			}
		}

		$fields['TITLE'] = $title;
		$fields['TEXT'] = trim($text);

		return $fields;
	}

	public static function printExample($ad)
	{
		if ($ad['YANDEX'])
			if ($ad['SEARCH'])
				self::printYandexSearch($ad);
			else
				self::printYandexNet($ad);
		else
			if ($ad['SEARCH'])
				self::printGoogleSearch($ad);
			else
				self::printGoogleNet($ad);
	}

	public static function printYandexSearch($ad)
	{
		$ad = self::getYandexFields($ad);
		$scheme = $ad['SCHEME'] == 'https' ? 'https' : 'http';
		$url = $scheme . '://' . $ad['HOST'] . $ad['URL'];
		$link = $ad['LINK'] ? '/' . $ad['LINK'] : '';

		?>
		<div class="ad-preview yandex-serp">
			<h2>
				<a class="link" target="_blank" href="<?= $url ?>">
					<span class="favicon"></span>
					<b><?= $ad['TITLE'] ?></b> / <?= $ad['HOST'] ?>
				</a>
			</h2>
			<div class="subtitle">
				<div class="path">
					<a class="link" target="_blank" href="<?= $url ?>"><?= $ad['HOST'] ?><?= $link ?></a>
				</div>
				<div class="lbl">Реклама</div>
			</div>
			<div class="content">
				<div class="text"><?= $ad['TEXT'] ?></div><?

				if ($ad['LINKSET'])
				{
					?>
					<div class="sitelinks"><?

						$linkset = Linkset::getById($ad['LINKSET'], $ad['PROJECT']);
						for ($i = 1; $i < 5; $i++)
						{
							$item = $linkset['DATA']['ITEMS'][$i - 1];
							$hidden = $item['Title'] ? '' : ' class="hidden"';
							$url = $scheme . '://' . $ad['HOST'] . $item['Href'];

							?><div<?= $hidden ?>>
								<a target="_blank" href="<?= $url ?>"><?= $item['Title'] ?></a>
							</div><?
						}

					?>
					</div><?
				}

				if ($ad['VCARD'])
				{
					$href = Vcard::getYandexHref($ad['VCARD'], $ad['PROJECT']);

					$card = Vcard::getById($ad['VCARD'], $ad['PROJECT']);
					$phone = Vcard::getPhone($card);
					$regime = Vcard::getRegime($card);

					?>
					<div class="meta">
						<div>
							<a class="link" target="_blank" href="<?= $href ?>">Контактная информация</a>
						</div><div><?= $phone ?></div><?

						if ($regime)
						{
							?><div><?= $regime ?></div><?
						}

						if ($card['DATA']['City'])
						{
							?><div><?= $card['DATA']['City'] ?></div><?
						}

						?>
					</div><?
				}

				?>
			</div>
		</div><?
	}

	public static function printYandexNet($ad)
	{
		$scheme = $ad['SCHEME'] == 'https' ? 'https' : 'http';
		$url = $scheme . '://' . $ad['HOST'] . $ad['URL'];
		?>
		<div class="ad-preview yandex-net">
			<div class="picture">
				<div class="picture-block">
					<a>
						<img src="" />
					</a>
				</div>
			</div>
			<div class="content">
				<h2>
					<a class="link" target="_blank" href="<?= $url ?>">
						<span><?= $ad['TITLE'] ?></span>
					</a>
				</h2>
				<div class="text"><?= $ad['TEXT'] ?></div><?

				if ($ad['LINKSET'])
				{
					?>
					<div class="sitelinks"><?

						$linkset = Linkset::getById($ad['LINKSET'], $ad['PROJECT']);
						for ($i = 1; $i < 5; $i++)
						{
							$item = $linkset['DATA']['ITEMS'][$i - 1];
							$hidden = $item['Title'] ? '' : ' class="hidden"';
							$url = $scheme . '://' . $ad['HOST'] . $item['Href'];

							?><div<?= $hidden ?>>
								<a target="_blank" href="<?= $url ?>"><span><?= $item['Title'] ?></span></a>
							</div><?
						}

						?>
					</div><?
				}

				?>
				<div class="contacts">
					<div>
						<a class="link" target="_blank" href="<?= $url ?>">
							<?= $ad['HOST'] ?>
						</a>
					</div><?

					if ($ad['VCARD'])
					{
						$href = Vcard::getYandexHref($ad['VCARD'], $ad['PROJECT']);
						?>
						<div>
							<a class="link" target="_blank" href="<?= $href ?>">
								Адрес и телефон
							</a>
						</div><?
					}

					?>
				</div>
			</div>
		</div><?
	}

	public static function printGoogleSearch($ad)
	{
		$scheme = $ad['SCHEME'] == 'https' ? 'https' : 'http';
		$url = $scheme . '://' . $ad['HOST'] . $ad['URL'];
		$title2 = $ad['TITLE_2'] ? ' - ' . $ad['TITLE_2'] : '';
		$link = $ad['LINK'] ? $ad['LINK'] : '';
		if ($ad['LINK_2'])
		{
			if ($link)
				$link .= '/';
			$link .= $ad['LINK_2'];
		}

		$phone = '';
		$href = '';
		$address = '';
		$regime = '';
		if ($ad['VCARD'])
		{
			$card = Vcard::getById($ad['VCARD'], $ad['PROJECT']);
			$phone = Vcard::getPhone($card);
			$href = '#';
			$address = Vcard::getAddress($card);
			if ($address)
				$address = '<span class="pos"></span>' . $address;
			$regime = Vcard::getMondayRegime($card);
			if ($regime)
			{
				if ($address)
					$regime = ' - Часы работы сегодня · ' . $regime;
				else
					$regime = 'Часы работы сегодня · ' . $regime;
			}
		}

		?>
		<div class="ad-preview google-serp">
			<h2>
				<a class="link" target="_blank" href="<?= $url ?>">
					<?= $ad['TITLE'] ?><?= $title2 ?>
				</a>
			</h2>
			<div class="subtitle">
				<div class="lbl">Реклама</div><div class="path"><?= $ad['HOST'] ?>/<?= $link ?></div><?

				if ($phone)
				{
					?><div class="ph"><?= $phone ?></div><?
				}

				?>
			</div>
			<div class="content">
				<div class="text"><?= $ad['TEXT'] ?></div>
				<div class="sitelinks"><?

					$linkset = Linkset::getById($ad['LINKSET'], $ad['PROJECT']);
					for ($i = 1; $i < 5; $i++)
					{
						$item = $linkset['DATA']['ITEMS'][$i - 1];
						$hidden = $item['Title'] ? '' : ' class="hidden"';
						$url = $scheme . '://' . $ad['HOST'] . $item['Href'];

						?>
						<div id="yandex<?= $i ?>"<?= $hidden ?>>
							<a target="_blank" href="<?= $url ?>"><?= $item['Title'] ?></a>
						</div><?
					}

					?>
				</div><?

				if ($ad['VCARD'])
				{
					?>
					<div class="meta">
						<a class="link" target="_blank" href="<?= $href ?>"><?= $address ?></a>
						<span class="google-regime"><?= $regime ?></span>
					</div><?
				}

				?>
			</div>
		</div><?
	}

	public static function printGoogleNet($ad)
	{
		$scheme = $ad['SCHEME'] == 'https' ? 'https' : 'http';
		$url = $scheme . '://' . $ad['HOST'] . $ad['URL'];
		?>
		<div class="ad-preview google-net">
			<table>
				<tr>
					<td class="first"><div></div></td>
					<td class="ad"><div>
						<h2>
							<a class="h2" target="_blank" href="<?= $url ?>"><?= $ad['TITLE'] ?></a>
						</h2>
						<div>
							<a class="text" target="_blank" href="<?= $url ?>"><?= $ad['TEXT'] ?></a>
							<a class="link" target="_blank" href="<?= $url ?>">Перейти в <?= $ad['HOST'] ?></a>
						</div>
					</div></td>
					<td class="ar"><div><a class="rbtn" href="<?= $url ?>">
						<div>
							<svg version="1.1" x="0px" y="0px" height="30px" width="19px" viewBox="0 0 26 42">
								<polyline fill="none" stroke="#1d83d8" stroke-width="5" stroke-miterlimit="10"
								          points="2.875,2.958 21.166,20.957 2.917,38.916">
							</svg>
						</div>
					</a></div></td>
				</tr>
			</table>
		</div><?
	}
}
