<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */

$path = $APPLICATION->GetCurDir();
$tree = \Local\Main\Project::getTreeByCurrentUser($path);

?>
<ul id="nav_tree"><?

	foreach ($tree as $project)
	{
		$class = $project['SELECTED'] ? ' class="selected"' : '';

		?>
		<li<?= $class ?>>
			<a href="<?= $project['HREF'] ?>"><?= $project['NAME'] ?></a> |
			<a href="<?= $project['HREF'] ?>links/">БС</a>
			<a href="<?= $project['HREF'] ?>vcards/">В</a>
			<ul><?
				foreach ($project['CATEGORIES'] as $cat)
				{
					$class = $cat['SELECTED'] ? ' class="selected"' : '';
					?>
					<li<?= $class ?>>
						<a href="<?= $cat['HREF'] ?>"><?= $cat['NAME'] ?></a> |
						<a href="<?= $cat['HREF'] ?>base/">База</a>
						<a href="<?= $cat['HREF'] ?>add/">Доп</a>
					<a href="<?= $cat['HREF'] ?>templates/">Ш</a><?

					/*?>
						<ul>
							<li><a href="<?= $cat['HREF'] ?>?type[y]=1&type[s]=1">Яндекс.Поиск</a></li>
							<li><a href="<?= $cat['HREF'] ?>?type[y]=1&type[n]=1">Яндекс.Сети</a></li>
							<li><a href="<?= $cat['HREF'] ?>?type[g]=1&type[s]=1">Google.Поиск</a></li>
							<li><a href="<?= $cat['HREF'] ?>?type[g]=1&type[n]=1">Google.Сети</a></li>
					</ul><?*/

					?>
					</li><?
				}
				?>
			</ul>
		</li><?
	}
	?>
</ul><?