<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$tabCode = $component->tabCode;
$tabs = array(
	'projects' => 'Проекты',
	'mark' => 'Метки',
	'view' => 'Виды',
);
if (!$tabs[$tabCode])
	$tabCode = 'projects';

$component->addNav('', $tabs[$tabCode], false, true);

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
		$href = '/';
		if ($code != 'projects')
			$href .= $code . '/';
		?>
		<li<?= $class?>><a data-id="#<?= $code ?>" href="<?= $href ?>"><?= $name ?></a></li><?
	}
	?>
</ul>

<div id="index_tabs" class="tab-content"><?

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
	if ($code == 'projects')
	{
		$projects = \Local\Main\Project::getByCurrentUser();

		?>

		<table class="table table-striped table-hover">
			<thead>
			<tr>
				<th></th>
				<th>Проект</th>
				<th>Статистика</th>
				<th></th>
				<th></th>
			</tr>
			</thead>
			<tbody><?

			foreach ($projects as $project)
			{
				$href = \Local\Main\Project::getHref($project['ID']);
				?>
				<tr>
					<td></td>
					<td><a href="<?= $href ?>"><?= $project['NAME'] ?></a></td>
					<td></td>
					<td></td>
					<td></td>
				</tr><?
			}

			?>
			</tbody>
		</table><?

		?>
		<p>
			<a class="btn btn-primary" href="<?= \Local\Main\Project::getNewHref() ?>">Новый проект</a>
		</p><?
	}
	//
	// ---------------------------------------------------
	//
	if ($code == 'mark')
	{
		$marks = \Local\Main\Mark::getByCurrentUser();

		?>
		<form id="mark-form">
			<fieldset>
				<legend>Метки для ключевых фраз</legend>
				<div class="rows"><?

					foreach ($marks as $item)
					{
						$bg = $item['COLOR'] ? '#' . $item['COLOR'] : 'none';
						?>
						<div class="control-group">
							<input type="text" placeholder="Название" name="mark[<?= $item ['ID'] ?>]"
							       value="<?= $item['NAME'] ?>" />
							#<input type="text" placeholder="Код цвета" name="color[<?= $item ['ID'] ?>]"
							        class="color" value="<?= $item['COLOR'] ?>" />
							<?= $item['HTML'] ?>
						</div><?
					}

					?>
					<div class="control-group">
						<input type="text" placeholder="Название" name="mark[]" />
						#<input type="text" placeholder="Код цвета" name="color[]" class="color" />
						<i class="mark"></i>
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
	// ---------------------------------------------------
	//
	if ($code == 'view')
	{
		$views = \Local\Main\View::getByCurrentUser();

		?>
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th>Название</th>
					<th></th>
				</tr>
			</thead>
			<tbody><?

				foreach ($views as $item)
				{
					$href = \Local\Main\View::getHref($item['ID']);
					?>
					<tr>
						<td><a href="<?= $href ?>"><?= $item['NAME'] ?></a></td>
						<td><?
							if (!$item['CODE'])
							{
								?><a data-id="<?= $item['ID'] ?>" class="view_delete"
								     href="javascript:void(0);">Удалить</a><?
							}
							?>
						</td>
					</tr><?
				}

				?>
			</tbody>
		</table><?

		?>
		<p>
			<a class="btn btn-primary" href="<?= \Local\Main\View::getNewHref() ?>">Новый вид</a>
		</p><?
	}

	?>
	</div><?
}

?>
</div><?

?>
<script type="text/javascript">
	siteOptions.indexPage = true;
</script><?
