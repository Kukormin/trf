<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */

if ($step == 20)
	$step = 15;

?>
<a href="/projects/">Проекты</a>
<h1>Создание нового проекта</h1>
<div class="parse_site"><div title="Проверка сайта"></div></div><?

?>
<form class="new_project form-horizontal">
	<fieldset>
		<div class="control-group">
			<label class="control-label" for="url">url сайта</label>
			<div class="controls">
				<input type="text" name="url" id="url" value="<?= $project['URL'] ?>" />
			</div>
		</div><?

		$class = $project ? '' : ' hidden';
		?>
		<div class="control-group<?= $class ?>">
			<label class="control-label" for="name">Название проекта</label>
			<div class="controls">
				<input type="text" name="name" id="name" value="<?= $project['NAME'] ?>" />
			</div>
		</div><?

		?>
		<button type="button" class="btn btn-primary new-project-extended">Создать</button>
		<input type="hidden" name="step" value="<?= $step ?>"/>
		<input type="hidden" name="phone_prefix" value=""/>
		<input type="hidden" name="phone_code" value=""/>
		<input type="hidden" name="phone_number" value=""/>
		<input type="hidden" name="phone_add" value=""/>
		<input type="hidden" name="estore1" value=""/>
		<input type="hidden" name="estore2" value=""/>
		<input type="hidden" name="email" value=""/>
		<input type="hidden" name="company" value=""/>
		<span class="loader"></span>
	</fieldset>
</form>
<div class="alerts"></div><?