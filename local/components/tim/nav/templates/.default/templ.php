<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$projectId = $component->projectId;
$project = $component->project;
$templ = $component->templ;

?>
<form id="templ_detail" class="form-horizontal" data-overlay="1">
	<input type="hidden" name="pid" value="<?= $projectId ?>">
	<input type="hidden" name="tid" value="<?= $templ['ID'] ?>">
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
			<label class="checkbox">
				<input type="checkbox" checked disabled />
				1) Ключевая фраза
			</label>
			<label class="checkbox">
				<input type="checkbox" name="replace"<?= $templ['DATA']['REPLACE'] ? ' checked' : '' ?> />
				2) Использовать словарь автозамен при обработке ключевой фразы
			</label>
			<label class="checkbox">
				<input type="checkbox" name="title_plus"<?= $templ['DATA']['TITLE_PLUS'] ? ' checked' : '' ?> />
				3) Присоединять к заголовку самую длинную из добавок (настраиваются в категории)
			</label>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">Текст объявления</label>
		<div class="controls">
			<label class="checkbox">
				<input type="checkbox" name="text_title"<?= $templ['DATA']['TEXT_TITLE'] ? ' checked' : '' ?> />
				1) Выводить остаток ключевой фразы (если не уместилась в заголовке целиком)
			</label>
			<label class="checkbox">
				<input type="checkbox" name="text_title_plus"<?= $templ['DATA']['TEXT_TITLE_PLUS'] ? ' checked' : ''
				?> />
				2) Выводить остаток длинной добавки заголовка
			</label>
			<label class="checkbox">
				<input type="checkbox" name="text_plus"<?= $templ['DATA']['TEXT_PLUS'] ? ' checked' : '' ?> />
				3) Присоединять к тексту объявления самую длинную из добавок (настраиваются в категории)
			</label>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="linkset">Набор быстрых ссылок</label>
		<div class="controls">
			<select id="linkset" name="linkset">
				<option value="0"<?= $templ['DATA']['LINKSET'] == 0 ? ' selected' : '' ?>>Без быстрых ссылок</option><?
				$sets = \Local\Main\Linkset::getByProject($projectId);
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
				$cards = \Local\Main\Vcard::getByProject($projectId);
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
		<button class="btn cancel" type="button">Отменить</button>
	</p>
	<div class="alerts"></div>
</form><?

?>
<script type="text/javascript">
	siteOptions.templPage = true;
</script><?
