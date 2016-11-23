<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$projectId = $component->projectId;
$project = $component->project;
$category = $component->category;
$templ = $component->templ;

?>
<form id="templ_detail" class="form-horizontal">
	<input type="hidden" name="pid" value="<?= $projectId ?>">
	<input type="hidden" name="cid" value="<?= $category['ID'] ?>">
	<input type="hidden" name="tid" value="<?= $templ['ID'] ?>">
	<input type="hidden" name="yandex" value="<?= $templ['YANDEX'] ?>">
	<div class="control-group">
		<label class="control-label" for="name">Название</label>
		<div class="controls">
			<input type="text" id="name" placeholder="Название" name="name" value="<?= $templ['NAME'] ?>" />
			<span class="help-inline"></span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="name">Назначение</label>
		<div class="controls">
			<label class="radio">
				<input type="radio" name="search" value="1"<?= $templ['SEARCH'] ? ' checked' : '' ?> /> поиск
			</label>
			<label class="radio">
				<input type="radio" name="search" value="0"<?= $templ['SEARCH'] ? '' : ' checked' ?> /> сети
			</label>
		</div>
	</div>
	<fieldset><?

		$titleName = $templ['YANDEX'] ? 'Заголовок' : 'Заголовок 1';
		?>
		<legend><?= $titleName ?></legend>
		<div class="row-fluid">
			<div class="span6"><?

				if ($templ['YANDEX'])
				{
					?>
					<div class="control-group">
						<label class="control-label" for="title">Длина заголовка (только для поиска)</label>

						<div class="controls">
							<label class="radio">
								<input type="radio" name="title_56" value="33"<?=
								!$templ['DATA']['TITLE_56'] ? ' checked' : '' ?> />
								Не более 33 символов
							</label>
							<label class="radio">
								<input type="radio" name="title_56" value="56"<?=
								$templ['DATA']['TITLE_56'] ? ' checked' : '' ?> />
								Расширить до 56 символов
							</label>
						</div>
					</div><?
				}

				?>
				<div class="control-group">
					<label class="control-label">Опции</label>
					<div class="controls">
						<label class="checkbox">
							<input type="checkbox" name="title_first_big"
								<?= $templ['DATA']['TITLE_FIRST_BIG'] ? ' checked' : '' ?> />
							Первая буква заглавная
						</label>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Составные части</label><?

					$constructData = $templ['DATA']['CONSTRUCT']['TITLE'];
					if (!$constructData)
						$constructData = array(
							array(
								'KEY' => 'keyword',
							),
						);
					$cKey = 'TITLE';
					$base = $category['DATA']['BASE'];
					include('templ_construct.php');

					?>
				</div>
			</div>
			<div class="span6">
				<div class="example">
				</div>
			</div>
		</div>
	</fieldset><?

	if (!$templ['YANDEX'])
	{
		?>
		<fieldset>
			<legend>Заголовок 2</legend>
			<div class="control-group">
				<label class="control-label">Опции</label>

				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" name="title_2_first_big"
							<?= $templ['DATA']['TITLE_2_FIRST_BIG'] ? ' checked' : '' ?> />
						Первая буква заглавная
					</label>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Составные части</label><?

				$constructData = $templ['DATA']['CONSTRUCT']['TITLE_2'];
				if (!$constructData)
					$constructData = array(
						array(
							'KEY' => 'keyword',
						),
					);
				$cKey = 'TITLE_2';
				$base = $category['DATA']['BASE'];
				include('templ_construct.php');

				?>
			</div>
		</fieldset><?
	}

	?>
	<fieldset>
		<legend>Текст объявления</legend>
		<div class="control-group">
			<label class="control-label">Опции</label>
			<div class="controls">
				<label class="checkbox">
					<input type="checkbox" name="text_first_big"
						<?= $templ['DATA']['TEXT_FIRST_BIG'] ? ' checked' : '' ?> />
					Первая буква заглавная
				</label>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Составные части</label><?

			$constructData = $templ['DATA']['CONSTRUCT']['TEXT'];
			if (!$constructData)
				$constructData = array(
					array(
						'KEY' => 'text',
					    'D' => '',
					),
				);
			$cKey = 'TEXT';
			include('templ_construct.php');

			?>
		</div>
	</fieldset><?

	$titleName = $templ['YANDEX'] ? 'Отображаемая ссылка' : 'Отображаемая ссылка 1';
	?>
	<fieldset>
		<legend><?= $titleName ?></legend>
		<div class="control-group">
			<label class="control-label">Составные части</label><?

			$constructData = $templ['DATA']['CONSTRUCT']['LINK'];
			if (!$constructData)
				$constructData = array(
					array(
						'KEY' => 'text',
						'D' => '',
					),
				);
			$cKey = 'LINK';
			include('templ_construct.php');

			?>
		</div>
	</fieldset><?

	if (!$templ['YANDEX'])
	{
		?>
		<fieldset>
			<legend>Отображаемая ссылка 2</legend>
			<div class="control-group">
				<label class="control-label">Составные части</label><?

				$constructData = $templ['DATA']['CONSTRUCT']['LINK_2'];
				if (!$constructData)
					$constructData = array(
						array(
							'KEY' => 'keyword',
						),
					);
				$cKey = 'LINK_2';
				$base = $category['DATA']['BASE'];
				include('templ_construct.php');

				?>
			</div>
		</fieldset><?
	}

	?>
	<fieldset>
		<legend>Расширения</legend>
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
		</div>
	</fieldset><?

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
