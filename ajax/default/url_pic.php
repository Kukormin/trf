<?
$return = array();

$projectId = intval($_REQUEST['pid']);
$categoryId = intval($_REQUEST['cid']);
$keygroupId = intval($_REQUEST['kgid']);

// для проверки авторизации
$category = \Local\Main\Category::getById($categoryId, $projectId);
if ($category)
{
	$file = CFile::MakeFileArray($_REQUEST['pic_url']);
	$res = \Local\Main\Picture::check($file);
	if ($res)
	{
		$return['alerts'] = array(
			array('<p>' . $res . '</p>', 'error'),
		);
		return;
	}

	$fileId = \Local\Main\Picture::upload($file, $projectId);
	if (!$fileId)
	{
		$return['alerts'] = array(
			array('<p>Ошибка сохранения файла</p>', 'error'),
		);
		return;
	}

	$return['id'] = $fileId;
	$return['file'] = \Local\Main\Picture::getPreview($fileId);
}
