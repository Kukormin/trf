<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var array $projectId */
/** @var array $setId */
/** @var array $set */

?>
<form id="linkset_detail" class="form-horizontal" data-overlay="1">
	<input type="hidden" name="pid" value="<?= $projectId ?>">
	<input type="hidden" name="sid" value="<?= $setId ?>">
	<div class="control-group">
		<label class="control-label" for="name">
			Название <i class="help" data-placement="top" data-original-title="Название набора быстрых ссылок"
			            data-content="Используется только для внутренней навигации"></i></label>
		<div class="controls">
			<input type="text" id="name" placeholder="Название" name="name" value="<?= $set['NAME'] ?>" />
			<span class="help-inline"></span><?

			if (!$set['NAME'])
			{
				?>
				<p class="examples">
					Напр.: <a href="#">Ссылки по-умолчанию</a>, <a href="#">Ссылки с контактами</a>
				</p><?
			}

			?>
		</div>
	</div><?

	for ($i = 1; $i < 5; $i++)
	{
		$item = $set['DATA']['ITEMS'][$i - 1];
		?>
		<hr/>
		<h4>Быстрая ссылка <?= $i ?></h4>
		<div class="control-group">
			<label class="control-label" for="text<?= $i ?>">Текст</label>

			<div class="controls">
				<input type="text" id="text<?= $i ?>" name="title[]" value="<?= $item['Title'] ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="href<?= $i ?>">Ссылка</label>

			<div class="controls">
				<input type="text" id="href<?= $i ?>" name="href[]" value="<?= $item['Href'] ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="desc<?= $i ?>">Описание</label>

			<div class="controls">
				<input type="text" id="desc<?= $i ?>" name="desc[]" value="<?= $item['Description'] ?>" />
			</div>
		</div><?
	}

	?>
	<p>
		<button class="btn btn-primary" type="button">Сохранить</button>
	</p>
	<div class="alerts"></div>
</form><?


?>
<script type="text/javascript">
	siteOptions.linksetPage = true;
</script><?