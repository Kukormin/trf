<!DOCTYPE html>
<html>
<head><?

	/** @var CMain $APPLICATION */
	/** @var CUser $USER */

	?>
	<title><?$APPLICATION->ShowTitle();?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?

	$assetInstance = \Bitrix\Main\Page\Asset::getInstance();
	$assetInstance->addCss(SITE_TEMPLATE_PATH . '/css/bootstrap.min.css', true);
	$APPLICATION->ShowCSS();
	?>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/init.js"></script>
</head>
<body><?
	//$APPLICATION->ShowPanel();
	$user = \Local\Main\User::getCurrentUser();

	?>
	<div class="container-fluid">
		<div class="navbar">
			<div class="navbar-inner">

				<a class="brand" href="/">Главная</a>

				<div class="nav-collapse collapse navbar-inverse-collapse">
					<ul class="nav">
						<li>
							<a href="/bitrix/admin/">(Админка)</a>
						</li><?

						if ($USER->IsAuthorized())
						{
							$APPLICATION->AddBufferContent(Array('\Local\Main\Project', "getHeaderMenu"));
						}

						?>
					</ul>
					<ul class="nav pull-right"><?

						if ($USER->IsAuthorized())
						{
							?>
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									Режим: <?= $user['INTERFACE']['EXTENDED'] ? 'Эксперт' : 'Начинающий' ?>
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li class="<?= $user['INTERFACE']['EXTENDED'] ? 'user-settings-regime' : 'disabled'
									?>">
										<a href="javascript:void(0)" data-id="simple">Начинающий</a>
									</li>
									<li class="<?= $user['INTERFACE']['EXTENDED'] ? 'user-settings' : 'user-settings-regime' ?>">
										<a href="javascript:void(0)" data-id="extended">Эксперт</a>
									</li>
								</ul>
							</li>
							<li>
								<a href="/personal/settings/">Настройки</a>
							</li>
							<li>
								<a href="/personal/">Личный кабинет</a>
							</li><?
						}
						else
						{
							?><li>
								<a href="/register/">Регистрация</a>
							</li><?
						}

						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="container-fluid"><?

		$APPLICATION->IncludeComponent('bitrix:breadcrumb', '', Array());

		?>
		<div class="row-fluid">
			<div class="span2"><?

				$APPLICATION->IncludeComponent('tim:empty', 'nav_tree', Array());

				?>
			</div>
			<div class="span10">
				<h1><? $APPLICATION->ShowTitle(false, false); ?></h1>

