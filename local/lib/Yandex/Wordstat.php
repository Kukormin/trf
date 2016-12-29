<?
namespace Local\Yandex;

use Bitrix\Highloadblock\HighloadBlockTable;
use Local\Main\Category;
use Local\System\ExtCache;
use Local\System\Http;
use Local\Utils\Log;

class Wordstat
{
	/**
	 * ID HL-блока
	 */
	const ENTITY_ID = 8;
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Yandex/Wordstat/';
	/**
	 * Время актуальности значения частотности
	 */
	const EXPIRES = 2592000; // 30 дней

	private $http;
	private $cookie = '/_log/ws/cookie.txt';
	private $logFilename = '/_log/ws/log/';
	private $login = 'www-ros-dengi-ru-2';
	private $pass = '12www-ros-dengi-ru-2';
	private $logDir = '/_log/ws/cache/';
	private $dataClass;
	private $now;
	private $categoryId;
	private $categoryWords = false;
	private $clearCache = false;
	/** @var Log */
	private $log;

	private static $STOP_WORDS = array('a','about','all','am','an','and','any','are','as','at','be','been','but','by',
	                                  'can','could','do','for','from','has','have','i','if','in','is','it','me','my',
	                                  'no','not','of','on','one','or','so','that','the','them','there','they','this',
	                                  'to','was','we','what','which','will','with','would','you','а','будем','будет',
	                                  'будете','будешь','буду','будут','будучи','будь','будьте','бы','был','была',
	                                  'были','было','быть','в','вам','вами','вас','весь','во','вот','все','всё','всего',
	                                  'всей','всем','всём','всеми','всему','всех','всею','всея','всю','вся','вы','да',
	                                  'для','до','его','едим','едят','ее','её','ей','ел','ела','ем','ему','емъ','если',
	                                  'ест','есть','ешь','еще','ещё','ею','же','за','и','из','или','им','ими','имъ',
	                                  'их','к','как','кем','ко','когда','кого','ком','кому','комья','которая',
	                                  'которого','которое','которой','котором','которому','которою','которую','которые',
	                                  'который','которым','которыми','которых','кто','меня','мне','мной','мною','мог',
	                                  'моги','могите','могла','могли','могло','могу','могут','мое','моё','моего','моей',
	                                  'моем','моём','моему','моею','можем','может','можете','можешь','мои','мой','моим',
	                                  'моими','моих','мочь','мою','моя','мы','на','нам','нами','нас','наса','наш',
	                                  'наша','наше','нашего','нашей','нашем','нашему','нашею','наши','нашим','нашими',
	                                  'наших','нашу','не','него','нее','неё','ней','нем','нём','нему','нет','нею','ним',
	                                  'ними','них','но','о','об','один','одна','одни','одним','одними','одних','одно',
	                                  'одного','одной','одном','одному','одною','одну','он','она','оне','они','оно',
	                                  'от','по','при','с','сам','сама','сами','самим','самими','самих','само','самого',
	                                  'самом','самому','саму','свое','своё','своего','своей','своем','своём','своему',
	                                  'своею','свои','свой','своим','своими','своих','свою','своя','себе','себя',
	                                  'собой','собою','та','так','такая','такие','таким','такими','таких','такого',
	                                  'такое','такой','таком','такому','такою','такую','те','тебе','тебя','тем','теми',
	                                  'тех','то','тобой','тобою','того','той','только','том','томах','тому','тот','тою',
	                                  'ту','ты','у','уже','чего','чем','чём','чему','что','чтобы','эта','эти','этим',
	                                  'этими','этих','это','этого','этой','этом','этому','этот','этою','эту','я','мені',
	                                  'наші','нашої','нашій','нашою','нашім','ті','тієї','тією','тії','теє');

	public function __construct($categoryId)
	{
		$this->http = new Http(array(
			'cookie' => $this->cookie,
		));
		$this->cookie = $_SERVER['DOCUMENT_ROOT'] . $this->cookie;
		$this->logDir = $_SERVER['DOCUMENT_ROOT'] . $this->logDir;

		$entityInfo = HighloadBlockTable::getById(static::ENTITY_ID)->Fetch();
		$entity = HighloadBlockTable::compileEntity($entityInfo);
		$this->dataClass = $entity->getDataClass();

		$this->now = time();
		$this->categoryId = intval($categoryId);
	}

