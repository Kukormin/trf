<?
namespace Local\Direct;

use GuzzleHttp\Client;

class Api4
{
	/**
	 * @var Client HTTP клиент для запросов к API
	 */
	private $client;

	/**
	 * @var string
	 */
	private $locale = 'ru';

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
		$this->client = new Client(array(
			//'base_uri' => 'https://api-sandbox.direct.yandex.ru/v4/json/',
			'base_uri' => 'https://api.direct.yandex.ru/v4/json/',
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
		$data = array_merge($data, array(
			'locale' => $this->locale,
			'token' => $this->token,
		));
		$response = $this->client->request('POST', '', array(
			'body' => json_encode($data, JSON_UNESCAPED_UNICODE),
		    'headers' => array(
			    'Content-Type' => 'application/json',
		    ),
		));
		$body = (string)$response->getBody();
		$result = json_decode($body, true);

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
