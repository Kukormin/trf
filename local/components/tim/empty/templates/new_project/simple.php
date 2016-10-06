<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */

$info = $project['DATA']['INFO'];
$product = $project['DATA']['PRODUCT'];
$catalog = $project['DATA']['CATALOG'];
//debugmessage($project);

if (!$info['country'])
	$info['country'] = 'Россия';

$tabs = array(
	10 => 'Ваш сайт',
	20 => 'Тип сайта',
	30 => 'Инфо о сайте',
	40 => 'Инфо о продукте',
	50 => 'Параметры рекламы',
	//60 => 'Конкуренты',
	70 => 'Счетчики',
	80 => 'Категории',
	90 => 'Ключевые слова',
	100 => 'Завершение',
);

?>
<a href="/projects/">Проекты</a>
<h1>Создание нового проекта</h1>
<div class="parse_site"><div title="Проверка сайта"></div></div><?
//
// Заголовки табов
//
?>
<ul class="nav nav-tabs" id="steps"><?

	foreach ($tabs as $i => $name)
	{
		$class = '';
		if ($step == $i)
			$class = ' class="active"';
		elseif ($i > $step)
			$class = ' class="disabled"';
		?>
		<li id="step<?= $i ?>"<?= $class ?>><a href="#st<?= $i ?>"><?= $name ?></a></li><?
	}
	?>
</ul>

