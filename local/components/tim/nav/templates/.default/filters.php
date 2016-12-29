<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */
/** @var array $user */
/** @var array $filters */

$project = $component->project;
$category = $component->category;

?>
<form id="keygroup-form" class="form-horizontal">

<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
<input type="hidden" name="page" value="1" /><?

foreach ($filters['YGSN'] as $k => $item)
{
	?>
	<input type="hidden" name="<?= $k ?>" value="<?= $item['VALUE'] ?>" /><?
}

foreach ($filters['PLATFORM'] as $k => $item)
{
	?>
	<input type="hidden" name="<?= $k ?>" value="<?= $item['VALUE'] ?>" /><?
}

// Панель фильтров
$style = ' style="display:none;"';
if ($user['DATA']['FILTERS_SHOW'])
	$style = '';
?>
<div class="tb-block" id="filters-block"<?= $style ?>><?

	foreach ($filters['FILTERS'] as $i => $item)
	{
		$style = $item['ACTIVE'] ? '' : ' style="display:none;"';
		?>
		<div id="fcg_<?= $i ?>" class="control-group"<?= $style ?>>
			<label class="control-label" for="input_<?= $i ?>"><?= $item['NAME'] ?></label>
			<div class="controls"><?

				if ($item['TYPE'] == 'text')
				{
					?>
					<input type="text" name="<?= $i ?>" id="input_<?= $i ?>" value="<?= $item['VALUE'] ?>" /><?
				}
				elseif ($item['TYPE'] == 'checkbox')
				{
					foreach ($item['VARS'] as $key => $var)
					{
						$checked = $item['VALUE'][$key] ? ' checked' : '';
						?>
						<label class="checkbox">
							<input type="checkbox" name="<?= $i ?>[<?= $key ?>]" value="1"<?= $checked ?> /> <?= $var ?>
						</label><?
					}
				}
				elseif ($item['TYPE'] == 'radio')
				{
					foreach ($item['VARS'] as $key => $var)
					{
						$checked = $key == $item['VALUE'] ? ' checked' : '';
						?>
						<label class="radio">
							<input type="radio" name="<?= $i ?>" value="<?= $key ?>"<?= $checked ?> /> <?= $var ?>
						</label><?
					}
				}
				elseif ($item['TYPE'] == 'select')
				{
					?><select name="<?= $i ?>" id="input_<?= $i ?>"><?
						foreach ($item['VARS'] as $key => $var)
						{
							$checked = $key == $item['VALUE'] ? ' selected' : '';
							?><option value="<?= $key ?>"<?= $checked ?>><?= $var ?></option><?
						}
						?>
					</select><?
				}

				?>
			</div>
		</div><?
	}

	?>
	<div class="control-group">
		<button id="apply" class="btn btn-primary" type="button">Применить</button>
		<div class="btn-group">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="icon-plus"></i> Добавить фильтр
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu" id="add_filter_menu"><?

				foreach ($filters['FILTERS'] as $i => $item)
				{
					$active = $item['ACTIVE'] ? ' class="active"' : '';
					?>
					<li<?= $active ?> data-id="<?= $i ?>">
						<a href="javascript:void(0)"><i class="icon-ok"></i><?= $item['NAME'] ?></a>
					</li><?
				}

				?>
			</ul>
		</div>
	</div>
</div><?

?>
</form><?