<?
namespace Local\Utils;

/**
 * Парсер сайтов
 */
class Parser
{
	const TIMEOUT = 30;

	public static function getWithInfo($url)
	{
		$result = '';
		$filename = $_SERVER['DOCUMENT_ROOT'] . '/_log/urls/' . md5($url) . '.html';
		if (file_exists($filename))
			$result = unserialize(file_get_contents($filename));

		if (!$result)
		{

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, idn_to_ascii($url));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_TRANSFER_ENCODING, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
			$html = curl_exec($ch);
			$result = curl_getinfo($ch);
			$result['HTML'] = $html;
			curl_close($ch);

			file_put_contents($filename, serialize($result));
		}

		return $result;
	}

	public static function get($url)
	{
		$res = self::getWithInfo($url);
		return $res['HTML'];
	}

	public static function site($url)
	{
		$return = array();

		$s = self::get($url);
		if ($s === false)
			return false;

		$tags = self::getTags($s);

		if (strpos($s, 'charset=windows-1251') || strpos($s, 'charset=Windows-1251'))
			$s = $GLOBALS['APPLICATION']->ConvertCharset($s, "Windows-1251", "UTF-8");

		$return['META'] = array(
			'title' => $tags['title'],
			'description' => $tags['description'],
			'keywords' => $tags['keywords'],
			'h1' => $tags['h1'],
		);
		$return['PHONE'] = self::phone($s);
		$return['EMAIL'] = self::email($tags, $s);
		$return['NAME'] = self::name($tags['title']);
		$return['ESTORE'] = self::estore($tags['title'], $tags['description']);
		$return['PRODUCT'] = self::product($tags);

		if (!$return['PHONE']['number'] || !$return['EMAIL'])
		{
			$contacts = self::contactsPage($s);

			if ($contacts)
			{
				if (strpos($contacts, '//') !== false)
					$s = self::get($contacts);
				else
					$s = self::get($url . '/' . $contacts);
				if ($s !== false)
				{
					if (!$return['PHONE']['number'])
						$return['PHONE'] = self::phone($s);
					if (!$return['EMAIL'])
					{
						$tags = self::getTags($s);
						$return['EMAIL'] = self::email($tags, $s);
					}
				}
			}
		}

		return $return;
	}

	public static function phone($s)
	{
		$text = strip_tags($s);
		$prefix = '';
		$code = '';
		$number = '';
		$trim = false;
		$ar1 = explode('8-800', $text);
		if (count($ar1) <= 1)
			$ar1 = explode('8 800', $text);
		if (count($ar1) <= 1)
			$ar1 = explode('8 (800)', $text);
		if (count($ar1) <= 1)
			$ar1 = explode('8(800)', $text);
		if (count($ar1) > 1)
		{
			$prefix = '8';
			$code = '800';
		}
		else
		{
			$ar1 = explode('+7', $text);
			if (count($ar1) > 1)
				$prefix = '+7';
			else
			{
				$ar1 = explode('телефон', $text);
				if (count($ar1) <= 1)
					$ar1 = explode('Телефон', $text);
				if (count($ar1) <= 1)
					$ar1 = explode('ТЕЛЕФОН', $text);
				if (count($ar1) > 1)
				{
					$prefix = '+7';
					$trim = true;
				}
				else
				{

				}
			}
		}
		if ($prefix)
		{
			$tmp = substr($ar1[1], 0, 60);
			$phoneChars = array(
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9',
				'0',
				'-',
				' ',
				'(',
				')'
			);
			$numbers = array(
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9',
				'0',
			);


			if ($trim)
			{
				$ar2 = str_split($tmp);
				foreach ($ar2 as $i => $s2)
				{
					if (in_array($s2, $phoneChars))
					{
						$tmp = substr($tmp, $i);
						break;
					}
				}
			}
			$number = '';
			$ar2 = str_split($tmp);
			foreach ($ar2 as $s2)
			{
				if (in_array($s2, $phoneChars))
					$number .= $s2;
				else
					break;
			}

			$number = trim($number);
			if (!$code)
			{
				$ar2 = str_split($number);
				$beg = false;
				foreach ($ar2 as $i => $s2)
				{
					if (in_array($s2, $numbers))
					{
						$code .= $s2;
						$beg = true;
					}
					else
					{
						if ($beg)
						{
							$number = substr($number, $i);
							break;
						}
					}
				}
			}
			$number = trim($number, " ()-\t\n\r\0\x0B");
		}

		$return = array(
			'prefix' => $prefix,
			'code' => $code,
			'number' => $number,
		);

		return $return;
	}

	public static function email($tags, $s)
	{
		$email = '';
		foreach ($tags['A'] as $item)
		{
			if (substr($item['href'], 0, 7) == 'mailto:')
			{
				$email = trim(substr($item['href'], 7));
				$ar1 = explode('?', $email);
				if (count($ar1) > 1)
					$email = $ar1[0];
				break;
			}
		}
		if ($email)
			return $email;

		$trims = array(
			" ",
			"'",
			'"',
			"<",
			">",
			"\t",
			"\n",
			"\r",
			"\0",
			"\x0B",
		);
		$ar1 = explode('"mailto:', $s);
		if (count($ar1) > 1)
		{
			$ar2 = explode('"', $ar1[1]);
			if (count($ar2) > 1)
				$email = $ar2[0];
			if (strpos($email, '<script') !== false)
				$email = '';
		}
		if ($email)
			return $email;

		$ar1 = explode("'mailto:", $s);
		if (count($ar1) > 1)
		{
			$ar2 = explode("'", $ar1[1]);
			if (count($ar2) > 1)
				$email = $ar2[0];
			if (strpos($email, '<script') !== false)
				$email = '';
		}
		if ($email)
			return $email;

		$ar1 = explode('@', $s);
		if (count($ar1) > 1)
		{
			$prev = '';
			foreach ($ar1 as $i => $tmp)
			{
				if ($prev)
				{
					$part1 = '';
					$part2 = '';
					$ar2 = str_split($prev);
					for ($i = count($ar2) - 1; $i >= 0; $i--)
					{
						$s2 = $ar2[$i];
						if (in_array($s2, $trims))
							break;
						else
							$part1 = $s2 . $part1;
					}
					$ar2 = str_split($tmp);
					foreach ($ar2 as $s2)
					{
						if (in_array($s2, $trims))
							break;
						else
							$part2 .= $s2;
					}

					if ($part1 && $part2)
					{
						$email = $part1 . '@' . $part2;
						if (strpos($email, 'Rating@Mail.ru') === false &&
							strpos($email, 'Рейтинг@Mail.ru') === false)
							break;
						else
							$email = '';
					}
				}
				$prev = $tmp;
			}
		}

		return $email;
	}

	public static function links($indexUrl)
	{
		$links = array();

		$s = self::get($indexUrl);
		if ($s === false)
			return $links;

		$tags = self::getTags($s);

		foreach ($tags['A'] as $item)
		{
			$href = $item['href'];
			$name = strtolower(trim($item['_TEXT']));

			if (!$href || strlen($name) < 2)
				continue;

			if (substr($href, 0, 1) == '#')
				continue;

			if (substr($href, 0, 10) == 'javascript')
				continue;

			if ($item['rel'] == 'nofollow')
				continue;

			$url = '';
			if (substr($href, 0, 7) == 'http://')
				$url = substr($href, 7);
			elseif (substr($href, 0, 8) == 'https://')
				$url = substr($href, 8);
			elseif (substr($href, 0, 2) == '//')
				$url = substr($href, 2);

			if ($url)
			{
				$ar = explode('/', $url);
				$url = $ar[0];
				if ($url != $indexUrl)
					continue;
			}

			if ($name == 'о нас' || $name == 'контакты' || $name == 'контакты')
				continue;

			$href = 'http://' . $indexUrl . $href;
			$links[$href] = $item['_TEXT'];

		}

		return $links;
	}

	public static function name($title)
	{

		if (strpos($title, 'Главная - ') === 0)
			$title = substr($title, 10);
		elseif (strpos($title, 'Главная') === 0)
			$title = substr($title, 7);

		$name = trim($title);

		if (strpos($name, '404') !== false)
			return '';

		if ($name)
		{
			$ar1 = explode('«', $name);
			if (count($ar1) > 1)
			{
				$ar2 = explode('»', $ar1[1]);
				if (count($ar2) > 1)
					$name = $ar2[0];
			}
			else
			{
				$ar1 = explode('“', $name);
				if (count($ar1) > 1)
				{
					$ar2 = explode('”', $ar1[1]);
					if (count($ar2) > 1)
						$name = $ar2[0];
				}
				else
				{
					$ar1 = explode('"', $name);
					if (count($ar1) > 2)
						$name = $ar1[1];
					else
					{
						$ar1 = explode(' - ', $name);
						if (count($ar1) > 1)
							$name = $ar1[0];
						$ar1 = explode(' – ', $name);
						if (count($ar1) > 1)
							$name = $ar1[0];
						$ar1 = explode(' | ', $name);
						if (count($ar1) > 1)
							$name = $ar1[0];
					}
				}
			}

		}

		return $name;
	}

	public static function estore($title, $description)
	{
		$tmp = strtolower($title);
		if (strpos($tmp, 'магазин') !== false)
			return true;

		$tmp = strtolower($description);
		if (strpos($tmp, 'магазин') !== false)
			return true;

		return false;
	}

	public static function contactsPage($tags)
	{
		$contacts = '';

		foreach ($tags['A'] as $item)
		{
			$text = strtolower($item['_TEXT']);
			if (strpos($text, 'контакты') !== false)
			{
				$contacts = $item['href'];
				break;
			}
		}

		return $contacts;
	}

	public static function metric($url)
	{
		$return = array();

		$s = self::get($url);
		if ($s === false)
			return $return;

		if (strpos($s, '//mc.yandex.ru/metrika/watch.js'))
			$return['yandex'] = true;
		if (strpos($s, '//www.google-analytics.com/analytics.js'))
			$return['google'] = true;

		return $return;
	}

	public static function yml($url)
	{
		$return = array();

		$s = self::get($url);
		if ($s === false)
			return $return;

		$doc = new \DOMDocument();
		$doc->loadXML($s);

		foreach ($doc->getElementsByTagName('category') as $item) {
			$attrValues = array();
			foreach ($item->attributes as $attr)
				$attrValues[$attr->name] = $attr->value;
			$id = intval($attrValues['id']);
			$return[$id] = array(
				'ID' => $id,
				'NAME' => $item->textContent,
			    'PARENT' => intval($attrValues['parentId']),
			    'CNT' => 0,
			);
		}

		foreach ($doc->getElementsByTagName('offer') as $item)
		{
			$fields = array();
			foreach ($item->childNodes as $child)
			{
				$fields[$child->tagName] = $child->textContent;
				$catId = intval($fields['categoryId']);
				$return[$catId]['CNT']++;
			}
		}

		return $return;
	}

	public static function getTags($s)
	{
		$return = array();

		$bom = pack("CCC",0xef,0xbb,0xbf);

		if (strpos($s, 'utf-8') || strpos($s, 'UTF-8'))
			if (substr($s, 0, 3) != $bom)
				$s = $bom . $s;

		$doc = new \DOMDocument();
		$doc->loadHTML($s);
		//debugmessage($doc);

		$docNode = $doc->documentElement;
		foreach ($docNode->childNodes as $item)
		{
			if ($item->tagName == 'head')
			{

				foreach ($item->childNodes as $child)
				{
					if ($child->tagName == 'title')
						$return['title'] = $child->textContent;
					elseif ($child->tagName == 'meta')
					{
						$attrValues = array();
						foreach ($child->attributes as $attr)
							$attrValues[$attr->name] = $attr->value;

						if ($attrValues['name'] == 'keywords')
							$return['keywords'] = $attrValues['content'];
						elseif ($attrValues['name'] == 'description')
							$return['description'] = $attrValues['content'];
					}
				}
			}
		}

		foreach ($doc->getElementsByTagName('h1') as $item) {
			$return['h1'] = $item->textContent;
			break;
		}

		foreach ($doc->getElementsByTagName('a') as $item) {
			$attrValues = array();
			foreach ($item->attributes as $attr)
				$attrValues[$attr->name] = $attr->value;
			$attrValues['_TEXT'] = $item->textContent;
			$return['A'][] = $attrValues;
		}

		return $return;
	}

	public static function product($tags)
	{
		$return = array();
		$tmp = $tags['title'] . ' ' . $tags['description'] . ' ' . $tags['keywords'] . ' ' . $tags['h1'];
		$tmp = strtolower($tmp);

		// цены
		if (strpos($tmp, 'по низким ценам') !== false ||
			strpos($tmp, 'низкие цены') !== false ||
			strpos($tmp, 'недорог') !== false ||
			strpos($tmp, 'не дорог') !== false ||
			strpos($tmp, 'дешев') !== false)
			$return['price'] = 'low';

		// поставщик
		if (strpos($tmp, 'от производителя') !== false)
			$return['prod'] = true;
		if (strpos($tmp, 'официальный дилер') !== false ||
			strpos($tmp, 'от официального дилера') !== false)
			$return['dealer'] = true;

		// оптом и в розницу
		if (strpos($tmp, 'оптом') !== false ||
			strpos($tmp, 'оптовы') !== false)
		{
			if (strpos($tmp, 'по оптовым') === false &&
				strpos($tmp, 'оптовые цены') === false)
				$return['opt'] = true;
		}
		if (strpos($tmp, 'розниц') !== false ||
			strpos($tmp, 'розничн') !== false)
			$return['rozn'] = true;

		// под ключ
		if (strpos($tmp, 'под ключ') !== false)
			$return['key'] = true;

		// регионы
		$ar1 = explode('интернет', $tmp);
		$regions = Region::getAll();
		foreach ($regions as $region)
		{
			if (!$region['YANDEX'] || $region['NAME'] == 'Строитель' || $region['NAME'] == 'Центр' ||
				$region['NAME'] == 'Выборг' || strlen($region['NAME']) < 4)
				continue;

			$name = strtolower($region['NAME']);
			$name = ' ' . substr($name, 0, strlen($name) - 1);

			foreach ($ar1 as $tmp1)
			{
				if (strpos($tmp1, $name) !== false)
					$return['REGIONS'][$region['NAME']] = $region['NAME'];
			}
		}

		// Продукт
		$titles = self::getVars($tags['title']);
		$descriptions = self::getVars($tags['description']);
		$vars = array_merge($titles, $descriptions);
		$return['VARS'] = array();
		foreach ($vars as $var)
		{
			if (strlen($var) > 10)
				$return['VARS'][] = $var;
		}
		return $return;
	}

	public static function getVars($s)
	{
		$s = strtolower(trim($s, " !:.,-–—\t\n\r\0\x0B"));
		if (strpos($s, 'Главная - ') === 0)
			$s = substr($s, 10);
		elseif (strpos($s, 'Главная') === 0)
			$s = substr($s, 7);
		$s = trim($s);
		if (strpos($s, '404') !== false)
			$s = '';

		if ($s)
		{
			$ar1 = explode('«', $s);
			if (count($ar1) > 1)
			{
				$ar2 = explode('»', $ar1[1]);
				if (count($ar2) > 1)
					return array_merge(self::getVars($ar1[0]), self::getVars($ar2[1]));
			}
			else
			{
				$ar1 = explode('“', $s);
				if (count($ar1) > 1)
				{
					$ar2 = explode('”', $ar1[1]);
					if (count($ar2) > 1)
						return array_merge(self::getVars($ar1[0]), self::getVars($ar2[1]));
				}
				else
				{
					$ar1 = explode('"', $s);
					if (count($ar1) > 2)
						return array_merge(self::getVars($ar1[0]), self::getVars($ar1[2]));
				}
			}

			$ar1 = explode(' - ', $s);
			if (count($ar1) > 1)
				return array_merge(self::getVars($ar1[0]), self::getVars($ar1[1]));
			$ar1 = explode(' – ', $s);
			if (count($ar1) > 1)
				return array_merge(self::getVars($ar1[0]), self::getVars($ar1[1]));
			$ar1 = explode(' | ', $s);
			if (count($ar1) > 1)
				return array_merge(self::getVars($ar1[0]), self::getVars($ar1[1]));

			$stop = array(
				'в интернет-магазине',
				'интернет-магазине',
				'в интернет магазине',
				'интернет магазине',
				'интернет-магазина',
				'интернет магазина',
				'интернет-магазин',
				'интернет магазин',
				'в онлайн-магазине',
				'в онлайн магазине',
				'онлайн-магазина',
				'онлайн магазина',
				'онлайн-магазин',
				'онлайн магазин',
				'в магазине',
				'в сети магазинов',
				'сеть магазинов',
				'магазинов',
				'магазины',
				'магазин',
				'в онлайн',
				'онлайн',
				'со скидкой',
			    'купить',
			    'с доставкой',
			    'с бесплатной доставкой',
			    'и бесплатной доставкой',
			    'с бесплатной доставкой',
			    'недорого',
			    'не дорого',
			    'дешево',
			    'дёшево',
			    'широкий ассортимент',
			    'широкий асортимент',
			    'большой ассортимент',
			    'большой асортимент',
			    'огромный ассортимент',
			    'огромный асортимент',
			    'по выгодным ценам',
			    'по выгодной цене',
			    'выгодные цены',
			    'по доступным ценам',
			    'по доступной цене',
			    'доступные цены',
			    'по самым низким ценам',
			    'по низким ценам',
			    'по низкой цене',
			    'самые низкие цены',
			    'низкие цены',
			    'цены',
			    'скидки',
			    'специальные предложения',
			    'для постоянных покупателей',
			    'бесплатная',
			    'доставка по',
			    'доставка',
			    'на следующий день',
			    'в ассортименте',
			    'оптом и в розницу',
			    'оптом',
			    'в розницу',
			    'ооо',
			    'от производителя',
			    'от официального дилера',
			    'рублей',
			    'продаем',
			    'продаём',
			    'продажа',
			    'под ключ',
			);

			foreach ($stop as $word)
			{
				$l = strlen($word);
				$p = strpos($s, $word);
				if ($p !== false)
					return array_merge(self::getVars(substr($s, 0, $p)), self::getVars(substr($s, $p + $l)));
			}

			$regions = Region::getAll();
			foreach ($regions as $region)
			{
				if (!$region['YANDEX'] || $region['NAME'] == 'Строитель' ||	$region['NAME'] == 'Центр' ||
					$region['NAME'] == 'Выборг' || strlen($region['NAME']) < 4)
					continue;

				$name = strtolower($region['NAME']);
				$name = ' ' . substr($name, 0, strlen($name) - 1);
				$pos = strpos(' ' . $s, $name);
				if ($pos !== false)
				{
					$s1 = trim(substr($s, 0, $pos));
					if (substr($s1, -4) == 'в г.')
						$s1 = substr($s1, 0, strlen($s1) - 4);
					if (substr($s1, -2) == 'г.')
						$s1 = substr($s1, 0, strlen($s1) - 2);
					if (substr($s1, -2) == ' в')
						$s1 = substr($s1, 0, strlen($s1) - 2);
					if (substr($s1, -3) == ' во')
						$s1 = substr($s1, 0, strlen($s1) - 3);

					$s2 = substr($s, $pos + strlen($name));
					$s2 = ltrim($s2, "абвгдеёжзийклмнопрстуфхцчшщъыьэюя");

					if ($s1 != 'в' && $s1 != 'во')
						return array_merge(self::getVars($s1), self::getVars($s2));
					else
						return self::getVars($s2);
				}
			}
		}

		return array($s => $s);
	}

	public static function parse1($url)
	{
		$return = array();

		$s = self::get($url);
		if ($s === false)
			return $return;

		$tags = self::getTags($s);

		if (strpos($s, 'charset=windows-1251') || strpos($s, 'charset=Windows-1251'))
			$s = $GLOBALS['APPLICATION']->ConvertCharset($s, "Windows-1251", "UTF-8");

		$return['META'] = array(
			'title' => $tags['title'],
			'description' => $tags['description'],
			'keywords' => $tags['keywords'],
			'h1' => $tags['h1'],
		);
		$return['PHONE'] = self::phone($s);
		$return['EMAIL'] = self::email($tags, $s);
		$return['NAME'] = self::name($tags['title']);
		$return['ESTORE'] = self::estore($tags['title'], $tags['description']);
		$return['PRODUCT'] = self::product($tags);


		if (!$return['PHONE']['number'] || !$return['EMAIL'])
		{
			$contacts = self::contactsPage($s);

			if ($contacts)
			{
				if (strpos($contacts, '//') !== false)
					$s = self::get($contacts);
				else
					$s = self::get($url . '/' . $contacts);
				if ($s !== false)
				{
					if (!$return['PHONE']['number'])
						$return['PHONE'] = self::phone($s);
					if (!$return['EMAIL'])
					{
						$tags = self::getTags($s);
						$return['EMAIL'] = self::email($tags, $s);
					}
				}
			}
		}

		debugmessage($return);

		$return['PRODUCT_'] = '<pre>' . print_r($return['META'], true) . '</pre>';
		$return['PRODUCT_'] .= '<pre>' . print_r($return['PRODUCT'], true) . '</pre>';

		return $return;
	}

}
