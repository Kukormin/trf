<?

namespace Local\Api;

use Local\Utils;

class ApiException extends \Exception
{
	/**
	 * @var int|string http статус
	 */
	protected $status = '';
	/**
	 * @var array строковые коды ошибок
	 */
	protected $error = array();

	/**
	 * Выкидывает исключение
	 * @param mixed $error ошибка
	 * @param int $status HTTP статус
	 * @param string $message сообщение
	 */
	public function __construct($error, $status = 500, $message = '')
	{
		parent::__construct($message);
		$this->status = $status;
		$this->error = $error;
	}

	/**
	 * Возвращает HTTP статус по коду
	 * @return string
	 */
	public function getHttpStatus()
	{
		return Utils::getHttpStatusByCode($this->status);
	}

	/**
	 * Возвращает ошибки
	 * @return array
	 */
	public function getError()
	{
		return $this->error;
	}
}