	/**
	 * Возвращает существующие значения частотности для категории
	 * @param bool $refreshCache
	 * @return array|mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getCategoryWordsDB($refreshCache = false)
	{
		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$this->categoryId,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			8640000,
			false,
			false
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$dataClass = $this->dataClass;
			$rsItems = $dataClass::getList(array(
				'filter' => array(
					'UF_CATEGORY' => $this->categoryId,
				),
			));
			$return = array();
			while ($item = $rsItems->Fetch())
				$return[$item['UF_WORD']] = array(
					'ID' => $item['ID'],
					'VALUE' => $item['UF_VALUE'],
					'TS' => intval($item['UF_TS']),
				);

			$extCache->endDataCache($return);
		}

		return $return;
	}

	/**
	 * Возвращает существующие значения частотности для категории с учетом статического кеша
	 * @return array|bool|mixed
	 */
	public function getCategoryWords()
	{
		if ($this->categoryWords === false)
		{
			$this->categoryWords = $this->getCategoryWordsDB();
			foreach ($this->categoryWords as &$item)
				$item['ACTIVE'] = $item['TS'] + static::EXPIRES > $this->now;
			unset($item);
		}

		return $this->categoryWords;
	}

	/**
	 * Возвращает частотность для нормализованной фразы
	 * @param $norm
	 * @return mixed
	 */
	public function get($norm)
	{
		$words = $this->getCategoryWords();
		return $words[$norm];
	}

	/**
	 * Возвращает значение частотности для фразы
	 * @param $phrase
	 * @return mixed
	 */
	public function getValue($phrase)
	{
		$norm = $this->normalize($phrase);
		$words = $this->getCategoryWords();
		return $words[$norm]['VALUE'];
	}

	/**
	 * Нормализует фразу
	 * @param $phrase
	 * @return string
	 */
	public function normalize($phrase)
	{
		$words = explode(' ', $phrase);
		$tmp = array();
		foreach ($words as $word)
			if ($word)
			{
				$lower = strtolower($word);
				if (!in_array($lower, self::$STOP_WORDS))
					$tmp[$lower] = $lower;
			}
		sort($tmp);
		return implode(' ', $tmp);
	}

	/**
	 * Проверяет частотности для заданного списка фраз
	 * @param $words
	 */
	public function checkWords($words)
	{
		$this->newLog();
		$this->log('Проверка списка слов');
		$this->clearCache = false;
		foreach ($words as $word)
		{
			$norm = $this->normalize($word);
			$cw = $this->get($norm);

			if ($cw['ACTIVE'])
				continue;

			$stat = $this->getStat($norm);
			foreach ($stat['words'] as $w => $v)
				$this->add($w, $v);

			sleep(3);
		}
		if ($this->clearCache)
			$this->getCategoryWordsDB(true);
	}

	/**
	 * Проверяет частотности для заданного списка фраз в виде базовых слов
	 * Отличается от checkWords тем, что проставляет нули дочерним фразам, если в родительской значение равно нулю
	 * @param $items
	 */
	public function checkBaseWords($items)
	{
		$this->newLog();
		$this->log('Проверка базовых слов');
		$this->log('Категория: ' . $this->categoryId);
		$this->clearCache = false;
		$l = count($items);
		foreach ($items as $i => $item)
		{
			$word = Category::getWordFromBase($item);
			$norm = $this->normalize($word);
			$cw = $this->get($norm);

			if ($cw['ACTIVE'])
				continue;

			$stat = $this->getStat($norm);
			if ($stat['error'] == 'captcha')
				break;

			$first = true;
			foreach ($stat['words'] as $w => $v)
			{
				$this->add($w, $v);
				if (!$first)
					continue;

				$first = false;
				if ($v == 0)
				{
					for ($j = $i + 1; $j < $l; $j++)
					{
						$cur = $items[$j];
						$child = true;
						foreach ($item as $p => $w)
						{
							if ($w && $w != $cur[$p])
							{
								$child = false;
								break;
							}
						}
						if ($child)
						{
							$tmp = $this->normalize(Category::getWordFromBase($cur));
							$this->add($tmp, 0);
						}
					}
				}
			}

			sleep(3);
		}
		if ($this->clearCache)
			$this->getCategoryWordsDB(true);
	}

