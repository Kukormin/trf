<?
// Сюда директ перенаправляет пользователя после запроса получения OAuth-токена
// https://oauth.yandex.ru/authorize?response_type=token&client_id=...

use Local\Direct\Clients;

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавление токена");

?>
	<script>if (location.hash) location.href = '?' + location.hash.substr(1);</script><?

if ($_REQUEST['error'] == 'access_denied')
{
	?>
	<p class="text-error">Пользователь отказал приложению в доступе.</p><?
}
elseif ($_REQUEST['error'] == 'unauthorized_client')
{
	?>
	<p class="text-error">Приложение отключено.</p><?
}
elseif ($_REQUEST['access_token'])
{
	$res = Clients::addToken($_REQUEST['access_token']);
	if ($res)
	{
		?>
		<p class="text-error"><?= $res ?></p><?
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>