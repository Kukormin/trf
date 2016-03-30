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
	<div class="container">

