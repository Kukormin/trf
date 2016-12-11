<?
$return = '';

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	?>
	<div class="loaded-pics"><?

		$pics = \Local\Main\Picture::getByProject($projectId, true);
		foreach ($pics as $id => $pic)
		{
			?><div class="img-polaroid" data-id="<?= $id ?>">
				<img src="<?= $pic['src'] ?>" />
			</div><?
		}

		?>
	</div><?
}