	/**
	 * Добавляет фразу и значение частотности в БД
	 * @param $norm
	 * @param $value
	 */
	private function add($norm, $value)
	{
		$updated = false;
		$item = $this->get($norm);
		$dataClass = $this->dataClass;
		if ($item['ID'])
		{
			if ($value != $item['VALUE'])
			{
				$dataClass::update($item['ID'], array(
					'UF_VALUE' => $value,
					'UF_TS' => $this->now,
				));
				$updated = true;
			}
		}
		else
		{
			$dataClass::add(array(
				'UF_WORD' => $norm,
				'UF_VALUE' => $value,
				'UF_TS' => $this->now,
				'UF_CATEGORY' => $this->categoryId,
			));
			$updated = true;
		}

		if ($updated)
		{
			$this->categoryWords[$norm] = array(
				'VALUE' => $value,
				'TS' => $this->now,
			    'ACTIVE' => true,
			);
			$this->clearCache = true;
		}
	}

	/**
	 * Получение значения частотности от wordstat
	 * @param $norm
	 * @return array
	 */
	private function getStat($norm)
	{
		$fuid = $this->getFuid();
		if (!$fuid)
		{
			$logged = $this->login();
			if ($logged)
				$fuid = $this->getFuid();
			else
				return array(
					'error' => 'login',
				);
		}

		if (!$fuid)
			return array(
				'error' => 'fuid',
			);

		$name = md5($norm) . '.1';
		$filename = $this->logDir . $name;
		$response = '';
		$cache = false;
		if (file_exists($filename))
		{
			$response = file_get_contents($filename);
			if ($response)
				$cache = true;
		}
		if (!$cache)
		{
			$url = 'https://wordstat.yandex.ru/stat/words';
			$post = 'db=&filter=all&map=world&page=1&page_type=words&period=monthly&regions=&sort=cnt&type=list&words=' . $norm;
			$headers = $this->http->getHeaders();
			$headers[] = 'X-Requested-With: XMLHttpRequest';
			$response = $this->http->post($url, $post, $headers);
		}

		$curValue = -1;
		if ($response)
		{
			$decoded = json_decode($response, true);
			if ($decoded['captcha'])
			{
				$return = array(
					'error' => 'captcha',
				);
			}
			elseif ($decoded['key'])
			{
				$this->saveLog($response, $name);
				$key = $this->decodeKey($decoded['key']);
				$userAgent = $this->http->getUserAgent();
				$keystring = substr($userAgent, 0, 25) . $fuid . $key;
				$kl = strlen($keystring);
				$data = $decoded['data'];
				$l = strlen($data);
				$res = '';
				for ($i = 0; $i < $l; $i++)
					$res .= chr(ord($data[$i]) ^ ord($keystring[$i % $kl]));
				$tmp = urldecode($res);
				$this->saveLog($tmp, $name . '1');

				$ar = json_decode($tmp, true);
				if ($ar['content'])
				{
					$curValue = $this->getNumberValue($ar['content']['includingPhrases']['info'][2]);
					$return['words'][$norm] = $curValue;
					$contentKeys = array(
						'includingPhrases',
						'phrasesAssociations'
					);
					foreach ($contentKeys as $contentKey)
						foreach ($ar['content'][$contentKey]['items'] as $item)
						{
							$phrase = $this->normalize($item['phrase']);
							$return['words'][$phrase] = $this->getNumberValue($item['number']);
						}
				}
				else
					$return = array(
						'error' => 'no_content',
					);
			}
			else
				$return = array(
					'error' => 'miss_key',
				);
		}
		else
			$return = array(
				'error' => 'no_response',
			);

		if ($return['error'])
			$this->log($norm . ' - ' . $return['error']);
		else
			$this->log($norm . ' - ' . $curValue . ' (' . count($return['words']) . ')');

		$return['cache'] = $cache;

		return $return;
	}

	/**
	 * Убирает лишние пробелы из чисел wordstat
	 * @param $num
	 * @return int
	 */
	private function getNumberValue($num)
	{
		$f = '';
		$i = 0;
		while (true)
		{
			$ord = ord($num[$i]);
			if ($ord == 0)
				break;
			if ($ord > 47 && $ord < 60)
				$f .= $num[$i];
			$i++;
		}
		return intval($f);
	}

	/**
	 * Получает значение важной куки
	 * @return string
	 */
	private function getFuid()
	{
		$cookie = file_get_contents($this->cookie);
		$cr = (strpos($cookie, "\r\n") !== false) ? "\r\n" : "\n";
		$rows = explode($cr, trim($cookie, $cr));
		$fuid = '';
		foreach ($rows as $i => $row)
		{
			if (strpos($row, "\tfuid01\t") === false)
				continue;
			$cols = explode("\t", $row);
			$fuid = $cols[6];
		}

		return $fuid;
	}

