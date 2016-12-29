<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */
/** @var array $category */
/** @var array $project */

$max = $category['DATA']['BASE_MAX'];
if (!$max)
	$max = 4;

$colCount = count($category['DATA']['BASE']);
if ($colCount < 7)
	$colCount = 7;
$rowCount = 10;
foreach ($category['DATA']['BASE'] as $item)
{
	$cnt = count($item['WORDS']) + 1;
	if ($cnt > $rowCount)
		$rowCount = $cnt;
}

?>
	<div class="copy_area">
		<textarea class="copy_textarea" id="copy_textarea"></textarea>
	</div>
	<form id="base_words">
		<input type="hidden" name="cid" value="<?= $category['ID'] ?>" />
		<input type="hidden" name="pid" value="<?= $project['ID'] ?>" />
		<legend>Семантическое ядро категории</legend>
		<p>
			<input type="text" id="max" name="max" value="<?= $max ?>" />
			Максимальное количество колонок, при формировании ключевой фразы
		</p>
		<p>
			<button class="btn btn-primary" type="button">Комбинировать ключевые фразы</button>
			<span class="loader-big"></span>
			Всего ключевых фраз: <strong id="total_cnt"></strong>
		</p>
		<div id="base_words_wrap">
			<table id="base_words_table">
				<thead>
				<tr>
					<th class="f"></th><?
					for ($j = 1; $j <= $colCount; $j++)
					{
						$char = chr(64 + $j);
						$right = '';
						if ($j == $colCount)
							$right = '<i></i>';
						?>
						<th><?= $right ?><?= $char ?></th><?
					}
					?>
				</tr>
				<tr>
					<th class="f"></th><?
					for ($j = 1; $j <= $colCount; $j++)
					{
						$item = $category['DATA']['BASE'][$j - 1];
						$checked = $item['REQ'] ? ' checked' : '';
						?>
						<th><label><input type="checkbox"<?= $checked ?> /> Обязательно</label></th><?
					}
					?>
				</tr>
				</thead>
				<tbody><?

				for ($i = 1; $i <= $rowCount; $i++)
				{
					?>
					<tr>
					<td class="f"><?= $i ?></td><?
					for ($j = 1; $j <= $colCount; $j++)
					{
						$item = $category['DATA']['BASE'][$j - 1];
						$word = $item['WORDS'][$i - 1];
						$class = $item['REQ'] ? ' class="req"' : '';
						?>
						<td<?= $class ?>><?= $word ?></td><?
					}
					?>
					</tr><?
				}
				?>
				</tbody>
			</table>
			<div class="edit">
				<input type="text" />
			</div>
		</div>
		<div class="alerts"></div>
	</form>
	<form class="result_cont"></form>
<?