<div class="tab-content"><?

	foreach ($tabs as $i => $name)
	{
		$class = $step == $i ? ' active' : '';
		$buttonText = 'Сохранить и продолжить';
		if ($i == 100)
			$buttonText = 'Готово';

		?>
		<div class="tab-pane<?= $class ?>" id="st<?= $i ?>">
			<form class="new_project form-horizontal">
				<fieldset><?

					//
					// Шаг 1. Адрес сайта.
					//
					if ($i == 10)
					{
						?>
						<legend>Введите адрес вашего сайта</legend>
						<div class="control-group">
							<label class="control-label" for="url">url сайта</label>
							<div class="controls">
								<input type="text" name="url" id="url" value="<?= $project['URL'] ?>" />
							</div>
						</div><?
					}
					//
					// Шаг 2. Тип сайта.
					//
					elseif ($i == 20)
					{
						?>
						<legend>Укажите тип вашего сайта</legend>
						<label class="radio">
							<input type="radio" id="estore1" name="estore"
							       value="1"<?= $project['ESTORE'] == 1 ? ' checked' : '' ?> />
							Интернет-магазин. У вас есть каталог товаров и файл выгрузки товаров
						</label>
						<input type="text" name="yml" placeholder="http://www.site.ru/catalog_export/market.yml"
						       value="<?= $project['YML'] ?>" />
						<p>Что такое YML - инструкция</p>

						<label class="radio">
							<input type="radio" id="estore2" name="estore"
							       value="2"<?= $project['ESTORE'] == 2 ? ' checked' : '' ?> />
							Классический сайт. У вас корпоративный сайт, промо-сайт, сайт-визитка или лендинг
						</label><?
					}
					//
					// Шаг 3. Инфо о проекте.
					//
					elseif ($i == 30)
					{
						?>
						<legend>Информация о проекте</legend>
						<div class="control-group">
							<label class="control-label" for="name">Название проекта</label>
							<div class="controls">
								<input type="text" name="name" id="name" value="<?= $project['NAME'] ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="email">Электронная почта</label>
							<div class="controls">
								<input type="text" name="email" id="email" value="<?= $project['EMAIL'] ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="company">Название компании</label>
							<div class="controls">
								<input type="text" name="company" id="company" value="<?= $info['company'] ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="country">Местоположение</label>
							<div class="controls">
								<input type="text" name="country" id="country" placeholder="Страна" value="<?=
								$info['country'] ?>" />
								<input type="text" name="city" id="city" placeholder="Город" value="<?=
								$info['city']
								?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="phone_prefix">Телефон</label>
							<div class="controls">
								<input type="text" name="phone_prefix" id="phone_prefix"
								       value="<?= $info['phone_prefix'] ?>" style="width:20px;" />
								<input type="text" name="phone_code" id="phone_code" placeholder="код"
								       value="<?= $info['phone_code'] ?>" style="width:50px;" />
								<input type="text" name="phone_number" id="phone_number" placeholder="номер"
								       value="<?= $info['phone_number'] ?>" style="width:100px;" />
								<input type="text" name="phone_add" id="phone_add" placeholder="добавочный"
								       value="<?= $info['phone_add'] ?>" style="width:100px;" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="regime">Время работы</label>
							<div class="controls"><?
								$APPLICATION->IncludeComponent('tim:empty', 'regime',
									array('VALUE' => $info['regime']));?>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="street">Адрес</label>
							<div class="controls">
								<input type="text" name="street" id="street" placeholder="улица"
								       value="<?= $info['street'] ?>" style="width:230px;" />
								<input type="text" name="house" id="house" placeholder="дом"
								       value="<?= $info['house'] ?>" style="width:50px;" />
								<input type="text" name="build" id="build" placeholder="корпус"
								       value="<?= $info['build'] ?>" style="width:50px;" />
								<input type="text" name="apart" id="apart" placeholder="офис"
								       value="<?= $info['apart'] ?>" style="width:50px;" />
							</div>
						</div><?
					}
					//
					// Шаг 4. Инфо о продукте.
					//
					elseif ($i == 40)
					{
						?>
						<legend>Укажите какой продукт предлагает ваш сайт</legend>
						<label class="radio">
							<input type="radio" name="product_type" value="1"<?= $project['PRODUCT_TYPE'] == 1 ? '
							checked' : ''
							?> />
							Вы продаете только товары
						</label>
						<label class="radio">
							<input type="radio" name="product_type" value="2"<?= $project['PRODUCT_TYPE'] == 2 ? ' checked' : ''
							?> />
							Вы оказываете только услуги
						</label>
						<label class="radio">
							<input type="radio" name="product_type" value="3"<?= $project['PRODUCT_TYPE'] == 3 ? ' checked' : ''
							?> />
							Вы продаете товары и оказываете услуги
						</label>

						<div class="control-group">
							<label class="control-label" for="prod1">Что? (одно слово)</label>
							<div class="controls">
								<input type="text" name="prod1" id="prod1" value="<?= $product['prod1'] ?>" /><br />
								<span class="quick">детская одежда и товары для новорожденных</span>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod2">Какое?</label>
							<div class="controls">
								<input type="text" name="prod2" id="prod2" value="<?= $product['prod2'] ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod3">Как?</label>
							<div class="controls">
								<select name="prod3" id="prod3">
									<option value="">выберите способ продажи</option>
									<option value="оптом" <?= $product['prod3'] == 'оптом' ? ' selected' : ''
									?>>оптом</option>
									<option value="в розницу"<?= $product['prod3'] == 'в розницу' ? ' selected' : ''
									?>>в розницу</option>
									<option value="оптом и в розницу"<?= $product['prod3'] == 'оптом и в розницу' ? ' selected' : ''
									?>>оптом и в розницу</option>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod4">От кого?</label>
							<div class="controls">
								<select name="prod4" id="prod4">
									<option value="">не указывать</option>
									<option value="от производителя"<?= $product['prod4'] == 'от производителя' ? ' selected' : ''
									?>>от производителя</option>
									<option value="от официального дилера"<?= $product['prod4'] == 'от официального дилера'
										? ' selected' : '' ?>>от официального дилера</option>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod5">Ваши цены</label>
							<div class="controls">
								<select name="prod5" id="prod5">
									<option value="">не указывать</option>
									<option value="по низким ценам"<?= $product['prod5'] == 'по низким ценам' ? ' selected' : ''
									?>>по низким ценам</option>
									<option value="по средним ценам"<?= $product['prod5'] == 'по средним ценам' ? ' selected' : ''
									?>>по средним ценам</option>
									<option value="дорого"<?= $product['prod5'] == 'дорого' ? ' selected' : ''
									?>>дорого</option>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod6">Доставка</label>
							<div class="controls">
								<input type="text" name="prod6" id="prod6" value="<?= $product['prod6'] ?>"
								       placeholder="с доставкой по Москве" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod7">Что? (одно слово)</label>
							<div class="controls">
								<input type="text" name="prod7" id="prod7" value="<?= $product['prod7'] ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod8">Каких?</label>
							<div class="controls">
								<input type="text" name="prod8" id="prod8" value="<?= $product['prod8'] ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod9">Каких?</label>
							<div class="controls">
								<input type="text" name="prod9" id="prod9" value="<?= $product['prod9'] ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod10">Как?</label>
							<div class="controls">
								<select name="prod10" id="prod10">
									<option value="">выберите вариант</option>
									<option value="под ключ" <?= $product['prod10'] == 'под ключ' ? ' selected'
										: '' ?>>под ключ</option>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod11">Ваши цены</label>
							<div class="controls">
								<select name="prod11" id="prod11">
									<option value="">не указывать</option>
									<option value="по низким ценам"<?= $product['prod11'] == 'по низким ценам' ? ' selected' : ''
									?>>по низким ценам</option>
									<option value="по средним ценам"<?= $product['prod11'] == 'по средним ценам' ? ' selected' : ''
									?>>по средним ценам</option>
									<option value="дорого"<?= $product['prod11'] == 'дорого' ? ' selected'
										: '' ?>>дорого</option>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="prod12">Доставка</label>
							<div class="controls">
								<input type="text" name="prod12" id="prod12" value="<?= $product['prod12']
								?>" placeholder="с доставкой по Москве" />
							</div>
						</div><?
					}
					//
					// Шаг 5. Параметры рекламы.
					//
					elseif ($i == 50)
					{
						$timeValue = $project['DATA']['TIME']['ITEMS'];
						if (!$timeValue)
							$timeValue = '0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0';
						?>
						<legend>Выберите источники посетителей, регионы и время показа</legend>
						<label class="checkbox">
							<input type="checkbox" name="yandex-search" value="1"
								<?= $project['YANDEX_SEARCH'] ? ' checked' : '' ?> />
							Яндекс.Директ Поиск
						</label>
						<label class="checkbox">
							<input type="checkbox" name="yandex-net" value="1"
								<?= $project['YANDEX_NET'] ? ' checked' : '' ?> />
							Яндекс.Директ РСЯ
						</label>
						<label class="checkbox">
							<input type="checkbox" name="google-search" value="1"
								<?= $project['GOOGLE_SEARCH'] ? ' checked' : '' ?> />
							Google.Adwords Поиск
						</label>
						<label class="checkbox">
							<input type="checkbox" name="google-net" value="1"
								<?= $project['GOOGLE_NET'] ? ' checked' : '' ?> />
							Google.Adwords КМС
						</label>
						<div class="control-group">
							<label class="control-label">Регионы показа рекламы</label>
							<input type="hidden" name="regions" value="<?= $project['DATA']['REGIONS'] ?>" />
							<div class="controls">
								<a href="#regionsModal" id="regionsPopup" role="button" class="btn">Выбрать</a>
								<div id="regionsModal" class="modal hide fade" tabindex="-1" role="dialog"
								     aria-hidden="true">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
										<h3 id="regionsModalLabel">Выбор регионов</h3>
										<div class="input-append">
											<input id="region_search" type="text" autocomplete="off"
											       placeholder="Начните вводить название региона" />
											<button class="btn" type="button" id="region_search_btn">Найти</button>
										</div>
									</div>
									<div class="modal-body"><?
										$APPLICATION->IncludeComponent('tim:empty', 'regions', array());?>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true">Отменить</button>
										<button class="btn btn-primary" data-dismiss="modal">Сохранить и закрыть</button>
									</div>
								</div>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Время показа</label>
							<input type="hidden" name="time" value="<?= $timeValue ?>" />
							<div class="controls">
								<a href="#timeModal" id="timePopup" role="button" class="btn">Выбрать</a>
								<div id="timeModal" class="modal hide fade" tabindex="-1" role="dialog"
								     aria-hidden="true">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
										<h3>Настройка временного таргетинга</h3>
									</div>
									<div class="modal-body"><?
										$APPLICATION->IncludeComponent('tim:empty', 'time', array());?>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true">Отменить</button>
										<button class="btn btn-primary">Сохранить и закрыть</button>
									</div>
								</div>
							</div>
						</div><?
					}
					//
					// Шаг 7. Счетчики
					//
					elseif ($i == 70)
					{
						$user = new AdWordsUser();
						$user->LogAll();
						$redirectUri = 'http://odiseo.ru/oauth2callback';
						$offline = true;
						$OAuth2Handler = $user->GetOAuth2Handler();
						$authorizationUrl = $OAuth2Handler->GetAuthorizationUrl(
							$user->GetOAuth2Info(), $redirectUri, $offline);

						?>
						<legend>Настройте счетчики на сайте</legend>
						<div class="metric_indicator"></div>
						<div class="metric_alerts"></div>

						<h3>Яндекс.Метрика</h3><?
						if ($project['METRIKA_TOKEN']) {
							?>
							<div class="metric_cont" id="yandex_ok">
								<p>У вас установлена Яндекс.Метрика.</p>
								<p>Доступ есть.</p>
							</div><?
						}
						else
						{
							?>
							<div class="metric_cont hidden" id="yandex_ok">
								<p>У вас установлена Яндекс.Метрика.</p>
								<p>Выполните следующие действия</p>
								<p>
									<a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=<?= DIRECT_API_APP_ID
									?>">Разрешить доступ к данным Метрики для приложения <?= SITE_NAME ?></a></p>
								<p><span class="login"></span></p>
							</div>
							<div class="metric_cont hidden" id="yandex_empty">
								<p>У вас не установлена Яндекс.Метрика.</p>
								<p>Выполните следующие действия</p>
							</div><?
						}

						?>
						<h3>Google.Analytics</h3><?
						if ($project['ANALYTICS_TOKEN']) {
							?>
							<div class="metric_cont" id="google_ok">
								<p>У вас установлена Google.Analytics.</p>
								<p>Доступ есть.</p>
							</div><?
						}
						else
						{
							?>
							<div class="metric_cont hidden" id="google_ok">
								<p>У вас установлен Google.Analytics.</p>
								<p>Выполните следующие действия</p>
								<p>
									<a href="<?= $authorizationUrl ?>">Разрешить доступ к данным для приложения <?=
									SITE_NAME ?></a></p>
								<p><span class="login"></span></p>
							</div>
							<div class="metric_cont hidden" id="google_empty">
								<p>У вас не установлен Google.Analytics.</p>
								<p>Выполните следующие действия</p>
							</div><?
						}
					}
					//
					// Категории
					//
					elseif ($i == 80)
					{
						?>
						<legend>Категории и товары для рекламы</legend>
						<p>Здесь нужно придумать какое-нибудь сообщение, которое поясняет,
							что эти категории нужны только для удобства и не несут никакой функциональной
							составляющей</p>
						<div class="yml_indicator"></div>
						<div class="yml_alerts"></div>
						<input type="hidden" name="catalog" value="<?= $project['DATA']['CAMPAIGNS'] ?>" />
						<div class="catalog<?= $catalog ? '' : ' need_ajax' ?>"><?
							$APPLICATION->IncludeComponent('tim:empty', 'catalog',
								array(
									'CATALOG' => $catalog,
									'LINKS' => $project['DATA']['LINKS'],
								    'URL' => $project['URL'],
								));
						?></div>
						<div id="add_links"></div>
						<p>
							<a href="#addCampModal" id="addCampPopup" role="button" class="btn">Добавить страницу</a>
							<div id="addCampModal" class="modal hide fade" tabindex="-1" role="dialog"
							     aria-hidden="true">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
									<h3>Добавление страницы для рекламы</h3>
								</div>
								<div class="modal-body">
									<div class="control-group">
										<label class="control-label" for="add_url">url адрес страницы</label>
										<div class="controls">
											<input type="text" name="add_url" id="add_url" value="" />
										</div>
									</div>
									<div class="control-group">
										<label class="control-label" for="add_name">Название</label>
										<div class="controls">
											<input type="text" name="add_name" id="add_name" value="" />
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button class="btn" data-dismiss="modal" aria-hidden="true">Отменить</button>
									<button class="btn btn-primary">Добавить</button>
								</div>
							</div>
						</p><?
					}
					//
					// Ключевые слова
					//
					elseif ($i == 90)
					{
						?>
						<legend>Выберите ключевые слова для для каждой страницы и категории</legend>
						<div class="yml_indicator"></div>
						<div class="yml_alerts"></div>
						<input type="hidden" name="catalog" value="<?= $project['DATA']['CAMPAIGNS'] ?>" />
						<div class="catalog_result"><?
							$APPLICATION->IncludeComponent('tim:empty', 'catalog_result',
								array('CATALOG' => $catalog));
						?></div><?
					}

					?>
					<br/>
					<button type="submit" class="btn btn-primary"><?= $buttonText ?></button>
					<span class="loader"></span>
					<input type="hidden" name="step" value="<?= $i ?>"/>
				</fieldset>
			</form>
		</div><?
	}

?>
</div>
<div class="alerts"></div><?