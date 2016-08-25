<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

?>
<p>Вы можете настроить расписание показа объявлений с точностью до одного часа. При этом,
	показы должны быть разрешены не менее 40 часов в рабочие дни</p>
<p>Часовой пояс:</p>
<p><i class="icon-info-sign"></i>Кликните на квадратик, чтобы поменять настройку часа. Кликните и, не отпуская,
                                       проведите, чтобы поменять настройки нескольких соседних часов.</p>
<div class="time_table"><?

	for ($i = 1; $i <= 7; $i++)
	{
		?>
		<div class="dw_row<?= $i < 6 ? ' work_day' : '' ?>">
			<dl><dt class="dw<?= $i ?>"></dt><dd><input type="checkbox"/></dd></dl><?
			for ($j = 1; $j <= 24; $j++)
			{
				?><span class="t100"></span><?
			}
			?>
		</div><?
	}
	?>
	<div class="bot">
		<dl><dt></dt><dd></dd></dl><?
		for ($j = 1; $j <= 24; $j++)
		{
			?><span><input type="checkbox"/><?= ($j-1) ?><br /><?= $j ?></span><?
		}
		?>
	</div>
</div>
<p>Всего часов в рабочие дни:<span class="work"></span></p>