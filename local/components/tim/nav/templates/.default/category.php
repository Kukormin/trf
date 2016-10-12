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
		'replace' => 'Словарь автозамен',
		'weight' => 'Добавки',
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
		$filters = \Local\Main\Keygroup::getFilters();
		?>
		<div class="row-fluid">
		<div class="span2">
			<form id="keygroup-form">
				<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
				<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
				<input type="hidden" name="page" value="1" /><?

				foreach ($filters as $i => $item)
				{
					?><br /><b><?= $item['NAME'] ?></b><br /><?
					if ($item['TYPE'] == 'text')
					{
						?><input type="text" name="<?= $i ?>" value="<?= $item['VALUE'] ?>" /><br /><?
					}
					elseif ($item['TYPE'] == 'checkbox')
					{
						foreach ($item['VARS'] as $key => $var)
						{
							$checked = $key == $item['VALUE'] ? ' checked' : '';
							?><input type="checkbox" name="<?= $i ?>[]" value="<?= $key ?>"<?= $checked ?> /> <?= $var
							?><br /><?
						}
					}
					elseif ($item['TYPE'] == 'radio')
					{
						foreach ($item['VARS'] as $key => $var)
						{
							$checked = $key == $item['VALUE'] ? ' checked' : '';
							?><input type="radio" name="<?= $i ?>" value="<?= $key ?>"<?= $checked ?> /> <?= $var
							?><br /><?
						}
					}
					elseif ($item['TYPE'] == 'select')
					{
						?>
						<select name="<?= $i ?>"><?
						foreach ($item['VARS'] as $key => $var)
						{
							$checked = $key == $item['VALUE'] ? ' selected' : '';
							?><option value="<?= $key ?>"<?= $checked ?>><?= $var ?></option><?
						}
						?>
						</select><?
					}
				}
				?>
				<p>
					<button id="apply" class="btn btn-primary" type="button">Применить</button>
				</p>
			</form>
		</div>
		<div class="span10">
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
									<a href="javascript:void(0)">Прикрепить шаблон</a>
									<? \Local\Main\Templ::printDropdown($project['ID']) ?>
								</li>
								<li class="dropdown-submenu remove_templ">
									<a href="javascript:void(0)">Удалить шаблон</a>
									<? \Local\Main\Templ::printDropdown($project['ID']) ?>
								</li>
								<li class="remove_all_templ">
									<a href="javascript:void(0)">Удалить все шаблоны</a>
								</li>
								<li class="remove_all_manual_ad">
									<a href="javascript:void(0)">Удалить все объявления, добавленные вручную</a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
			<div id="keygroup-table">

			</div>
		</div>
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
						<label class="control-label" for="print-path">Отображаемая ссылка</label>
						<div class="controls">
							<input type="text" id="print-path" name="print-path"
							       value="<?= $category['DATA']['PRINT_PATH'] ?>" />
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

		?>
		<form id="base_words">
			<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
			<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
			<legend>Семантическое ядро категории</legend>
			<div class="rows"><?

				$i = 0;
				foreach ($category['DATA']['BASE'] as $item)
				{
					$checked = $item['REQ'] ? ' checked' : '';
					if ($i == 0)
					{
						?>
						<div class="row-fluid"><?
					}

					?>
					<div class="span2">
						<label class="checkbox">
							<input type="checkbox" name="r[]"<?= $checked ?> /> Обязательно
						</label>
						<textarea name="w[]"><?= implode("\n", $item['WORDS']) ?></textarea>
					</div><?

					if ($i == 5)
					{
						?>
						</div><?
						$i = 0;
					}
					else
						$i++;
				}
				if ($i || !$category['DATA']['BASE'])
				{
					if (!$category['DATA']['BASE'])
					{
						?>
						<div class="row-fluid"><?
					}
					while ($i < 6)
					{
						?>
						<div class="span2">
							<label class="checkbox">
								<input type="checkbox" name="r[]" /> Обязательно
							</label>
							<textarea name="w[]"></textarea>
						</div><?
						$i++;
					}
					?>
					</div><?
				}

			?>
			</div>
			<p>
				<button id="add_row" class="btn" type="button">Добавить строку</button>
			</p>
			<p>
				<input type="text" id="max" name="max" value="<?= $max ?>" />
				Максимальное количество колонок, при формировании ключевой фразы
			</p>
			<p>
				<button class="btn btn-primary" type="button">Сохранить</button>
				Всего ключевых фраз: <strong id="total_cnt"></strong>
			</p>
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
				<legend>Дополнительные фразы</legend>
				<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
				<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
				<p>
					<textarea name="add_words"><?= implode("\n", $category['DATA']['ADD_WORDS']) ?></textarea>
				</p>
				<p>
					<input type="checkbox" id="de" name="de" />
					Деактивировать другие дополнительные фразы
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