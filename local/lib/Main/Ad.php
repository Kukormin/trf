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
				$y = intval($item['UF_YANDEX']);
				$s = intval($item['UF_SEARCH']);
				$return[$id] = array(
					'ID' => $id,
					'YANDEX' => $y,
					'SEARCH' => $s,
					'PLATFORM' => ($y ? 'y' : 'g') . ($s ? 's' : 'n'),
					'TITLE' => $item['UF_TITLE'],
					'TITLE_2' => $item['UF_TITLE_2'],
					'TEXT' => $item['UF_TEXT'],
					'URL' => $item['UF_URL'],
					'LINK' => $item['UF_LINK'],
					'LINK_2' => $item['UF_LINK_2'],
					'LINKSET' => $item['UF_LINKSET'],
					'VCARD' => $item['UF_VCARD'],
					'PICTURE' => intval($item['UF_PICTURE']),
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
		$id = intval($id);
		$all = self::getByKeygroup($keygroupId, $refreshCache);
		return $all[$id];
	}

	public static function getAddHref($category, $keygroup)
	{
		return Keygroup::getHref($category, $keygroup) . self::URL . '/new/';
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
		return $id;
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
		}
	}

	public static function delete($ad)
	{
		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$dataClass = $entity->getDataClass();
		$dataClass::delete($ad['ID']);

		self::getByKeygroup($ad['GROUP'], true);
	}

	public static function addByTemplate($keygroup, $templ, $category)
	{
		$ad = self::generateByTemplate($keygroup, $templ, $category);
		$ad['GROUP'] = $keygroup['ID'];
		$ad['CATEGORY'] = $category['ID'];
		$ad['PROJECT'] = $category['PROJECT'];
		self::add($ad);
		self::getByKeygroup($keygroup['ID'], true);
	}

	public static function generateByTemplate($keygroup, $templ, $category)
	{
		$ad = array(
			'YANDEX' => $templ['YANDEX'],
			'SEARCH' => $templ['SEARCH'],
			'PLATFORM' => ($templ['YANDEX'] ? 'y' : 'g') . ($templ['SEARCH'] ? 's' : 'n'),
			'URL' => $category['DATA']['PATH'],
			'LINKSET' => $templ['DATA']['LINKSET'],
			'VCARD' => $templ['DATA']['VCARD'],
		    'PICTURE' => $templ['DATA']['PICTURE'],
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
		$src = '';
		if ($ad['PICTURE'])
		{
			$picture = Picture::getPreview($ad['PICTURE']);
			if ($picture['src'])
				$src = $picture['src'];
		}
		?>
		<div class="ad-preview yandex-net">
			<div class="picture">
				<div class="picture-block">
					<a>
						<img src="<?= $src ?>" />
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

	public static function printRow($ad, $kg, $category, $first, $adCount, $view, $platform, $checkedIds = array())
	{
		$rs = $adCount > 1 ? ' rowspan="' . $adCount . '"' : '';
		if (!$adCount)
			$adCount = 1;
		$cnt = $first ? ' data-cnt="' . $adCount . '"' : '';
		$adId = intval($ad['ID']);
		?>
		<tr data-id="<?= $adId ?>" data-kgid="<?= $kg['ID'] ?>"<?= $cnt ?>><?

			foreach ($view['DATA']['COLUMNS'] as $col)
			{
				if ($col == 'ad')
				{
					foreach ($view['DATA']['AD_COLUMNS'] as $adCol)
					{
						$etd = $adCol == 'cb' ? '' : ' e-td';
						?>
						<td class="ad-td<?= $etd ?>" data-col="<?= $adCol ?>"><?

							if ($adCol == 'cb')
							{
								$checked = '';
								/*$inIds = in_array($kg['ID'], $checkedIds);
								if ($_REQUEST['select_all'] && !$inIds || !$_REQUEST['select_all'] && $inIds)
									$checked = ' checked';*/
								?><input class="select_ad" type="checkbox" id="<?= $ad['ID'] ?>"<?= $checked ?> /><?
							}
							elseif ($adCol == 'platform')
							{
								/*?><a class="btn ed" target="_blank"
								     href="<?= self::getHref($category, $kg, $ad) ?>"
								     title="Редактировать объявление"><i></i></a>
								<span class="btn delete" title="Удалить объявление"><i></i></span><?*/

								$pl = '';
								if ($adId)
									$pl = $ad['PLATFORM'];
								else
								{
									foreach ($platform as $k => $v)
									{
										if ($v['VALUE'])
										{
											$pl = $k;
											break;
										}
									}
								}

								?><span class="ad-icon <?= $pl ?>" data-value="<?= $pl ?>"
								        title="<?= $platform[$pl]['NAME'] ?>"></span><?
							}
							elseif ($adCol == 'title')
							{
								?><?= $ad['TITLE'] ?><?
							}
							elseif ($adCol == 'title2')
							{
								?><?= $ad['TITLE_2'] ?><?
							}
							elseif ($adCol == 'text')
							{
								?><?= $ad['TEXT'] ?><?
							}
							elseif ($adCol == 'url')
							{
								?><?= $ad['URL'] ?><?
							}
							elseif ($adCol == 'link')
							{
								?><?= $ad['LINK'] ?><?
							}
							elseif ($adCol == 'link2')
							{
								?><?= $ad['LINK_2'] ?><?
							}
							elseif ($adCol == 'preview')
							{
								if ($adId)
									self::printExample($ad);
							}
							elseif ($adCol == 'pr')
							{

							}

						?></td><?

					}
				}
				else
				{
					$hidden = $first ? '' : ' h';

					?>
					<td class="vs-td<?= $hidden ?>"<?= $rs ?> data-col="<?= $col ?>"><?

						if ($first)
						{
							if ($col == 'cb')
							{
								$checked = '';
								$inIds = in_array($kg['ID'], $checkedIds);
								if ($_REQUEST['select_all'] && !$inIds || !$_REQUEST['select_all'] && $inIds)
									$checked = ' checked';
								?><input class="select_item" type="checkbox" id="<?= $kg['ID'] ?>"<?= $checked ?> /><?
							}
							elseif ($col == 'name')
							{
								$href = Keygroup::getHref($category, $kg);
								?><a href="<?= $href ?>"><?= $kg['NAME'] ?></a><?
							}
							elseif ($col == 'ws')
							{
								?><?= $kg['WORDSTAT'] ?><?
							}
							elseif ($col == 'mark')
							{
								foreach ($kg['MARKS'] as $markId)
								{
									$mark = Mark::getById($markId);
									echo ' ';
									echo $mark['HTML'];
								}
							}
							elseif ($col == 'action')
							{
								if ($view['EDIT_MODE'])
								{
									?><a class="btn add" target="_blank" id="add_<?= $kg['ID'] ?>"
									     href="<?= self::getAddHref($category, $kg) ?>"
									     title="Добавить объявление"><i></i></a><?
								}
							}
						}

					?></td><?
				}
			}

		?></tr><?
	}

}
