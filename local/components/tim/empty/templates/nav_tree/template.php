<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */

$path = $APPLICATION->GetCurDir();
$tree = \Local\Main\Project::getTreeByCurrentUser($path);

$user = \Local\Main\User::getCurrentUser();
$ygsn = $user['DATA']['YGSN'];

?>
<ul id="nav_tree"><?

	foreach ($tree as $project)
	{
		if (!$project['SELECTED'])
			continue;

		$class = $project['SELECTED'] ? ' class="selected"' : '';

		?>
		<li<?= $class ?>>
			<a href="<?= $project['HREF'] ?>"><?= $project['NAME'] ?></a><?

			?>
			<ul><?
				foreach ($project['CATEGORIES'] as $cat)
				{
					$class = $cat['SELECTED'] ? ' class="selected"' : '';
					?>
					<li<?= $class ?>>
						<a href="<?= $cat['HREF'] ?>?ygsn=1111"><?= $cat['NAME'] ?></a><?

						/*?>
							<a href="<?= $cat['HREF'] ?>base/">База</a>
							<a href="<?= $cat['HREF'] ?>add/">Доп</a>
							<a href="<?= $cat['HREF'] ?>templates/">Ш</a><?*/

						?>
						<ul><?

							$class = '';
							if ($cat['SELECTED'])
							{
								$class = 'cat_selected_ys';
								if ($ygsn['y'] && $ygsn['s'])
									$class .= ' selected';
							}
							?>
							<li class="<?= $class ?>"><a href="<?= $cat['HREF'] ?>?ygsn=1010">Яндекс.Поиск</a></li><?

							$class = '';
							if ($cat['SELECTED'])
							{
								$class = 'cat_selected_yn';
								if ($ygsn['y'] && $ygsn['n'])
									$class .= ' selected';
							}
							?>
							<li class="<?= $class ?>"><a href="<?= $cat['HREF'] ?>?ygsn=1001">Яндекс.Сети</a></li><?

							$class = '';
							if ($cat['SELECTED'])
							{
								$class = 'cat_selected_gs';
								if ($ygsn['g'] && $ygsn['s'])
									$class .= ' selected';
							}
							?>
							<li class="<?= $class ?>"><a href="<?= $cat['HREF'] ?>?ygsn=0110">Google.Поиск</a></li><?

							$class = '';
							if ($cat['SELECTED'])
							{
								$class = 'cat_selected_gn';
								if ($ygsn['g'] && $ygsn['n'])
									$class .= ' selected';
							}
							?>
							<li class="<?= $class ?>"><a href="<?= $cat['HREF'] ?>?ygsn=0101">Google.Сети</a></li><?

						?>
						</ul><?

						?>
					</li><?
				}
				?>
			</ul>
		</li><?
	}
	?>
</ul><?