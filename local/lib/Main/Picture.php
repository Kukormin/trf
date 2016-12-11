<?
namespace Local\Main;

use Local\System\ExtCache;

/**
 * Картинка для РСЯ
 */
class Picture
{
	/**
	 * Путь для кеширования
	 */
	const CACHE_PATH = 'Local/Main/Picture/';

	public static function getByProject($projectId, $refreshCache = false)
	{
		$projectId = intval($projectId);
		$extCache = new ExtCache(
			array(
				__FUNCTION__,
				$projectId,
			),
			static::CACHE_PATH . __FUNCTION__ . '/',
			864000,
			false
		);
		if (!$refreshCache && $extCache->initCache())
			$return = $extCache->getVars();
		else
		{
			$extCache->startDataCache();

			$return = array();
			$file = new \CFile;
			$rsFiles = $file->GetList(array(), array(
				'MODULE_ID' => 'proj_' . $projectId,
			));
			while ($item = $rsFiles->Fetch())
				$return[$item['ID']] = self::getPreview($item['ID'], $file);

			$extCache->endDataCache($return);
		}

		return $return;
	}

	public static function getPreview($id, $file = null)
	{
		if (!$file)
			$file = new \CFile();
		return $file->ResizeImageGet(
			$id,
			array(
				'width' => 90,
				'height' => 90
			),
			BX_RESIZE_IMAGE_PROPORTIONAL
		);
	}

	public static function check($tmp)
	{
		return \CFile::CheckImageFile($tmp);
	}

	public static function upload($tmp, $projectId)
	{
		$file = new \CFile;

		// масштабируем изображение
		$file->ResizeImage(
			$tmp,
			array(
				'width' => 1200,
				'height' => 1200
			),
			BX_RESIZE_IMAGE_PROPORTIONAL
		);

		$tmp['MODULE_ID'] = 'proj_' . $projectId;
		$fileId = $file->SaveFile($tmp, 'pic');

		if ($fileId)
			self::getByProject($projectId, true);

		return $fileId;
	}
}
