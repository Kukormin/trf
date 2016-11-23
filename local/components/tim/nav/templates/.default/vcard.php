<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */
/** @global CMain $APPLICATION */

$projectId = $component->projectId;
$project = $component->project;
$card = $component->card;

$mapInfo = $card ? 'Вы можете скорректировать положение метки' : 'Вы можете установить положение метки вручную';

?>
<form id="vcard_detail" class="form-horizontal">
	<input type="hidden" name="pid" value="<?= $projectId ?>">
	<input type="hidden" name="cid" value="<?= $card['ID'] ?>">
	<div class="control-group">
		<label class="control-label" for="name">
			Название визитки <i class="help" data-placement="top" data-original-title="Название визитки"
			            data-content="Используется только для внутренней навигации"></i></label>
		<div class="controls">
			<input type="text" id="name" placeholder="Название" name="name" value="<?= $card['NAME'] ?>" />
			<span class="help-inline"></span><?

			if (!$card['NAME'])
			{
				?>
				<p class="examples">
					Напр.: <a href="#">Основная визитка</a>, <a href="#">Визитка филиала</a>
				</p><?
			}

			?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="CompanyName">Название компании/ФИО</label>
		<div class="controls">
			<input type="text" class="required" id="CompanyName" name="data[CompanyName]"
			       value="<?= $card['DATA']['CompanyName'] ?>" />
			<span class="help-inline"></span>
		</div>
	</div>
	<fieldset>
		<legend>Контакты</legend>
		<div class="control-group">
			<label class="control-label" for="ContactPerson">Контактное лицо</label>
			<div class="controls">
				<input type="text" id="ContactPerson" name="data[ContactPerson]"
				       value="<?= $card['DATA']['ContactPerson'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="ContactEmail">E-mail</label>
			<div class="controls">
				<input type="text" id="ContactEmail" name="data[ContactEmail]"
				       value="<?= $card['DATA']['ContactEmail'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="ContactEmail">Режим работы</label>
			<div class="controls regime-controls"><?
				$APPLICATION->IncludeComponent('tim:empty', 'regime', array(
					'NAME' => 'data[WorkTime]',
					'VALUE' => $card['DATA']['WorkTime'],
				));?>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Телефон</legend>
		<div class="control-group">
			<label class="control-label" for="CountryCode">Код страны</label>
			<div class="controls">
				<input type="text" class="phone required" id="CountryCode" name="data[Phone][CountryCode]"
				       value="<?= $card['DATA']['Phone']['CountryCode'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="CityCode">Код города</label>
			<div class="controls">
				<input type="text" class="phone required" id="CityCode" name="data[Phone][CityCode]"
				       value="<?= $card['DATA']['Phone']['CityCode'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="PhoneNumber">Телефон</label>
			<div class="controls">
				<input type="text" class="phone required" id="PhoneNumber" name="data[Phone][PhoneNumber]"
				       value="<?= $card['DATA']['Phone']['PhoneNumber'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="Extension">Добавочный</label>
			<div class="controls">
				<input type="text" class="phone" id="Extension" name="data[Phone][Extension]"
				       value="<?= $card['DATA']['Phone']['Extension'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Подробности</legend>
		<div class="control-group">
			<label class="control-label" for="ExtraMessage">Подробнее о товаре/услуге</label>
			<div class="controls">
				<input type="text" id="ExtraMessage" name="data[ExtraMessage]"
				       value="<?= $card['DATA']['ExtraMessage'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="Ogrn">ОГРН/ОГРНИП</label>
			<div class="controls">
				<input type="text" id="Ogrn" name="data[Ogrn]" value="<?= $card['DATA']['Ogrn'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Местоположение</legend>
		<div class="control-group">
			<label class="control-label" for="Country">Страна</label>
			<div class="controls">
				<input type="text" class="required address" id="Country" name="data[Country]"
				       value="<?= $card['DATA']['Country'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="City">Город</label>
			<div class="controls">
				<input type="text" class="required address" id="City" name="data[City]"
				       value="<?= $card['DATA']['City'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="Street">Улица</label>
			<div class="controls">
				<input type="text" class="address" id="Street" name="data[Street]"
				       value="<?= $card['DATA']['Street'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="House">Дом</label>
			<div class="controls">
				<input type="text" class="address" id="House" name="data[House]"
				       value="<?= $card['DATA']['House'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="Building">Корпус</label>
			<div class="controls">
				<input type="text" class="address" id="Building" name="data[Building]"
				       value="<?= $card['DATA']['Building'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="Apartment">Офис</label>
			<div class="controls">
				<input type="text" id="Apartment" name="Apartment" value="<?= $card['DATA']['Apartment'] ?>" />
				<span class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Карта</label>
			<div class="controls">
				<div class="map_info process">
					<span class="loader"></span>
					<span class="help-inline"><?= $mapInfo ?></span>
				</div>
				<input type="hidden" name="data[PointOnMap][X]" value="<?= $card['DATA']['PointOnMap']['X'] ?>" />
				<input type="hidden" name="data[PointOnMap][Y]" value="<?= $card['DATA']['PointOnMap']['Y'] ?>" />
				<input type="hidden" name="data[PointOnMap][X1]" value="<?= $card['DATA']['PointOnMap']['X1'] ?>" />
				<input type="hidden" name="data[PointOnMap][Y1]" value="<?= $card['DATA']['PointOnMap']['Y1'] ?>" />
				<input type="hidden" name="data[PointOnMap][X2]" value="<?= $card['DATA']['PointOnMap']['X2'] ?>" />
				<input type="hidden" name="data[PointOnMap][Y2]" value="<?= $card['DATA']['PointOnMap']['Y2'] ?>" />
				<div class="map" id="vcard_map">
				</div>
			</div>
		</div>
	</fieldset><?

	?>
	<fieldset>
		<legend>Предварительный просмотр</legend>
		<div class="example"><?

			$phone = \Local\Main\Vcard::getPhone($card);
			$regime = \Local\Main\Vcard::getRegime($card);

			$href = '#';
			if ($card['ID'])
				$href = \Local\Main\Vcard::getYandexHref($card['ID'], $card['PROJECT']);

			?>
			<div class="yandex-serp">
				<h2>
					<a class="link" target="_blank" href="http://<?= $project['URL'] ?>">
						<span class="favicon"></span>
						Заголовок <b>объявления</b> в Яндексе! / <?= $project['URL'] ?>
					</a>
				</h2>
				<div class="subtitle">
					<div class="path">
						<a class="link" target="_blank" href="http://<?= $project['URL'] ?>"><?= $project['URL'] ?>/<b>объявление</b></a>
					</div>
					<div class="lbl">Реклама</div>
				</div>
				<div class="content">
					<div class="text">Текст <b>объявления</b>. 5 лет гарантии! Звони!</div>
					<div class="meta">
						<div>
							<a id="vcard_preview" class="link" target="_blank" href="<?= $href ?>"
							   data-id="<?= intval($card['ID']) ?>">Контактная информация</a>
						</div><div id="yandex-phone"><?= $phone ?></div><div id="yandex-regime"><?= $regime ?></div><div
							id="yandex-city"><?= $card['DATA']['City'] ?></div>
					</div>
				</div>
			</div><?

			$href = '#';
			// TODO: доделать
			$regime = ' - Часы работы сегодня · ' . \Local\Main\Vcard::getMondayRegime($card);;
			$address = \Local\Main\Vcard::getAddress($card);
			if ($address)
				$address = '<span class="pos"></span>' . $address;


			?>
			<div class="google-serp">
				<h2>
					<a class="link" target="_blank" href="http://<?= $project['URL'] ?>">
						Заголовок <b>объявления</b> в Google!
					</a>
				</h2>
				<div class="subtitle">
					<div class="lbl">Реклама</div>
					<div class="path">
						<?= $project['URL'] ?>/<b>объявление</b>
					</div>
					<div id="google-phone" class="ph"><?= $phone ?></div>
				</div>
				<div class="content">
					<div class="text">Текст <b>объявления</b>. 5 лет гарантии! Звони!</div>
					<div class="meta">
						<a id="google_preview" class="link" target="_blank" href="<?= $href ?>"
						   data-id="<?= intval($card['ID']) ?>"><?= $address ?></a>
						<span class="google-regime" id="google-regime"><?= $regime ?></span>
					</div>
				</div>
			</div>
		</div>
	</fieldset>

	<p>
		<button class="btn btn-primary" type="button">Сохранить</button>
		<button class="btn cancel" type="button">Отменить</button>
	</p>
	<div class="alerts"></div>
</form><?

$GLOBALS['COMPONENTS']['YMAP'] = true;
?>
<script type="text/javascript">
	siteOptions.vcardPage = true;<?
	if ($card['DATA']['PointOnMap'])
	{
		?>mapOptions = <?= json_encode($card['DATA']['PointOnMap']) ?>;<?
	}
	?>
</script><?
