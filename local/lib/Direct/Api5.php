<?
namespace Local\Direct;

use GuzzleHttp\Client;

class Api5
{
	/**
	 * @var Client HTTP клиент для запросов к API
	 */
	private $http;

	//private $url = 'https://api-sandbox.direct.yandex.com/json/v5/';
	private $url = 'https://api.direct.yandex.com/json/v5/';
	private $locale = 'ru';
	private $timeout = 20;

	/**
	 * @var string OAuth-токен для запросов
	 */
	private $token = '';

	/**
	 * @var string логин клиента
	 */
	private $clientLogin = '';

	/**
	 * @param string $token OAuth-токен для запросов
	 * @param $clientLogin
	 * @param string $service сервис
	 */
	public function __construct($token, $clientLogin, $service = '')
	{
		$this->token = $token;
		$this->clientLogin = $clientLogin;
		$this->http = new Client(array(
			'base_uri' => $this->url . $service,
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
		$requestBody = json_encode($data, JSON_UNESCAPED_UNICODE);
		$headers = array(
			'Content-Type' => 'application/json; charset=utf-8',
			'Accept-Language' => $this->locale,
			'Authorization' => 'Bearer ' . $this->token,
		);
		if ($this->clientLogin)
			$headers['Client-Login'] = $this->clientLogin;
		$response = $this->http->request('POST', '', array(
			'body' => $requestBody,
		    'headers' => $headers,
		));
		$body = (string)$response->getBody();
		// TODO: лимит баллов
		//$units = $response->getHeader('Units');
		//debugmessage($units);
		$result = json_decode($body, true);

		// Запись ошибки в журнал
		if ($result['error'])
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
				'ITEM_ID' => $this->clientLogin,
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
				'params' => $param,
			));
		return $this->post($data);
	}

}
