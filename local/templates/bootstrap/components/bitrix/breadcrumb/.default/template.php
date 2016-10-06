<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arResult */

ob_start();

$l = count($arResult);
if ($l)
{
	$l--;
	?>
	<ul class="breadcrumb"><?
		foreach ($arResult as $i => $item)
		{
			if ($i < $l)
			{
				?>
				<li><a href="<?= $item['LINK'] ?>"><?= $item['TITLE'] ?></a> <span class="divider">/</span></li><?
			}
			else
			{
				?>
				<li class="active"><?= $item['TITLE'] ?></li><?
			}
		}

	?>
	</ul><?
}

$strReturn = ob_get_contents();
ob_end_clean();

return $strReturn;
