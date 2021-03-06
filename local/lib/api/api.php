<?

namespace Local\Api;

use Local\Utils;

abstract class Api
{
	/**
	 * Property: method
	 * HTTP метод (GET, POST, PUT или DELETE)
	 */
	protected $method = '';

	/**
	 * Property: endpoint
	 * Модель из URL (напр. /files)
	 */
	protected $endpoint = '';

	/**
	 * Property: args
	 * Другие аргументы, не попавшие в endpoint
	 */
	protected $args = Array();

	/**
	 * Property: file
	 * Содержит входные данные PUT запроса
	 */
	protected $file = Null;

	/**
	 * Property: file
	 * Содержит входные данные POST запроса
	 */
	protected $post = Null;

	/**
	 * Constructor: __construct
	 * Обработка параметров, подготовка
	 * @param $request
	 * @throws \Exception
	 */
	public function __construct($request)
	{
		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		//header("Content-Type: application/json");

		$this->args = explode('/', rtrim($request, '/'));
		$this->endpoint = array_shift($this->args);
		$this->method = $_SERVER['REQUEST_METHOD'];
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER))
		{
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE')
			{
				$this->method = 'DELETE';
			}
			else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
			{
				$this->method = 'PUT';
			}
			else
			{
				throw new ApiException('wrong_http_method', 405);
			}
		}

		switch ($this->method)
		{
			case 'DELETE':
				$this->request = $this->_cleanInputs($_POST);
				break;
			case 'POST':
				$this->request = $this->_cleanInputs($_POST);
				if ($this->request['data'])
					$this->post = json_decode($this->request['data'], true);
				else
					$this->post = json_decode(file_get_contents("php://input"), true);
				break;
			case 'GET':
				$this->request = $this->_cleanInputs($_GET);
				break;
			case 'PUT':
				$this->request = $this->_cleanInputs($_GET);
				$this->file = file_get_contents("php://input");
				break;
			default:
				throw new ApiException('method_not_allowed', 405);
				break;
		}
	}

	/**
	 * Передача управления заданному методу
	 * @return string
	 * @throws ApiException
	 */
	public function processAPI()
	{
		if (method_exists($this, $this->endpoint))
			return $this->_response($this->{$this->endpoint}($this->args));
		else
			throw new ApiException('wrong_endpoint', 404);
	}

	/**
	 * Возвращает ответ в JSON виде
	 * @param $data
	 * @param int $status
	 * @return string
	 */
	private function _response($data, $status = 200)
	{
		header('HTTP/1.1 ' . Utils::getHttpStatusByCode($status));
		return json_encode($data, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Очищает входные данные
	 * @param $data
	 * @return array|string
	 */
	private function _cleanInputs($data)
	{
		$clean_input = Array();
		if (is_array($data))
		{
			foreach ($data as $k => $v)
				$clean_input[$k] = $this->_cleanInputs($v);
		}
		else
			$clean_input = trim(strip_tags($data));

		return $clean_input;
	}

}