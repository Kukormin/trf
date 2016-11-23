<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC", "Y");
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$projectId = intval($_REQUEST['pid']);
$project = \Local\Main\Project::getById($projectId);
$href = $project['URL'];

$data = $_REQUEST['data'];
$host = 'test.ru';
$metro = '';
$punct = $data['Country'] && $data['City'] ? ', ' : '';
$location = $data['Country'] . $punct . $data['City'];
$adTitle = 'Заголовок объявления!';
$adText = 'Текст объявления. Преимущества. Звони!';
$phone = $data['Phone']['CountryCode'] . ' (' . $data['Phone']['CityCode'] . ') ' . $data['Phone']['PhoneNumber'];
if ($data['Phone']['Extension'])
	$phone .= ' доб.' . $data['Phone']['Extension'];
$address = $data['Street'];
if ($data['House'])
{
	if ($address)
		$address .= ', ';
	$address .= 'д. ' . $data['House'];
}
if ($data['Building'])
{
	if ($address)
		$address .= ', ';
	$address .= 'корп. ' . $data['Building'];
}
if ($data['Apartment'])
{
	if ($address)
		$address .= ', ';
	$address .= 'офис ' . $data['Apartment'];
}

$regime = \Local\Main\Vcard::getRegimeParts($data['WorkTime']);

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="format-detection" content="telephone=no">
	<link type="text/css" rel="stylesheet" href="style.css" />
	<title>Контактная информация</title>
</head>
<body>
	<div class="page">
		<div class="page-section print-button-container">
			<a class="print-button" href="#" onclick="return _print()">
				<span class="print-button__icon"></span>Печать
			</a>
		</div>
		<div class="page-section">
			<h1 id="title" class="title dont-break-out"><?= trim($data['CompanyName']) ?></h1>
			<div class="head-link large-text"><a href="http://<?= $href ?>"><?= $href ?></a></div>
		</div>
		<div id="location" class="page-section large-text"><?= $location ?></div>
		<div class="page-section description"><?= trim($data['ExtraMessage']) ?></div>
		<div class="separator"></div>
		<div class="page-section">
			<div class="page-section banner">
				<div class="banner-title"><a href="http://<?= $href ?>"><?= $adTitle ?></a></div>
				<div class="banner-content"><?= $adText ?></div>
				<div class="banner-link"><a href="http://<?= $href ?>"><?= $href ?></a></div>
			</div>
			<div class="contact-item call-button-container">
				<div class="hint">Телефон</div>
				<div class="large-text"><?= $phone ?></div>
			</div>
			<div class="contact-item">
				<div class="hint">Время работы</div>
				<ul class="large-text work-time"><?
					foreach ($regime as $item)
					{
						?>
						<li><div><?= $item[0] ?></div><?= $item[1] ?></li><?
					}
					?>
				</ul>
			</div>
			<div class="contact-item">
				<div class="hint">Адрес</div>
				<div id="address" class="large-text"><?= $address ?></div>
				<div class="large-text"><?= $metro ?></div>
			</div>
		</div>
		<div class="map-container">
			<div id="map-desktop" class="map"></div>
		</div>
	</div>

	<script src="https://api-maps.yandex.ru/2.1/?load=package.standard&lang=ru-RU"></script>
	<script>
		function _print(){return window.print(),!1}
		function _trim(e) {
			return (e + "").replace(/^s+/g, "").replace(/s+$/g, "");
		}
		ymaps.ready(function () {
			var e = _trim(document.getElementById("title").innerHTML);
			var t = _trim(document.getElementById("address").innerHTML);
			var n = _trim(document.getElementById("location").innerHTML);
			var p = <?= $data['PointOnMap']['Y'] ?>;
			var s = <?= $data['PointOnMap']['X'] ?>;
			var m = n + ", " + t;
			var i = {
				bounds: [[<?= $data['PointOnMap']['X1'] ?>, <?= $data['PointOnMap']['Y1'] ?>],
					[<?= $data['PointOnMap']['X2'] ?>, <?= $data['PointOnMap']['Y2'] ?>]],
				controls: ["geolocationControl", "fullscreenControl", "zoomControl"]
			};
			var d = {
				balloonContentHeader: e,
				balloonContentBody: m
			};
			var k = new ymaps.Map("map-desktop", i, {suppressMapOpenBlock: !0});
			k.geoObjects.add(new ymaps.Placemark([s, p], d));
		});
	</script>
</body>
</html>
<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
