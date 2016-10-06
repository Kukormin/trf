<?
use Local\Direct\Clients;

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$assetInstance = \Bitrix\Main\Page\Asset::getInstance();
$assetInstance->addJs(SITE_TEMPLATE_PATH . '/js/import.js');
$APPLICATION->SetTitle("Личный кабинет");

$user = \Local\ExtUser::getCurrentUser();
// При первом входе предлагаем пользователю выбрать вид интерфейса
if ($user['INTERFACE']['NONE'])
{
	if ($_REQUEST['t'] == 'simple' || $_REQUEST['t'] == 'extended')
	{
		\Local\ExtUser::setInterface($_REQUEST['t']);
		$user = \Local\ExtUser::getCurrentUser();
	}

	if (!$user['UF_INTERFACE'])
	{
		?>
		<h2>Добро пожаловать, <?= $user['NAME'] ?></h2>
		<h4>Выберите вариант интерфейса</h4>
		<form method="GET">
			<div class="row-fluid">
				<div class="span6">
					<h3>Начинающий</h3>
					<p>Выберите данный интерфейс, если:</p>
					<ul>
						<li>Вы ни разу не запускали контекстную рекламу</li>
						<li>Вы хотите быстро и просто запустить контекстную рекламу</li>
						<li>Вам нужен упрощенный интерфейс запуска рекламы</li>
					</ul>
					<p>В любой момент вы можете изменить ваш интерфейс в самом верху сайта</p>
					<button class="btn btn-primary" type="submit" name="t" value="simple">Выбрать</button>
				</div>
				<div class="span6">
					<h3>Профессионал</h3>
					<p>Выберите данный интерфейс, если:</p>
					<ul>
						<li>Вы уже запускали рекламу в Яндекс.Директ и Google.Adwords</li>
						<li>Вы хотите более тщательно подготовиться к запуску рекламы</li>
						<li>Вам нужен более гибкий интерфейс запуска рекламы</li>
					</ul>
					<p>В любой момент вы можете изменить ваш интерфейс в самом верху сайта</p>
					<button class="btn btn-primary" type="submit" name="t" value="extended">Выбрать</button>
				</div>
			</div>
		</form><?
	}
}

if (!$user['INTERFACE']['NONE'])
{
	?>
	<?
}

/*?>
<h3>Кампании для пользователей директа:</h3><?
$clients = Clients::getByCurrentUser();
foreach ($clients['ITEMS'] as $client)
{
	$campaigns = \Local\Direct\Import::checkCampaigns($client);
	$showSyncButton = false;

	?>
	<h4><?= $client["NAME"] ?></h4>
	<table class="table table-striped table-hover">
	<thead>
	<tr>
		<th>Название</th>
		<th>№</th>
		<th>Статус</th>
		<th></th>
	</tr>
	</thead>
	<tbody><?

	foreach ($campaigns['ITEMS'] as $campaign)
	{
		$status = '';
		if ($campaign['ACTIVE'] != 'Y')
			$status = 'Не импортирована';
		else
		{
			if ($campaign['CHANGES'])
			{
				$status = 'Есть изменения для синхронизации';
				$showSyncButton = true;
			}
			else
				$status = 'Синхронизирована';
		}
		?>
		<tr>
		<td><?= $campaign['NAME'] ?></td>
		<td><?= $campaign['CampaignID'] ?></td>
		<td><?= $status ?></td>
		<td><?
			if ($campaign['ACTIVE'] != 'Y')
			{
				?>
				<a href="/personal/campaign/import.php?client=<?= $client['ID'] ?>&id=<?= $campaign['ID']
				?>">Импортировать</a><?
			}
			?>
		</td>
		</tr><?
	}

	?>
	</tbody>
	</table><?

	if ($showSyncButton)
	{
		?>
		<p>
			<button class="btn btn-primary client_sync" type="button" data-loading-text="В процессе..."
			        data-client="<?= $client['ID'] ?>">Синхронизировать</button>
		</p>
		<div class="results"></div><?
	}

}

?>
	<h3>Действия</h3>
	<p><a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=<?= DIRECT_API_CLIENT_ID
		?>">Подключить текущего пользователя Директа</a></p>
	<p><a href="update.php">Обновить данные</a></p>
	<p><a href="tmp.php">tmp</a></p><?*/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>