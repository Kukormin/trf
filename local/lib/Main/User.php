<?
namespace Local\Main;

/**
 * Дополнительные методы для работы с пользователем битрикса
 */
class User
{
	const INTERFACE_SIMPLE = 1;
	const INTERFACE_EXTENDED = 2;

	public static function getCurrentUserId()
	{
		$user = new \CUser();
		return $user->GetID();
	}

	public static function getCurrentUser()
	{
		$return = [];
		$user = new \CUser();
		$userId = $user->GetID();
		if ($userId)
		{
			$rs = $user->GetByID($userId);
			$return = $rs->Fetch();
			$return['INTERFACE'] = array(
				'SIMPLE' => $return['UF_INTERFACE'] == 1,
				'EXTENDED' => $return['UF_INTERFACE'] == 2,
			    'NONE' => !$return['UF_INTERFACE'],
			);
		}

		return $return;
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
