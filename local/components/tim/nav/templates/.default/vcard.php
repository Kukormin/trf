<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$projectId = $component->projectId;
$card = $component->card;

?>
<form id="vcard_detail" class="form-horizontal" data-overlay="1">
	<input type="hidden" name="pid" value="<?= $projectId ?>">
	<input type="hidden" name="cid" value="<?= $card['ID'] ?>">
	<div class="control-group">
		<label class="control-label" for="name">
			Название <i class="help" data-placement="top" data-original-title="Название визитки"
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
		<label class="control-label" for="city">Город</label>
		<div class="controls">
			<input type="text" id="city" name="city" value="<?= $card['DATA']['CITY'] ?>" />
			<span class="help-inline"></span>
		</div>
	</div><?


	?>
	<p>
		<button class="btn btn-primary" type="button">Сохранить</button>
		<button class="btn cancel" type="button">Отменить</button>
	</p>
	<div class="alerts"></div>
</form><?

?>
<script type="text/javascript">
	siteOptions.vcardPage = true;
</script><?
