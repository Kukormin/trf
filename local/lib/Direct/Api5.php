<?
namespace Local\Direct;

use GuzzleHttp\Client;

class Api5
{
	/**
	 * @var Client HTTP клиент для запросов к API
	 */
	private $client;

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
		$this->client = new Client(array(
			//'base_uri' => 'https://api-sandbox.direct.yandex.com/json/v5/' . $service,
			'base_uri' => 'https://api.direct.yandex.com/json/v5/' . $service,
			'timeout' => 5,
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
		$response = $this->client->request('POST', '', array(
			'body' => $requestBody,
		    'headers' => array(
			    'Content-Type' => 'application/json; charset=utf-8',
		        'Authorization' => 'Bearer ' . $this->token,
		        'Client-Login' => $this->clientLogin,
		    ),
		));
		$body = (string)$response->getBody();
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
