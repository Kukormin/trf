<?
include('IXR_Library.inc.php');

class mutagen
{
	/**
	 * @var IXR_Client
	 */
	private $ljClient;

	/**
	 * Получение конкуренции и подсказок
	 * @param $ljClient
	 */
	function mutagen($ljClient)
	{
		$this->ljClient = $ljClient;
	}

	/**
	 * Вызов метода API
	 * @param $option
	 * @return mixed
	 */
	function method($option)
	{
		$this->ljClient->query($option["method"], $option["var"]);
		$ljResponse = $this->ljClient->getResponse();

		if (isset($ljResponse["faultCode"]))
		{
			print $ljResponse["faultString"];
			exit();
		}
		else
		{
			return $ljResponse;
		}
	}

	/**
	 * Создает задание на проверку ключей
	 * @param $option
	 * @return mixed
	 */
	function key_create_task($option)
	{
		return $this->method(array(
				"method" => "mutagen.check_key.create_task",
				"var" => $option
			));
	}

	/**
	 * Проверяет, готово ли задание на проверку конкуренции
	 * @param $option
	 * @return mixed|string
	 */
	function key_get_task($option)
	{
		$data = $this->method(array(
				"method" => "mutagen.check_key.get_task",
				"var" => $option
			));

		if ($data["status"] == "completed")
		{
			return $data;
		}
		elseif ($data["status"] == "rejected")
		{
			return "fail";
		}
		else
		{
			sleep(5);
			return $this->key_get_task($option);
		}
	}

	/**
	 * Создает задание на получение ключей
	 * @param $option
	 * @return mixed
	 */
	function suggest_create_task($option)
	{
		return $this->method(array(
				"method" => "mutagen.suggest.create_task",
				"var" => $option
			));
	}

	/**
	 * Проверяет, готово ли задание
	 * @param $option
	 * @return mixed|string
	 */
	function suggest_get_task($option)
	{
		$data = $this->method(array(
				"method" => "mutagen.suggest.get_task",
				"var" => $option
			));

		if ($data["status"] == "completed")
		{
			return $data;
		}
		elseif ($data["status"] == "rejected")
		{
			return "fail";
		}
		else
		{
			sleep(5);
			return $this->suggest_get_task($option);
		}
	}

	function getWords($words, $regionId)
	{

		/*$regionId = 2;
		$this->ljClient->query('mutagen.parser.mass.new');//, $words);//, "wordstat_n");//, "tmp", $regionId);
		$ljResponse = $this->ljClient->getResponse();
		debugmessage($ljResponse);*/

		debugmessage($words);
		$x = implode("\n", $words);
		/*$words = array(
			"прокат мерседес",
			"прокат мерседес черный",
			"прокат мерседес white",
			"прокат мерс",
		);*/
		debugmessage($x);
		$this->ljClient->query('mutagen.parser.mass.new', $x, "wordstat_n", $regionId, "проверка из api");
		$ljResponse = $this->ljClient->getResponse();
		debugmessage($ljResponse);

		/*$this->ljClient->query('mutagen.parser.mass.list');
		$ljResponse = $this->ljClient->getResponse();

		$last_mass_id = key($ljResponse);*/

		//return $this->getWordsWait($last_mass_id);
	}

	private function getWordsWait($id)
	{

		$this->ljClient->query('mutagen.parser.mass.id', $id);
		$data = $this->ljClient->getResponse();
		debugmessage($data);
		return $data;

		if ($data["status"] == "completed" || $data["status"] == "finish")
			return $data;
		elseif ($data["status"] == "rejected")
			return "fail";
		else
		{
			sleep(5);
			return $this->getWordsWait($id);
		}
	}
}
