<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/**
 * Выводит HTML код региона со всеми потомками
 * @param $tree
 * @param $id
 */
function printRegion($tree, $id)
{
	$region = \Local\Region::getByYandex($id);
	$cl = $tree[$id] ? ' st-c' : '';
	?>
	<div class="r<?= $cl ?>" id="r<?= $id ?>">
		<span></span>
		<input type="checkbox" id="cb<?= $id ?>" />
		<label for="cb<?= $id ?>"><?= $region['NAME'] ?></label><?

		if ($tree[$id])
		{
			?>
			<div class="c"><?
				foreach ($tree[$id] as $child)
					printRegion($tree, $child);
				?>
			</div><?
		}

	?></div><?
}

$all = \Local\Region::getAll();
$tree = array();
$js = '';
foreach ($all as $region)
{
	if (!$region['YANDEX'])
		continue;

	$tree[$region['PARENT']][] = $region['YANDEX'];
	if ($js)
		$js .= ',';
	$js .= "'" . $region['NAME'] . "'";
}

?>
<div class="region_tree"><?
	foreach ($tree[0] as $child)
		printRegion($tree, $child);
	?>
</div><?


?>
<script>var allRegions = [<?= $js ?>];</script><?