	/**
	 * Логинится на сервисе wordstat
	 * @return bool
	 */
	private function login()
	{
		// Главная страница
		$response = $this->http->get('https://wordstat.yandex.ru/');
		$this->saveLog($response, 'index.html');

		if (strpos($response, 'mode=logout') !== false)
			return true;

		// Запрос на авторизацию
		$ts = round(getmicrotime() * 1000);
		$url = 'https://passport.yandex.ru/passport?mode=auth&from=&retpath=&twoweeks=yes';
		$post = 'login=' . $this->login . '&passwd=' . $this->pass . '&timestamp=' . $ts;
		$response = $this->http->post($url, $post);
		$this->saveLog($response, 'login.html');

		// Получение нужных куков
		$kiks = $this->http->get('https://kiks.yandex.ru/su/');
		$this->saveLog($kiks, 'kiks.html');

		return strpos($response, 'mode=logout') !== false;
	}

	/**
	 * Сохранение ответа от wordstat
	 * @param $response
	 * @param $filename
	 */
	private function saveLog($response, $filename)
	{
		$file = $this->logDir . $filename;
		file_put_contents($file, $response);
	}

	/**
	 * Получает ключ для декодирования ответа от wordstat
	 * @param $key
	 * @return string
	 */
	private function decodeKey($key)
	{
		$ar = explode('"', $key);
		$cnt = count($ar);
		if ($cnt == 5)
		{
			$tmp = $this->f2($ar[3], $ar[4]);
			return $ar[1] . $tmp;
		}
		elseif ($cnt == 7)
		{
			$tmp = $this->f2($ar[5], $ar[6]);
			return $ar[1] . $ar[3] . $tmp;
		}

		return '';
	}

	/**
	 * Последовательно применяет операции js к строке
	 * @param $tmp
	 * @param $op
	 * @return array|string
	 */
	private function f2($tmp, $op)
	{
		// .concat(972859^87560).substr(4).split('a').join('b').split('a').join('b').split('').reverse().join('')
		$ar1 = explode('.', $op);
		foreach ($ar1 as $k)
		{
			$key = substr($k, 0, 4);
			if ($key == 'spli')
			{
				$ar2 = explode("('", $k);
				if (count($ar2) == 2)
				{
					$ar3 = explode("')", $ar2[1]);
					if (count($ar3) == 2)
						$tmp = $this->split($ar3[0], $tmp);
				}
			}
			elseif ($key == 'join')
			{
				$ar2 = explode("('", $k);
				if (count($ar2) == 2)
				{
					$ar3 = explode("')", $ar2[1]);
					if (count($ar3) == 2)
						$tmp = implode($ar3[0], $tmp);
				}
			}
			elseif ($key == 'conc')
			{
				$ar2 = explode("(", $k);
				if (count($ar2) == 2)
				{
					$ar3 = explode(")", $ar2[1]);
					if (count($ar3) == 2)
					{
						$ar4 = explode("^", $ar3[0]);
						if (count($ar4) == 2)
							$tmp .= intval($ar4[0]) ^ intval($ar4[1]);
					}
				}
			}
			elseif ($key == 'subs')
			{
				$ar2 = explode("(", $k);
				if (count($ar2) > 1)
				{
					$ar3 = explode(")", $ar2[1]);
					if (count($ar3) > 1)
						$tmp = substr($tmp, $ar3[0]);
				}
			}
			elseif ($key == 'reve')
			{
				$tmp = array_reverse($tmp);
			}
		}
		return $tmp;
	}

	/**
	 * Аналог js функции split
	 * @param $char
	 * @param $tmp
	 * @return array
	 */
	private function split($char, $tmp)
	{
		if ($char === '')
			return str_split($tmp);

		$return = array();
		$l = strlen($tmp);
		$cur = '';
		for ($i = 0; $i < $l; $i++)
		{
			if ($tmp[$i] == $char)
			{
				$return[] = $cur;
				$cur = '';
			}
			else
				$cur .= $tmp[$i];
		}
		$return[] = $cur;

		return $return;
	}

	/**
	 * Создает файл лога
	 */
	private function newLog()
	{
		$this->log = new Log($this->logFilename . date('Y_m_d') . '.txt');
	}

	/**
	 * Сохраняет строку в лог
	 * @param $text
	 */
	private function log($text)
	{
		$this->log->writeText($text);
	}

}
