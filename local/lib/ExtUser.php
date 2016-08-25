<?
namespace Local;

/**
 * Дополнительные методы для работы с пользователем битрикса
 */
class ExtUser
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

		return true;
	}

}
