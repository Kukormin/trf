<!DOCTYPE html>
<html>
<head><?

	/** @var CMain $APPLICATION */
	?>
	<title><?$APPLICATION->ShowTitle();?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"><?

	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/css/bootstrap.min.css', true);
	$APPLICATION->ShowHead();
	?>
</head>
<body><?
	$APPLICATION->ShowPanel();
	?>
	<div class="container">

