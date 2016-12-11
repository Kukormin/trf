<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */
$projectId = $component->projectId;
$project = $component->project;
$category = $component->category;
$keygroup = $component->keygroup;
$ad = $component->ad;

?>
<form id="ad_detail" class="form-horizontal" enctype="multipart/form-data">
	<input type="hidden" name="pid" value="<?= $projectId ?>">
	<input type="hidden" name="cid" value="<?= $category['ID'] ?>">
	<input type="hidden" name="kgid" value="<?= $keygroup['ID'] ?>">
	<input type="hidden" name="adid" value="<?= $ad['ID'] ?>">
	<input type="hidden" name="yandex" value="<?= $ad['YANDEX'] ?>">
	<div class="control-group">
		<label class="control-label" for="name">Назначение</label>
		<div class="controls">
			<label class="radio">
				<input type="radio" name="search" value="1"<?= $ad['SEARCH'] ? ' checked' : '' ?> /> поиск
			</label>
			<label class="radio">
				<input type="radio" name="search" value="0"<?= $ad['SEARCH'] ? '' : ' checked' ?> /> сети
			</label>
		</div>
	</div>
	<fieldset>
		<legend>Основное</legend>
		<div class="row-fluid">
			<div class="span6"><?

				$titleName = $ad['YANDEX'] ? 'Заголовок' : 'Заголовок 1';
				$max = $ad['YANDEX'] ? 33 : 30;
				?>
				<div class="control-group">
					<label class="control-label" for="title"><?= $titleName ?></label>
					<div class="controls">
						<input type="text" id="title" name="title"
						       data-max="<?= $max ?>" value="<?= $ad['TITLE'] ?>" required />
						<span class="help-inline"></span>
					</div>
				</div><?

				if (!$ad['YANDEX'])
				{
					?>
					<div class="control-group">
						<label class="control-label" for="title_2">Заголовок 2</label>
						<div class="controls">
							<input type="text" id="title_2" name="title_2"
							       data-max="30" value="<?= $ad['TITLE'] ?>" required />
							<span class="help-inline"></span>
						</div>
					</div><?
				}

				$max = $ad['YANDEX'] ? 75 : 80;
				?>
				<div class="control-group">
					<label class="control-label" for="text">Текст объявления</label>
					<div class="controls">
						<input type="text" id="text" name="text"
						       data-max="<?= $max ?>" value="<?= $ad['TEXT'] ?>" required />
						<span class="help-inline"></span>
					</div>
				</div><?

				$scheme = $category['DATA']['SCHEME'] == 'https' ? 'https' : 'http';
				$path = $ad['ID'] ? $ad['URL'] : $category['DATA']['PATH'];
				$res = $scheme . '://' . $project['URL'] . $path;
				$max = 1024 - strlen($scheme) - strlen($project['URL']) - 3;
				?>
				<div class="control-group">
					<label class="control-label" for="url">Ссылка</label>
					<div class="controls">
						<input type="text" id="host" value="<?= $scheme ?>://<?= $project['URL'] ?>" disabled />
						<input type="text" id="url" name="url"
						       data-max="<?= $max ?>" value="<?= $path ?>" />
						<span class="help-inline"></span>
						<span class="loader"></span>
					</div>
				</div><?

				$titleName = $ad['YANDEX'] ? 'Отображаемая ссылка' : 'Отображаемая ссылка 1';
				$max = $ad['YANDEX'] ? 20 : 15;
				?>
				<div class="control-group">
					<label class="control-label" for="link"><?= $titleName ?></label>
					<div class="controls">
						<input type="text" value="<?= $scheme ?>://<?= $project['URL'] ?>/" disabled />
						<input type="text" id="link" name="link"
						       data-max="<?= $max ?>" value="<?= $ad['LINK'] ?>" />
						<span class="help-inline"></span>
					</div>
				</div><?

				if (!$ad['YANDEX'])
				{
					?>
					<div class="control-group">
						<label class="control-label" for="link_2">Отображаемая ссылка 2</label>
						<div class="controls">
							<input type="text" value="<?= $scheme ?>://<?= $project['URL'] ?>/" disabled />
							<input type="text" id="link_2" name="link_2"
							       data-max="15" value="<?= $ad['LINK_2'] ?>" />
							<span class="help-inline"></span>
						</div>
					</div><?
				}

				?>
			</div>
			<div class="span6">
				<div class="example">
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Расширения</legend>
		<div class="control-group">
			<label class="control-label" for="linkset">Набор быстрых ссылок</label>
			<div class="controls">
				<select id="linkset" name="linkset">
					<option value="0"<?= $ad['LINKSET'] == 0 ? ' selected' : '' ?>>Без быстрых ссылок</option><?
					$sets = \Local\Main\Linkset::getByProject($projectId);
					foreach ($sets as $set)
					{
						$selected = $ad['LINKSET'] == $set['ID'] ? ' selected' : '';
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
					<option value="0"<?= $ad['VCARD'] == 0 ? ' selected' : '' ?>>Не прикреплять визитку</option><?
					$cards = \Local\Main\Vcard::getByProject($projectId);
					foreach ($cards as $card)
					{
						$selected = $ad['VCARD'] == $card['ID'] ? ' selected' : '';
						?>
						<option value="<?= $card['ID'] ?>"<?= $selected ?>><?= $card['NAME'] ?></option><?
					}
					?>
				</select>
			</div>
		</div><?

		if ($ad['YANDEX'])
		{
			$isSearch = $ad['SEARCH'];
			$picId = $ad['PICTURE'];
			include('pic.php');
		}

		?>
	</fieldset><?

	?>
	<p>
		<button class="btn btn-primary save-btn" type="button">Сохранить</button>
		<button class="btn cancel" type="button">Отменить</button>
	</p>
	<div class="alerts"></div>
</form><?

?>
<script type="text/javascript">
	siteOptions.adPage = true;
</script><?
