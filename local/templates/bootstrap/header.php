<!DOCTYPE html>
<html>
<head><?

	/** @var CMain $APPLICATION */
	?>
	<title><?$APPLICATION->ShowTitle();?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"><?

	$assetInstance = \Bitrix\Main\Page\Asset::getInstance();
	$assetInstance->addCss(SITE_TEMPLATE_PATH . '/css/bootstrap.min.css', true);
	$assetInstance->addJs(SITE_TEMPLATE_PATH . '/js/jquery.js');
	$assetInstance->addJs(SITE_TEMPLATE_PATH . '/js/bootstrap.min.js');
	$APPLICATION->ShowHead();
	?>
</head>
<body><?
	$APPLICATION->ShowPanel();
	?>
	<header>
		<div class="container">
			<hr />
			<a href="/">Главная</a>
			<a style="float:right;" href="/personal/">Личный кабинет</a>
			<hr />
		</div>
	</header>
	<div class="container">

