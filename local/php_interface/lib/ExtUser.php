<?
namespace Local;

/**
 * Дополнительные методы для работы с пользователем битрикса
 */
class ExtUser
{
	public static function getCurrentUserId()
	{
		$user = new \CUser();
		return $user->GetID();
	}
}
