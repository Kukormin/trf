<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$projectId = $component->projectId;
$project = $component->project;
$category = $component->category;
$keygroup = $component->keygroup;

$typeText = '';
if ($keygroup['TYPE'] == 0)
	$typeText = 'Добавлена вручную';
elseif ($keygroup['TYPE'] == 1)
	$typeText = 'Получена из базовых слов';
elseif ($keygroup['TYPE'] == 2)
	$typeText = 'Деактивирована';

$words = '';
if ($keygroup['BASE_ARRAY'])
{
	foreach ($keygroup['BASE_ARRAY'] as $i => $part)
		$words .= '<br />- ' . $part;
}

$dt = ConvertTimeStamp($keygroup['TS'], "FULL", "ru");
$ws = $keygroup['WORDSTAT'] == -1 ? 'не проверена' : $keygroup['WORDSTAT'];

?>
<div class="row-fluid">
	<div class="span4">
		<form id="keygroup_detail" class="form-horizontal">
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
			<div class="alerts"></div>
		</form>
	</div>
	<div class="span8">
		<h4>
			Добавить объявление по шаблону:
		</h4>
		<p><?

			$templates = \Local\Main\Templ::getByCategory($category['ID']);
			foreach ($templates as $templ)
			{
				?>
				<a href="<?= \Local\Main\Ad::getAddTemplHref($category, $keygroup, $templ) ?>"
				   class="btn add-ad" type="button"><?= $templ['NAME']?></a><?
			}

			?>
		</p>
		<h4>
			Создать объявление вручную:
		</h4>
		<p>
			<a href="<?= \Local\Main\Ad::getAddHref($category, $keygroup) ?>"
			   class="btn add-ad" type="button">Создать вручную</a>
		</p><?

		$ads = \Local\Main\Ad::getByKeygroup($keygroup['ID']);
		foreach ($ads as $ad)
		{
			$ad['HOST'] = $project['URL'];
			$ad['SCHEME'] = $category['DATA']['SCHEME'];
			\Local\Main\Ad::printExample($ad);

			$href = \Local\Main\Ad::getHref($category, $keygroup, $ad);
			?><p><a href="<?= $href ?>">Редактировать</a></p><?
		}
		?>
	</div>
</div>

<?


?>
<script type="text/javascript">
	siteOptions.keygroupPage = true;
</script><?