<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$projectId = $component->projectId;
$project = $component->project;
$tabCode = $component->tabCode;
$category = $component->category;
$categoryId = intval($category['ID']);

if (!$categoryId) {
	$tabs = array(
		'settings' => 'Настройки',
	);
	$tabCode = 'settings';
	$categoryHref = \Local\Main\Category::getNewHref($projectId);
}
else
{
	$tabs = array(
		'main' => 'Фразы и объявления',
		'stat' => 'Статистика',
		'seach' => 'Поисковые запросы',
		'base' => 'Комбинатор',
		'karma' => 'Карма',
		//'add' => 'Дополнительные фразы',
		//'templates' => 'Шаблоны',
		'settings' => 'Настройки',
	);
	if (!$tabs[$tabCode])
		$tabCode = 'main';
	$categoryHref = \Local\Main\Category::getHref($category);
	$component->addNav('', $tabs[$tabCode], false, true);
}

//
// Заголовки табов
//
?>
<ul class="nav nav-tabs history-tabs" id="project_tabs"><?

	foreach ($tabs as $code => $name)
	{
		$class = '';
		if ($code == $tabCode)
			$class = ' class="active"';
		$href = $categoryHref;
		if ($code != 'main' && $categoryId)
			$href .= $code . '/';
		?>
		<li<?= $class?>><a id="tab-<?= $code ?>" data-id="#<?= $code ?>" href="<?= $href ?>"><?= $name ?></a></li><?
	}
	?>
</ul>

<div id="category_detail" class="tab-content"><?

