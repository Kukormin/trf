<?
// Сюда директ перенаправляет пользователя после запроса получения OAuth-токена
// https://oauth.yandex.ru/authorize?response_type=token&client_id=...

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавление токена");

if ($_REQUEST['code'])
{
	$res = \Local\Project::addAnalyticsToken($_REQUEST['code']);
	if ($res)
	{
		?>
		<p class="text-error"><?= $res ?></p><?
	}
	else
	{
		LocalRedirect('/projects/new/');
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>