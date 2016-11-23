<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var array $arParams */

$minutes = array('00', '15', '30', '45');
$strValue = $arParams['VALUE'];
if (!$strValue)
	$strValue = '0;4;09;00;18;00';
$arValue = explode(';', $strValue);
$l = count($arValue);
$tmp = array();
for ($i = 0; $i < $l; $i += 6)
{
	$from = $arValue[$i];
	$to = $arValue[$i + 1];
	$time = array(
		$arValue[$i + 2],
		$arValue[$i + 3],
		$arValue[$i + 4],
		$arValue[$i + 5],
	);
	$key = $time[0] . ':' . $time[1] . '-' . $time[2] . ':' . $time[3];
	if (!$tmp[$key])
	{
		$tmp[$key] = array(
			'TIME' => $time,
		    'DAYS' => array(),
		);
	}
	for ($j = $from; $j <= $to; $j++)
	{
		$tmp[$key]['DAYS'][$j] = $j;
	}
}

$hiddenInputName = 'regime';
if ($arParams['NAME'])
	$hiddenInputName = $arParams['NAME'];

?>
<input class="required" type="hidden" name="<?= $hiddenInputName ?>" value="<?= $strValue ?>" />
<div class="cont"><?
	foreach ($tmp as $part)
	{
		?><div class="regime_part"><?
			for ($j = 0; $j < 7; $j++)
			{
				$active = isset($part['DAYS'][$j]) ? ' active' : '';
				?>
				<span class="dw<?= $j ?><?= $active ?>"></span><?
			}
			?>
			<i class="icon-time"></i>
			<select class="from_h"><?
				for ($j = 0; $j <= 24; $j++)
				{
					$pre = $j < 10 ? '0' : '';
					$val = $pre . $j;
					$selected = $part['TIME'][0] === $val ? ' selected' : '';
					?>
					<option<?= $selected ?>><?= $val ?></option><?
				}
				?>
			</select> :
			<select class="from_m"><?
				foreach ($minutes as $val)
				{
					$selected = $part['TIME'][1] === $val ? ' selected' : '';
					?>
					<option<?= $selected ?>><?= $val ?></option><?
				}
				?>
			</select> —
			<select class="to_h"><?
				for ($j = 0; $j <= 24; $j++)
				{
					$pre = $j < 10 ? '0' : '';
					$val = $pre . $j;
					$selected = $part['TIME'][2] === $val ? ' selected' : '';
					?>
					<option<?= $selected ?>><?= $val ?></option><?
				}
				?>
			</select> :
			<select class="to_m"><?
				foreach ($minutes as $val)
				{
					$selected = $part['TIME'][3] === $val ? ' selected' : '';
					?>
					<option<?= $selected ?>><?= $val ?></option><?
				}
				?>
			</select>
			<i class="icon-minus-sign"></i>
			<i class="icon-plus-sign"></i>
			<br />
			<em class="works">будни</em><em class="holidays">выходные</em><em
				class="every">ежедневно</em><em class="t24">круглосуточно</em>
		</div><?
	}
	?>
</div>