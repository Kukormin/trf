<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var int $projectId */
/** @var array $project */
/** @var string $tabCode */
/** @var string $titleParts */

if ($projectId == 'new')
{
	$tabs = array(
		'settings' => 'Настройки',
	);
	$tabCode = 'settings';
	$projectHref = \Local\Project::getNewHref();
}
else
{
	$tabs = array(
		'cat' => 'Категории',
		'phrase' => 'Ключевые фразы',
		'links' => 'Быстрые ссылки',
		'vcards' => 'Визитки',
		'templates' => 'Шаблоны объявлений',
		'settings' => 'Настройки',
	);
	if (!$tabs[$tabCode])
		$tabCode = 'cat';
	$titleParts[] = $tabs[$tabCode];
	$projectHref = \Local\Project::getHref($projectId);
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
		$href = $projectHref;
		if ($code != 'cat' && $projectId != 'new')
			$href .= $code . '/';
		?>
		<li<?= $class?>><a data-id="#<?= $code ?>" href="<?= $href ?>"><?= $name ?></a></li><?
	}
	?>
</ul>

<div id="project_detail" class="tab-content"><?

$campaigns = \Local\Category::getByProject($projectId);

foreach ($tabs as $code => $name)
{
	$class = '';
	if ($code == $tabCode)
		$class = ' active';
	?>
	<div class="tab-pane<?= $class ?>" id="<?= $code ?>"><?

	//
	// ---------------------------------------------------
	//
	if ($code == 'cat')
	{

		?>
		<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th></th>
			<th>С</th>
			<th>Категории</th>
			<th>Тип</th>
			<th>Качество</th>
			<th>Статистика</th>
		</tr>
		</thead>
		<tbody><?

		foreach ($campaigns as $item)
		{
			$targetName = '';
			if ($item['TARGET'] == 'target')
				$targetName = 'целевая';
			elseif ($item['TARGET'] == 'near')
				$targetName = 'околоцелевая';
			$href = \Local\Category::getHref($item);

			?>
			<tr>
			<td></td>
			<td><?/*= $item['IS_YANDEX'] ? 'Я' : 'G'*/ ?></td>
			<td><a href="<?= $href ?>"><?= $item['NAME'] ?></a></td>
			<td><?/*= $item['IS_SEARCH'] ? 'поиск' : 'сеть'*/ ?></td>
			<td><?/*= $targetName */?></td>
			<td></td>
			</tr><?
		}

		?>
		</tbody>
		</table><?

		$href = \Local\Category::getNewHref($projectId);
		?>
		<p>
			<a href="<?= $href ?>" id="add_category" class="btn btn-primary">Добавить категорию</a>
		</p><?
	}
	//
	// ---------------------------------------------------
	//
	elseif ($code == 'phrase')
	{
		?>
		<div class="row">
			<div class="span4">
				<div id="ad-filter-panel">
					Кампании
					<ul><?
						foreach ($campaigns as $item)
						{
							?>
							<li><input type="checkbox" /><?= $item['NAME'] ?></li><?
						}
						?>
					</ul>
				</div>
			</div>
			<div class="span8">
				<div id="ad-table">
					<table class="table table-striped table-hover">
						<thead>
						<tr>
							<th>№</th>
							<th></th>
							<th>Ключевая фраза</th>
							<th>Частота</th>
							<th></th>
							<th>Заголовок</th>
							<th></th>
							<th>Описание</th>
							<th></th>
							<th>URL</th>
							<th>Быстрые ссылки</th>
						</tr>
						</thead>
						<tbody><?

						/*$groups = \Local\Keygroup::getList();
						foreach ($groups as $item)
						{
							?>
							<tr>
							<td><?= $item['ID'] ?></td>
							<td></td>
							<td><?= $item['NAME'] ?></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							</tr><?
						}*/

						?>
						</tbody>
					</table>
				</div>
			</div>
		</div><?
	}
	//
	// ---------------------------------------------------
	//
	elseif ($code == 'links')
	{
		?>
		<table id="sitelinks" class="table table-striped table-hover">
			<thead>
			<tr>
				<th>Название</th>
				<th>Текст 1</th>
				<th>Ссылка 1</th>
				<th>Текст 2</th>
				<th>Ссылка 2</th>
				<th>Текст 3</th>
				<th>Ссылка 3</th>
				<th>Текст 4</th>
				<th>Ссылка 4</th>
			</tr>
			</thead>
			<tbody><?

			$sets = \Local\Linkset::getByProject($projectId);
			foreach ($sets as $set)
			{
				$href = \Local\Linkset::getHref($set);
				?>
				<tr>
					<td><a href="<?= $href ?>"><?= $set['NAME'] ?></a></td><?
					foreach ($set['DATA']['ITEMS'] as $item)
					{
						?>
						<td><?= $item['Title'] ?></td>
						<td><?= $item['Href'] ?></td><?
					}
					?>
				</tr><?
			}

			?>
			</tbody>
		</table><?

		$href = \Local\Linkset::getAddHref($projectId);
		?>
		<p>
			<a href="<?= $href ?>" class="btn btn-primary" type="button">Добавить набор быстрых
				ссылок</a>
		</p>
	<?
	}
	//
	// ---------------------------------------------------
	//
	elseif ($code == 'vcards')
	{
		?>
		<table id="vcards" class="table table-striped table-hover">
			<thead>
			<tr>
				<th>Название</th>
				<th>Данные</th>
			</tr>
			</thead>
			<tbody><?

			$cards = \Local\Vcard::getByProject($projectId);
			foreach ($cards as $card)
			{
				$href = \Local\Vcard::getHref($card);
				?>
				<tr>
					<td><a href="<?= $href ?>"><?= $card['NAME'] ?></a></td>
					<td></td>
				</tr><?
			}

			?>
			</tbody>
		</table><?

		$href = \Local\Vcard::getAddHref($projectId);
		?>
		<p>
			<a href="<?= $href ?>" class="btn btn-primary" type="button">Добавить визитку</a>
		</p>
	<?
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

		$cards = \Local\Templ::getByProject($projectId);
		foreach ($cards as $card)
		{
			$href = \Local\Templ::getHref($card);
			?>
			<tr>
			<td><a href="<?= $href ?>"><?= $card['NAME'] ?></a></td>
			<td></td>
			</tr><?
		}

		?>
		</tbody>
		</table><?

		$href = \Local\Templ::getAddHref($projectId);
		?>
		<p>
			<a href="<?= $href ?>" class="btn btn-primary" type="button">Добавить шаблон</a>
		</p>
	<?
	}
	//
	// ---------------------------------------------------
	//
	elseif ($code == 'settings')
	{
		$disabled = $projectId == 'new' ? '' : ' disabled';
		$class = $projectId == 'new' ? ' new' : '';
		?>
		<form id="settings_form" class="form-horizontal<?= $class?>">
			<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
			<fieldset>
				<legend>Информация о проекте</legend>
				<div class="control-group">
					<label class="control-label" for="url"><span class="loader"></span> URL сайта</label>
					<div class="controls">
						<input type="text" name="url" id="url" value="<?= $project['URL'] ?>"<?= $disabled ?> />
						<span class="help-inline"></span>
					</div>
				</div>
				<div class="control-group hide_new">
					<label class="control-label" for="name">Название проекта</label>
					<div class="controls">
						<input type="text" name="name" id="name" value="<?= $project['NAME'] ?>" />
						<span class="help-inline"></span>
						<p class="examples"></p>
					</div>
				</div>
			</fieldset><?

			if ($project['DATA']['NEW'])
			{
				?>
				<div class="alert alert-success">
					<button type="button" class="close" data-dismiss="alert">×</button>
					Проект успешно создан
				</div><?
			}

			?>
			<p class="hide_new">
				<button id="save_settings" class="btn btn-primary" type="button">Сохранить</button>
			</p>
		</form>
		<?
	}

	?>
	</div><?
}

?>
</div><?

?>
<script type="text/javascript">
	siteOptions.projectPage = true;
</script><?
