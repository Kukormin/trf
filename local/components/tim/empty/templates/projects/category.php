<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var int $projectId */
/** @var array $project */
/** @var int $categoryId */
/** @var array $category */
/** @var string $tabCode */
/** @var array $titleParts */

if ($categoryId == 'new') {
	$tabs = array(
		'settings' => 'Настройки',
	);
	$tabCode = 'settings';
	$categoryHref = \Local\Category::getNewHref($projectId);
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
	$titleParts[] = $tabs[$tabCode];
	$categoryHref = \Local\Category::getHref($category);
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
		if ($code != 'main' && $categoryId != 'new')
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
		$filters = \Local\Keygroup::getFilters();
		?>
		<div class="row">
		<div class="span3">
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
		<div class="span9">
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

			if ($category['DATA']['NEW'])
			{
				?>
				<div class="alert alert-success">
					<button type="button" class="close" data-dismiss="alert">×</button>
					Категория успешно создана
				</div><?
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
		$weight = $category['DATA']['WEIGHT'];
		?>
		<form id="weight-form">
			<fieldset>
				<legend>Дополнения к текстам объявлений</legend>
				<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
				<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
				<div class="rows"><?

					foreach ($weight as $item)
					{
						?>
						<div class="control-group">
							<input type="text" name="w[]" value="<?= $item ?>"/>
						</div><?
					}

					?>
					<div class="control-group">
						<input type="text" name="w[]" value=""/>
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

	?>
	</div><?
}

?>
</div><?

?>
<script type="text/javascript">
	siteOptions.categoryPage = true;
	siteOptions.keygroupFilters = <?= $categoryId == 'new' ? 'false' : 'true' ?>;
</script><?