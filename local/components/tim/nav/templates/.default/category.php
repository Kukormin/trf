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
		'main' => 'Сводная таблица',
		'settings' => 'Настройки',
		'base' => 'Базовые слова',
		'add' => 'Дополнительные фразы',
		'templates' => 'Шаблоны',
		//'replace' => 'Словарь автозамен',
		//'weight' => 'Добавки',
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
		<li<?= $class?>><a data-id="#<?= $code ?>" href="<?= $href ?>"><?= $name ?></a></li><?
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
				<ul class="nav" id="multi-nav">
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
								<a id="toggle_page" href="javascript:void(0)">Инвертировать</a>
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
							<li class="divider"></li>
							<li class="dropdown-submenu add_templ">
								<a href="javascript:void(0)">Создать объявление по шаблону</a>
								<? \Local\Main\Templ::printDropdown($project['ID']) ?>
							</li>
						</ul>
					</li>
				</ul><?

				$user = \Local\Main\User::getCurrentUser();
				$filtersActive = $user['DATA']['FILTERS_SHOW'] ? ' class="active"' : '';
				$viewActive = $user['DATA']['VIEW_SHOW'] ? ' class="active"' : '';
				?>
				<ul class="nav pull-right">
					<li<?= $filtersActive ?>>
						<a id="filters_toogle" href="javascript:void(0)">Фильтр</a>
					</li>
					<li<?= $viewActive ?>>
						<a id="view_toogle" href="javascript:void(0)">Вид</a>
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
		?>
		<div id="keygroup-table">

		</div><?
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

		$max = $category['DATA']['BASE_MAX'];
		if (!$max)
			$max = 4;

		$colCount = count($category['DATA']['BASE']);
		if ($colCount < 7)
			$colCount = 7;
		$rowCount = 10;
		foreach ($category['DATA']['BASE'] as $item)
		{
			$cnt = count($item['WORDS']) + 1;
			if ($cnt > $rowCount)
				$rowCount = $cnt;
		}

		?>
		<div id="copy_area">
			<textarea id="copy_textarea"></textarea>
		</div>
		<form id="base_words">
			<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
			<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
			<legend>Семантическое ядро категории</legend>
			<p>
				<input type="text" id="max" name="max" value="<?= $max ?>" />
				Максимальное количество колонок, при формировании ключевой фразы
			</p>
			<p>
				<button class="btn btn-primary" type="button">Генерировать ключевые фразы</button>
				<span class="loader-big"></span>
				Всего ключевых фраз: <strong id="total_cnt"></strong>
			</p>
			<div id="base_words_wrap">
				<table id="base_words_table">
					<thead>
						<tr class="">
							<th class="f"></th><?
							for ($j = 1; $j <= $colCount; $j++)
							{
								$char = chr(64 + $j);
								$right = '';
								if ($j == $colCount)
									$right = '<i></i>';
								?>
								<th><?= $right ?><?= $char ?></th><?
							}
							?>
						</tr>
						<tr class="">
							<th class="f"></th><?
							for ($j = 1; $j <= $colCount; $j++)
							{
								$item = $category['DATA']['BASE'][$j - 1];
								$checked = $item['REQ'] ? ' checked' : '';
								?>
								<th><label><input type="checkbox"<?= $checked ?> /> Обязательно</label></th><?
							}
							?>
						</tr>
					</thead>
					<tbody><?

						for ($i = 1; $i <= $rowCount; $i++)
						{
							?>
							<tr>
								<td class="f"><?= $i ?></td><?
								for ($j = 1; $j <= $colCount; $j++)
								{
									$item = $category['DATA']['BASE'][$j - 1];
									$word = $item['WORDS'][$i - 1];
									$class = $item['REQ'] ? ' class="req"' : '';
									?>
									<td<?= $class ?>><?= $word ?></td><?
								}
								?>
							</tr><?
						}
						?>
					</tbody>
				</table>
				<div class="edit">
					<input type="text" />
				</div>
			</div>
			<div class="alerts"></div>
		</form>
		<?
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
	/*
	//
	// --------------------------------------
	//
	elseif ($code == 'replace')
	{
		$replace = $category['DATA']['REPLACE'];
		?>
		<form id="replace-form">
			<fieldset>
				<legend>Словарь автозамен</legend>
				<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
				<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
				<div class="rows"><?

					foreach ($replace as $from => $to)
					{
						?>
						<div class="control-group">
							<input type="text" name="from[]" value="<?= $from ?>"/>
							<input type="text" name="to[]" value="<?= $to ?>"/>
						</div><?
					}

					?>
					<div class="control-group">
						<input type="text" name="from[]" value=""/>
						<input type="text" name="to[]" value=""/>
					</div>
				</div>
				<p>
					<button class="btn add-row" type="button">Добавить строку</button>
				</p>
			</fieldset>
			<p>
				<button class="btn btn-primary" type="button">Сохранить</button>
			</p>
			<div class="alerts"></div>
		</form><?
	}
	//
	// --------------------------------------
	//
	elseif ($code == 'weight')
	{
		$titlePlus = $category['DATA']['TITLE_PLUS'];
		$textPlus = $category['DATA']['TEXT_PLUS'];
		?>
		<form id="plus-form">
			<div class="row-fluid">
				<div class="span6 title-plus">
					<fieldset>
						<legend>Дополнения к заголовкам объявлений</legend>
						<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
						<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
						<div class="rows"><?

							foreach ($titlePlus as $item)
							{
								?>
								<div class="control-group">
									<input type="text" name="w1[]" value="<?= $item ?>"/>
								</div><?
							}

							?>
							<div class="control-group">
								<input type="text" name="w1[]" value=""/>
							</div>
						</div>
						<p>
							<button class="btn add-row" type="button">Добавить строку</button>
						</p>
					</fieldset>
				</div>
				<div class="span6 text-plus">
					<fieldset>
						<legend>Дополнения к текстам объявлений</legend>
						<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
						<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
						<div class="rows"><?

							foreach ($textPlus as $item)
							{
								?>
								<div class="control-group">
								<input type="text" name="w2[]" value="<?= $item ?>"/>
								</div><?
							}

							?>
							<div class="control-group">
								<input type="text" name="w2[]" value=""/>
							</div>
						</div>
						<p>
							<button class="btn add-row" type="button">Добавить строку</button>
						</p>
					</fieldset>
				</div>
			</div>
			<p>
				<button class="btn btn-primary" type="button">Сохранить</button>
			</p>
			<div class="alerts"></div>
		</form><?
	}*/

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