<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var array $projectId */
/** @var array $project */
/** @var array $templId */
/** @var array $templ */

$sets = \Local\Linkset::getByProject($projectId);
$cards = \Local\Vcard::getByProject($projectId);

?>
<form id="templ_detail" class="form-horizontal" data-overlay="1">
	<input type="hidden" name="pid" value="<?= $projectId ?>">
	<input type="hidden" name="tid" value="<?= $templId ?>">
	<div class="control-group">
		<label class="control-label" for="name">Название</label>
		<div class="controls">
			<input type="text" id="name" placeholder="Название" name="name" value="<?= $templ['NAME'] ?>" />
			<span class="help-inline"></span><?

			if (!$templ['NAME'])
			{
				/*?>
				<p class="examples">
					Напр.: <a href="#">Основная визитка</a>, <a href="#">Визитка филиала</a>
				</p><?*/
			}

			?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="title">Заголовок</label>
		<div class="controls">
			Начальная часть ключевой фразы (до 33 символов)
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">Текст объявления</label>
		<div class="controls">
			<label class="radio">
				<input type="radio" name="text" value="0"<?= !$templ['DATA']['TEXT'] ? ' checked' : '' ?> />
				Оставшаяся часть ключевой фразы (что не уместилось в заголовок)
			</label>
			<label class="radio">
				<input type="radio" name="text" value="1"<?= $templ['DATA']['TEXT'] ? ' checked' : '' ?> />
				Ключевая фраза целиком
			</label>
			<label class="checkbox">
				<input type="checkbox" name="weight"<?= $templ['DATA']['WEIGHT'] ? ' checked' : '' ?> />
				Присоединять к тексту объявления самую длинную возможную добавку
			</label>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<label class="checkbox">
				<input type="checkbox" name="replace"<?= $templ['DATA']['REPLACE'] ? ' checked' : '' ?> />
				Использовать словарь автозамен при формировании заголовка и текста
			</label>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="url">Ссылка</label>
		<div class="controls">
			<select class="input-small" id="scheme" name="scheme">
				<option value="http"<?= $templ['DATA']['SCHEME'] == 'http' ? ' selected' : '' ?>>http://</option>
				<option value="https"<?= $templ['DATA']['SCHEME'] == 'https' ? ' selected' : '' ?>>https://</option>
			</select>
			<input type="text" id="host" name="host" value="<?= $project['URL'] ?>" disabled />
			<input type="text" id="url" name="url" value="<?= $templ['DATA']['URL'] ?>" />
			<span class="help-inline"></span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="durl">Отображаемая ссылка</label>
		<div class="controls">
			<input type="text" id="durl" name="durl" value="<?= $templ['DATA']['DURL'] ?>" />
			<span class="help-inline"></span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="linkset">Набор быстрых ссылок</label>
		<div class="controls">
			<select id="linkset" name="linkset">
				<option value="0"<?= $templ['DATA']['LINKSET'] == 0 ? ' selected' : '' ?>>Без быстрых ссылок</option><?
				foreach ($sets as $set)
				{
					$selected = $templ['DATA']['LINKSET'] == $set['ID'] ? ' selected' : '';
					?>
					<option value="<?= $set['ID'] ?>"<?= $selected ?>><?= $set['NAME'] ?></option><?
				}
				?>
			</select>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="vcard">Визитка</label>
		<div class="controls">
			<select id="vcard" name="vcard">
				<option value="0"<?= $templ['DATA']['VCARD'] == 0 ? ' selected' : '' ?>>Не прикреплять визитку</option><?
				foreach ($cards as $card)
				{
					$selected = $templ['DATA']['VCARD'] == $card['ID'] ? ' selected' : '';
					?>
					<option value="<?= $card['ID'] ?>"<?= $selected ?>><?= $card['NAME'] ?></option><?
				}
				?>
			</select>
		</div>
	</div><?


	?>
	<p>
		<button class="btn btn-primary" type="button">Сохранить</button>
	</p>
	<div class="alerts"></div>
</form><?

?>
<script type="text/javascript">
	siteOptions.templPage = true;
</script><?
