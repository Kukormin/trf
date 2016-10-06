<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var array $titleParts */

$s = '';
foreach ($titleParts as $part)
{
	if ($s)
		$s .= ',';
	$s .= '"' . $part . '"';
}
?>
<script type="text/javascript">
	siteOptions.titleParts = [<?= $s ?>];
	siteOptions.titleSep = '<?= \Local\Utils::TITLE_SEPARATOR ?>';
</script><?