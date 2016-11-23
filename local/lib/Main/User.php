<?
namespace Local\Main;

/**
 * Дополнительные методы для работы с пользователем битрикса
 */
class User
{
	const INTERFACE_SIMPLE = 1;
	const INTERFACE_EXTENDED = 2;

	/**
	 * @var bool Пользователь
	 */
	private static $user = false;

	public static function getCurrentUserId()
	{
		$user = new \CUser();
		return $user->GetID();
	}

	public static function getCurrentUser()
	{
		if (self::$user === false)
		{
			$u = new \CUser();
			$userId = $u->GetID();
			if ($userId)
			{
				$rs = $u->GetByID($userId);
				$user = $rs->Fetch();
				if (!$user['UF_DATA'])
					$user['UF_DATA'] = '{}';
				self::$user = array(
					'ID' => $userId,
					'NAME' => $user['NAME'],
					'INTERFACE' => array(
						'SIMPLE' => $user['UF_INTERFACE'] == 1,
						'EXTENDED' => $user['UF_INTERFACE'] == 2,
						'NONE' => !$user['UF_INTERFACE'],
					),
					'UF_DATA' => $user['UF_DATA'],
					'DATA' => json_decode($user['UF_DATA'], true),
				);
			}
			else
				self::$user = array();
		}

		return self::$user;
	}

	public static function saveData($new)
	{
		self::getCurrentUser();

		$data = self::$user['DATA'];
		foreach ($new as $key => $value)
			$data[$key] = $value;

		$encoded = json_encode($data, JSON_UNESCAPED_UNICODE);

		if (self::$user['UF_DATA'] != $encoded)
		{
			$u = new \CUser();
			$u->Update(self::$user['ID'], array(
				'UF_DATA' => $encoded,
			));
			self::$user['DATA'] = $data;
		}
	}

	public static function setInterface($t)
	{
		$user = new \CUser();
		$userId = $user->GetID();
		if (!$userId)
			return false;

		$id = 0;
		if ($t == 'simple')
			$id = self::INTERFACE_SIMPLE;
		elseif ($t == 'extended')
			$id = self::INTERFACE_EXTENDED;
		if (!$id)
			return false;

		$user->Update($userId, array(
			'UF_INTERFACE' => $id,
		));

		// При переключении интерфейса сбрасываем шаги в создании проекта
		$project = Project::getAdding();
		if ($project)
		{
			$step = $project['STEP'];
			if ($step > 10)
			{
				$fields = array(
					'STEP' => 10,
				);
				Project::update($project, $fields);
			}
		}


		return true;
	}

}
