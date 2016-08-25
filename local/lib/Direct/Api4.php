<?
namespace Local\Direct;

use GuzzleHttp\Client;

class Api4
{
	/**
	 * @var Client HTTP клиент для запросов к API
	 */
	private $http;

	//private $url = 'https://api-sandbox.direct.yandex.ru/v4/json/';
	private $url = 'https://api.direct.yandex.ru/live/v4/json/';
	private $locale = 'ru';
	private $timeout = 5;

	/**
	 * @var string OAuth-токен для запросов
	 */
	private $token = '';

	/**
	 * @param $token string OAuth-токен для запросов
	 */
	public function __construct($token)
	{
		$this->token = $token;
		$this->http = new Client(array(
			'base_uri' => $this->url,
			'timeout' => $this->timeout,
		));
	}

	/**
	 * Отправляет POST запрос
	 * @param array $data
	 * @return mixed
	 */
	public function post($data = array())
	{
		$data = array_merge($data, array(
			'locale' => $this->locale,
			'token' => $this->token,
		));
		$response = $this->http->request('POST', '', array(
			'body' => json_encode($data, JSON_UNESCAPED_UNICODE),
		    'headers' => array(
			    'Content-Type' => 'application/json',
		    ),
		));
		$body = (string)$response->getBody();
		$result = json_decode($body, true);

		// Запись ошибки в журнал
		if ($result['error_code'])
		{
			$description = json_encode(array(
				'REQUEST' => $data,
				'RESPONSE' => $result,
			), JSON_UNESCAPED_UNICODE);

			$log = new \CEventLog();
			$log->Add(array(
				'SEVERITY' => 'WARNING',
				'AUDIT_TYPE_ID' => 'DIRECT_API_ERROR',
				'MODULE_ID' => 'main',
				'ITEM_ID' => $data['method'],
				'DESCRIPTION' => print_r($description, true),
			));
		}

		return $result;
	}

	/**
	 * Отправляет POST запрос с указанным методом
	 * @param string $method
	 * @param array $param
	 * @return mixed
	 */
	public function method($method, $param = array())
	{
		$data = array('method' => $method);
		if ($param)
			$data = array_merge($data, array(
				'param' => $param,
			));
		return $this->post($data);
	}

}
