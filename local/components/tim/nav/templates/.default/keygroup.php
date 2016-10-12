<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$projectId = $component->projectId;
$category = $component->category;
$keygroup = $component->keygroup;

$typeText = 'Получена из базовых слов';
$words = '';
if ($keygroup['BASE'] == -1)
	$typeText = 'Добавлена вручную';
elseif ($keygroup['BASE'] == -2)
	$typeText = 'Деактивирована';
else
{
	$parts = explode(',', $keygroup['BASE']);
	foreach ($parts as $i => $part)
	{
		$word = $category['DATA']['BASE'][$i]['WORDS'][$part - 1];
		if ($word)
			$words .= '<br />- ' . $word;
	}
}

$dt = ConvertTimeStamp($keygroup['TS'], "FULL", "ru");
$ws = $keygroup['WORDSTAT'] == -1 ? 'не проверена' : $keygroup['WORDSTAT'];

?>
<div class="row-fluid">
	<div class="span4">
		<form id="keygroup_detail" class="form-horizontal" data-overlay="1">
			<input type="hidden" name="pid" value="<?= $projectId ?>">
			<input type="hidden" name="cid" value="<?= $keygroup['CATEGORY'] ?>">
			<input type="hidden" name="kgid" value="<?= $keygroup['ID'] ?>">
			<div class="control-group">
				<label class="control-label">Тип ключевой фразы</label>
				<div class="controls"><?= $typeText ?><?= $words ?></div>
			</div>
			<div class="control-group">
				<label class="control-label">Дата создания</label>
				<div class="controls"><?= $dt ?></div>
			</div>
			<div class="control-group">
				<label class="control-label">Частотность wordstat</label>
				<div class="controls"><?= $ws ?></div>
			</div>
			<div class="control-group">
				<label class="control-label">Метки</label>
				<div class="controls"><?
					$marks = \Local\Main\Mark::getByCurrentUser();
					foreach ($marks as $mark)
					{
						$checked = in_array($mark['ID'], $keygroup['MARKS']) ? ' checked' : '';
						?>
						<label class="checkbox">
							<input type="checkbox"<?= $checked ?> name="mark[<?= $mark['ID'] ?>]" />
							<?= $mark['NAME'] ?> <?= $mark['HTML'] ?>
						</label><?
					}
					?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Шаблоны объявлений</label>
				<div class="controls"><?
					$templates = \Local\Main\Templ::getByProject($projectId);
					foreach ($templates as $templ)
					{
						$checked = in_array($templ['ID'], $keygroup['TEMPLATES']) ? ' checked' : '';
						?>
						<label class="checkbox">
							<input type="checkbox"<?= $checked ?> name="templ[<?= $templ['ID'] ?>]" />
							<?= $templ['NAME'] ?>
						</label><?
					}
					?>
				</div>
			</div>
			<?


			?>
			<p>
				<button class="btn btn-primary" type="button">Сохранить</button>
				<button class="btn cancel" type="button">Отменить</button>
			</p>
			<div class="alerts"></div>
		</form>
	</div>
	<div class="span8"><?
		foreach ($templates as $templ)
		{
			if (in_array($templ['ID'], $keygroup['TEMPLATES']))
			{
				/*?>
				<div class="example"><?= $?>
				<div class="yandex-serp">
					<h2>
						<a class="link" target="_blank" href="http://<?= $project['URL'] ?>">
							<span class="favicon"></span>
							Заголовок <b>объявления</b>! / <?= $project['URL'] ?>
						</a>
					</h2>
					<div class="subtitle">
						<div class="path">
							<a class="link" target="_blank" href="http://<?= $project['URL'] ?>"><?= $project['URL'] ?>/<b>объявление</b>/</a>
						</div>
						<div class="lbl">Реклама</div>
					</div>
					<div class="content">
						<div class="text">Текст <b>объявления</b>. 5 лет гарантии! Звони!</div>
						<div class="sitelinks"><?

							for ($i = 1; $i < 5; $i++)
							{
								$item = $set['DATA']['ITEMS'][$i - 1];
								$hidden = $item['Title'] ? '' : ' class="hidden"';
								?>
							<div id="yandex<?= $i ?>"<?= $hidden ?>>
								<a target="_blank" href="http://<?= $project['URL'] ?><?= $item['Href'] ?>"><?= $item['Title'] ?></a>
								</div><?
							}

							?>
						</div>
						<div class="meta">
							<div>
								<a class="link" target="_blank" href="#">Контактная информация</a>
							</div><div>+7 (9999) 99-99-99</div><div>пн-пт 8:00-22:00, сб-вс 8:15-22:00</div>
						</div>
					</div>
				</div>
				</div><?*/
			}
		}
		?>
	</div>
</div>

<?


?>
<script type="text/javascript">
	siteOptions.keygroupPage = true;
</script><?