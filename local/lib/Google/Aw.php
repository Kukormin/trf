<?
namespace Local\Google;

/**
 * Дополнительные методы для работы с пользователем битрикса
 */
class Aw
{
	/**
	 * Префикс для клиентов в adwords
	 */
	const ADWORDS_PREFIX = 'traffo-';

	public static function createSubAccount($project)
	{
		$user = new \AdWordsUser();
		$user->SetClientCustomerId('583-295-5558');
		$user->LogAll();

		$managedCustomerService = $user->GetService('ManagedCustomerService', ADWORDS_VERSION);

		// Create customer.
		$customer = new \ManagedCustomer();
		$customer->name = self::ADWORDS_PREFIX . $project['ID'];
		$customer->currencyCode = 'RUB';
		$customer->dateTimeZone = 'Europe/Moscow';

		// Create operation.
		$operation = new \ManagedCustomerOperation();
		$operation->operator = 'ADD';
		$operation->operand = $customer;

		$operations = array($operation);

		// Make the mutate request.
		$result = $managedCustomerService->mutate($operations);

		return $result->value[0];

	}

}
