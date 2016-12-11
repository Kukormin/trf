<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$view = $component->view;
$data = $view['DATA'];
if (!$data['COLUMNS'])
	$data['COLUMNS'] = array('cb', 'name', 'action');

?>
<form id="view_detail" class="form-horizontal">
	<input type="hidden" name="vid" value="<?= $view['ID'] ?>">
	<div class="control-group">
		<label class="control-label" for="name">
			Название <i class="help" data-placement="top" data-original-title="Название вида"
			            data-content="Используется только для внутренней навигации"></i></label>
		<div class="controls">
			<input type="text" id="name" placeholder="Название" name="name" value="<?= $view['NAME'] ?>" />
			<span class="help-inline"></span>
		</div>
	</div>

	<fieldset>
		<legend>Набор столбцов</legend>
		<div class="view_constuctor">
			<div>
				<h5>Выбранные столбцы</h5>
				<div class="view_columns left"><?

					foreach ($data['COLUMNS'] as $code)
					{
						$col = \Local\Main\View::getColumnByCode($code);
						$title = $col['TITLE'] ? $col['TITLE'] : $col['NAME'];
						$b = $col['REQUIRED'] ? '' : '<b></b>';
						?>
						<div class="column_cont" data-code="<?= $code ?>">
							<div class="view_column">
								<i></i><?= $title ?><?= $b ?>
							</div>
						</div><?
					}

					?>
				</div>
			</div>
			<div>
				<h5>Доступные столбцы</h5>
				<div class="view_columns right"><?

					foreach (\Local\Main\View::$COLUMNS as $code => $col)
					{
						if (in_array($code, $data['COLUMNS']))
							continue;

						$title = $col['TITLE'] ? $col['TITLE'] : $col['NAME'];
						?>
						<div class="column_cont" data-code="<?= $code ?>">
							<div class="view_column">
								<i></i><?= $title ?><b></b>
							</div>
						</div><?
					}

					?>
				</div>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>Дополнительные настройки</legend>
		<div class="control-group">
			<label class="control-label">Сколько объявлений отображать для ключевой фразы</label>
			<div class="controls">
				<label class="radio">
					<input type="radio" name="ad_count" value="0"<?= !$data['AD_COUNT'] ? ' checked' : '' ?> />
					Все
				</label>
				<label class="radio">
					<input type="radio" name="ad_count" value="1"<?= $data['AD_COUNT'] == 1 ? ' checked' : '' ?> />
					Только первое
				</label>
				<label class="radio">
					<input type="radio" name="ad_count" value="0"<?= $data['AD_COUNT'] == 2 ? ' checked' : '' ?> />
					Первые два объявления
				</label>
			</div>
		</div>
	</fieldset>

	<p>
		<button class="btn btn-primary" type="button">Сохранить</button>
		<button class="btn cancel" type="button">Отменить</button>
	</p>
	<div class="alerts"></div>
</form><?


?>
<script type="text/javascript">
	siteOptions.viewPage = true;
</script><?