foreach ($tabs as $code => $name)
{
	$class = '';
	if ($code == $tabCode)
		$class = ' active';
	?>
	<div class="tab-pane<?= $class ?>" id="<?= $code ?>"><?

	//
	// --------------------------------------
	//
	if ($code == 'main')
	{
		// Панель массовых операций
		?>
		<div class="navbar">
			<div class="navbar-inner">
				<ul class="nav" id="multi-nav" style="display:none;">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="icon-plus"></i> Добавить <b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li class="dropdown-submenu">
								<a href="javascript:void(0)">Ключевые фразы</a>
								<ul class="dropdown-menu">
									<li>
										<a id="to-add-tab" href="javascript:void(0)">Списком</a>
									</li>
									<li>
										<a id="to-base-tab" href="javascript:void(0)">Из базовых слов</a>
									</li>
								</ul>
							</li>
							<li class="disabled">
								<a id="add_ad" href="javascript:void(0)">Объявления</a>
							</li>
						</ul>
					</li>
					<li>
						<i class="help" data-placement="bottom" data-original-title="Массовые операции"
						   data-content="Подсказа по массовым оперциям и инверсии напр."></i>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							Выбрано: <span id="selected_count">0</span> из <span id="all_count">0</span>
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a id="this_page" href="javascript:void(0)">Выбрать все на этой странице</a>
							</li>
							<li>
								<a id="all_page" href="javascript:void(0)">Выбрать все на ВСЕХ страницах</a>
							</li>
							<li>
								<a id="toggle_select" href="javascript:void(0)">Инвертировать</a>
							</li>
							<li class="disabled">
								<a id="cancel_select" href="javascript:void(0)">Отменить выбор</a>
							</li>
						</ul>
					</li>
					<li class="dropdown hidden" id="multi-action">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							Действие с выбранными:
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a id="update_ws" href="javascript:void(0)">Обновить частотность</a>
							</li><?

							if (\Local\Main\Mark::isNotEmpty())
							{
								?>
								<li class="divider"></li>
								<li class="dropdown-submenu add_mark">
									<a href="javascript:void(0)">Добавить метку</a>
									<? \Local\Main\Mark::printDropdown() ?>
								</li>
								<li class="dropdown-submenu remove_mark">
									<a href="javascript:void(0)">Удалить метку</a>
									<? \Local\Main\Mark::printDropdown() ?>
								</li>
								<li class="remove_all_mark">
									<a href="javascript:void(0)">Удалить все метки</a>
								</li><?
							}

							?>
							<li class="divider"></li><?

							$templ = \Local\Main\Templ::getByCategory($category['ID']);
							if ($templ)
							{
								?>
								<li class="dropdown-submenu add_templ">
									<a href="javascript:void(0)">Создать объявление по шаблону</a>
									<? \Local\Main\Templ::printDropdown($category['ID']) ?>
								</li><?
							}
							else
							{
								?>
								<li class="disabled">
									<a href="javascript:void(0)">(Нет шаблонов)</a>
								</li><?
							}
							?>
						</ul>
					</li>
				</ul><?

				$user = \Local\Main\User::getCurrentUser();
				$filters = \Local\Main\Keygroup::getFilters();

				$filtersActive = $user['DATA']['FILTERS_SHOW'] ? ' class="active"' : '';
				?>
				<ul class="nav pull-right">
					<li id="filter_platform" class="btns"><?

						foreach ($filters['PLATFORM'] as $k => $item)
						{
							$active = $item['VALUE'] ? ' class="active"' : '';
							?><div data-id="<?= $k ?>"<?= $active ?> title="<?= $item['NAME'] ?>">
								<i class="ad-icon <?= $k ?>"></i><?= $item['NAME']
							?></div><?
						}

						?>
					</li><?

/*
					?>
					<li id="filter_ygsn" class="btns ygsn"><?

						foreach ($filters['YGSN'] as $k => $item)
						{
							$active = $item['VALUE'] ? ' active' : '';
							?><div class="<?= $k ?><?= $active ?>" data-id="<?= $k ?>"
							       title="<?= $item['NAME'] ?>"><i></i></div><?
						}

						?>
					</li><?*/

					?>
					<li<?= $filtersActive ?>>
						<a id="filters_toogle" href="javascript:void(0)">Фильтр</a>
					</li><?

					$views = \Local\Main\View::getByCurrentUser();
					$active = $user['DATA']['EDIT'] ? ' class="active"' : '';
					?>
					<li class="btns">
						<div<?= $active ?> id="edit_regime">Режим правки</div>
					</li><?

					$style = $user['DATA']['EDIT'] ? ' style="display:none;"' : '';
					?>
					<li class="btn-group views" id="views-um"<?= $style ?>><?

						$name = '';
						$fName = '';
						$selectedView = 0;
						foreach ($views as $view)
						{
							if ($view['EDIT_MODE'])
								continue;

							if (!$selectedView)
							{
								$fName = $view['NAME'];
								$selectedView = $view['ID'];
							}

							if ($view['ID'] == $user['DATA']['VIEW'])
							{
								$name = $view['NAME'];
								$selectedView = $view['ID'];
								break;
							}
						}
						if (!$name)
							$name = $fName;

						?>
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							<i><?= $name ?></i>
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu pull-right"><?

							$showDivider = false;
							foreach ($views as $view)
							{
								if ($view['EDIT_MODE'])
									continue;

								$active = $view['ID'] == $selectedView ? ' active' : '';
								?>
								<li>
									<a class="view_change<?= $active ?>" href="javascript:void(0)"
									   data-id="<?= $view['ID'] ?>"><?= $view['NAME'] ?></a>
								</li><?
								$showDivider = true;
							}

							if ($showDivider)
							{
								?>
								<li class="divider"></li><?
							}

							?>
							<li>
								<a id="view_setings" href="<?= \Local\Main\View::getViewsHref() ?>">Настройки</a>
							</li>
						</ul>
					</li><?

					$style = $user['DATA']['EDIT'] ? '' : ' style="display:none;"';
					?>
					<li class="btn-group views" id="views-em"<?= $style ?>><?

						$name = '';
						$fName = '';
						$selectedView = 0;
						foreach ($views as $view)
						{
							if (!$view['EDIT_MODE'])
								continue;

							if (!$selectedView)
							{
								$fName = $view['NAME'];
								$selectedView = $view['ID'];
							}

							if ($view['ID'] == $user['DATA']['VIEW_EM'])
							{
								$name = $view['NAME'];
								$selectedView = $view['ID'];
								break;
							}
						}
						if (!$name)
							$name = $fName;

						?>
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							<i><?= $name ?></i>
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu pull-right"><?

							$showDivider = false;
							foreach ($views as $view)
							{
								if (!$view['EDIT_MODE'])
									continue;

								$active = $view['ID'] == $selectedView ? ' active' : '';
								?>
								<li>
								<a class="view_change<?= $active ?>" href="javascript:void(0)"
								   data-id="<?= $view['ID'] ?>"><?= $view['NAME'] ?></a>
								</li><?
								$showDivider = true;
							}

							if ($showDivider)
							{
								?>
								<li class="divider"></li><?
							}

							?>
							<li>
								<a id="view_setings" href="<?= \Local\Main\View::getViewsHref() ?>">Настройки</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</div><?

		// ======================================================================
		// Панель фильтров
		//
		include ('filters.php');
		//
		// ======================================================================

		// Контейнер фраз (подгрузится аяксом)
		$class = $user['DATA']['EDIT'] ? ' class="edit-mode"' : '';
		?>
		<div class="copy_area">
			<textarea class="copy_textarea" id="kg_copy_textarea"></textarea>
		</div>
		<div id="keygroup-table-wrap">
			<div id="keygroup-table"<?= $class ?>>

			</div>
			<div class="edit">
				<input type="text" />
			</div>
			<ul class="dropdown-menu" id="platform-menu" role="menu"><?

				foreach ($filters['PLATFORM'] as $k => $item)
				{
					$disabled = $item['VALUE'] ? '' : ' class="disabled"';
					?>
					<li<?= $disabled ?>>
						<a href="javascript:void(0)" data-code="<?= $k ?>">
							<i class="ad-icon <?= $k ?>"></i>
							<?= $item['NAME'] ?>
						</a>
					</li><?
				}

			?>
			</ul>
		</div><?

		// Всплывающее окно подтверждения удаления объявления
		?>
		<div id="delete_modal" class="modal hide fade" role="dialog" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel">Удаление объявления</h3>
			</div>
			<div class="modal-body">Удалить объявление?</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Отмена</button>
				<button class="btn btn-primary">Удалить</button>
			</div>
		</div><?
	}
	//
	// --------------------------------------
	//
	elseif ($code == 'stat')
	{
		?>
		<?
	}
	//
	// --------------------------------------
	//
	elseif ($code == 'settings')
	{
		if ($category['DATA']['NEW'])
		{
			?>
			<div class="alert alert-success">
				<button type="button" class="close" data-dismiss="alert">×</button>
				Категория успешно создана
			</div><?
		}

		?>
		<form id="settings_form" class="form-horizontal">
			<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
			<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
			<fieldset>
				<legend>Информация о категории</legend>
				<div class="control-group">
					<label class="control-label" for="name">Название категории</label>
					<div class="controls">
						<input type="text" name="name" id="name" value="<?= $category['NAME'] ?>" />
						<span class="help-inline"></span>
					</div>
				</div>
			</fieldset><?

			if ($categoryId)
			{
				$scheme = $category['DATA']['SCHEME'] == 'https' ? 'https' : 'http';
				$res = $scheme . '://' . $project['URL'] . $category['DATA']['PATH'];
				?>
				<fieldset>
					<legend>Ссылка на рекламируемую страницу</legend>
					<div class="control-group">
						<label class="control-label" for="scheme">Протокол</label>
						<div class="controls">
							<select class="input-small" id="scheme" name="scheme">
								<option value="http"<?= $scheme == 'http' ? ' selected' : '' ?>>http://</option>
								<option value="https"<?= $scheme == 'https' ? ' selected' : '' ?>>https://</option>
							</select>
							<span class="help-inline"></span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="host">Хост</label>
						<div class="controls">
							<input type="text" id="host" name="host" value="<?= $project['URL'] ?>" disabled />
							<span class="help-inline"></span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="path">Путь</label>
						<div class="controls">
							<input type="text" id="path" name="path" value="<?= $category['DATA']['PATH'] ?>" />
							<span class="help-inline"></span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="res">Результат</label>
						<div class="controls">
							<input type="text" class="input-xxlarge" id="res" value="<?= $res ?>" disabled />
							<span class="loader"></span>
							<span class="help-inline"></span>
						</div>
					</div>
				</fieldset><?
			}

			?>
			<p>
				<button id="save_settings" class="btn btn-primary" type="button">Сохранить</button>
			</p>
		</form>
		<?
	}
	//
	// --------------------------------------
	//
	elseif ($code == 'base')
	{

		// ======================================================================
		// Панель фильтров
		//
		include ('combo.php');
		//
		// ======================================================================
	}
	//
	// --------------------------------------
	//
	elseif ($code == 'add')
	{
		?>
		<form id="additional_words">
			<fieldset>
				<legend>Дополнительные ключевые фразы</legend>
				<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
				<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
				<p>
					<textarea name="add_words"><?= implode("\n", $category['DATA']['ADD_WORDS']) ?></textarea>
				</p>
				<p>
					<input type="checkbox" id="de" name="de" />
					Удалить другие дополнительные фразы
				</p>
			</fieldset>
			<p>
				<button class="btn btn-primary" type="button">Сохранить</button>
			</p>
			<div class="alerts"></div>
		</form><?
	}
	//
	// ---------------------------------------------------
	//
	elseif ($code == 'templates')
	{
		?>
		<table id="templates" class="table table-striped table-hover">
		<thead>
		<tr>
			<th>Название</th>
			<th>Данные</th>
		</tr>
		</thead>
		<tbody><?

		$templs = \Local\Main\Templ::getByCategory($category['ID']);
		foreach ($templs as $templ)
		{
			$href = \Local\Main\Templ::getHref($templ, $category);
			?>
			<tr>
			<td><a href="<?= $href ?>"><?= $templ['NAME'] ?></a></td>
			<td></td>
			</tr><?
		}

		?>
		</tbody>
		</table><?

		$yhref = \Local\Main\Templ::getAddYandexHref($category);
		$ghref = \Local\Main\Templ::getAddGoogleHref($category);
		?>
		<p>
			<a href="<?= $yhref ?>" class="btn btn-primary" type="button">Добавить шаблон для <?= DIRECT_NAME ?></a>
			<a href="<?= $ghref ?>" class="btn btn-primary" type="button">Добавить шаблон для <?= ADWORDS_NAME ?></a>
		</p>
	<?
	}

	?>
	</div><?
}

?>
</div><?

?>
<script type="text/javascript">
	siteOptions.categoryPage = true;
	siteOptions.keygroupFilters = <?= $categoryId ? 'true' : 'false' ?>;
</script><?