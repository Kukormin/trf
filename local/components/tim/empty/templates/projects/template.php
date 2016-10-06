<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */

$path = $_SERVER['REQUEST_URI'];
$tmp = explode('?', $path);
$path = $tmp[0];
$parts = explode('/', $path);
$titleParts = array(SITE_NAME);

$projectId = $parts[2];
// для формирования меню в шапке
$GLOBALS['CURRENT_PROJECT_ID'] = $projectId;

if ($projectId == 'new')
{
	// Новый проект
	$href = \Local\Project::getProjectsHref();
	$APPLICATION->AddChainItem('Проекты', $href);
	$href = \Local\Project::getNewHref();
	$name = 'Новый проект';
	$APPLICATION->AddChainItem($name, $href);
	$titleParts[] = $name;
	$APPLICATION->SetTitle($name);
	include('project.php');
}
elseif (!$projectId)
{
	// Список проектов
	include('projects_list.php');
	$href = \Local\Project::getProjectsHref();
	$APPLICATION->SetTitle('Проекты');
	$titleParts[] = 'Проекты';
}
else
{
	$projectId = intval($projectId);
	$project = \Local\Project::getById($projectId);
	if (!$project)
	{
		$APPLICATION->IncludeFile('/inc/404.php');
		return;
	}

	if ($project['DATA']['NEW'])
	{
		$data = $project['DATA'];
		$data['NEW'] = false;
		\Local\Project::update($project, array('DATA' => $data));
	}

	$href = \Local\Project::getProjectsHref();
	$APPLICATION->AddChainItem('Проекты', $href);
	$href = \Local\Project::getHref($projectId);
	$projectName = '<span class="current_project_name">' . $project['NAME'] . '</span>';
	$APPLICATION->AddChainItem($projectName, $href);
	$titleParts[] = $project['NAME'];

	$key = $parts[3];
	$id = $parts[4];

	// Категория
	if ($key == \Local\Category::URL)
	{
		$categoryId = $id;
		if ($categoryId == 'new')
		{
			$name = 'Новая категория';
			$APPLICATION->AddChainItem($name, '');
			$APPLICATION->SetTitle($name);
		}
		else
		{
			$categoryId = intval($categoryId);
			$category = \Local\Category::getById($categoryId, $projectId);
			if (!$category)
			{
				$APPLICATION->IncludeFile('/inc/404.php');
				return;
			}

			if ($category['DATA']['NEW'])
			{
				$data = $category['DATA'];
				$data['NEW'] = false;
				\Local\Category::update($category, array('DATA' => $data));
			}

			$href = \Local\Category::getHref($category);
			$name = '<span class="current_category_name">' . $category['NAME'] . '</span>';
			$APPLICATION->AddChainItem('Категория: ' . $name, $href);
			$APPLICATION->SetTitle($name);
			$name = $category['NAME'];
		}

		$titleParts[] = $name;
		$tabCode = $parts[5];
		include('title_parts.php');
		include('category.php');
	}
	// Быстрые ссылки
	elseif ($key == \Local\Linkset::URL && $id)
	{
		$APPLICATION->AddChainItem('Быстрые ссылки', $href . \Local\Linkset::URL . '/');

		$setId = $id;
		if ($setId == 'new')
		{
			$name = 'Добавление набора быстрых ссылок';
			$APPLICATION->AddChainItem($name, $href);
			$APPLICATION->SetTitle($name);
		}
		else
		{
			$setId = intval($setId);
			$set = \Local\Linkset::getById($setId, $projectId);
			if (!$set)
			{
				$APPLICATION->IncludeFile('/inc/404.php');
				return;
			}

			$name = $set['NAME'];
			$APPLICATION->AddChainItem($name);
			$APPLICATION->SetTitle($name);
		}
		$titleParts[] = $name;
		include('title_parts.php');
		include('linkset.php');
	}
	// Визитки
	elseif ($key == \Local\Vcard::URL && $id)
	{
		$APPLICATION->AddChainItem('Визитки', $href . \Local\Vcard::URL . '/');

		$cardId = $id;
		if ($cardId == 'new')
		{
			$name = 'Добавление визитки';
			$APPLICATION->AddChainItem($name, $href);
			$APPLICATION->SetTitle($name);
		}
		else
		{
			$cardId = intval($cardId);
			$card = \Local\Vcard::getById($cardId, $projectId);
			if (!$card)
			{
				$APPLICATION->IncludeFile('/inc/404.php');
				return;
			}

			$name = $card['NAME'];
			$APPLICATION->AddChainItem($name);
			$APPLICATION->SetTitle($name);
		}
		$titleParts[] = $name;
		include('title_parts.php');
		include('vcard.php');
	}
	// Шаблоны объявлений
	elseif ($key == \Local\Templ::URL && $id)
	{
		$APPLICATION->AddChainItem('Шаблоны объявлений', $href . \Local\Templ::URL . '/');

		$templId = $id;
		if ($templId == 'new')
		{
			$name = 'Добавление шаблона';
			$APPLICATION->AddChainItem($name, $href);
			$APPLICATION->SetTitle($name);
		}
		else
		{
			$templId = intval($templId);
			$templ = \Local\Templ::getById($templId, $projectId);
			if (!$templ)
			{
				$APPLICATION->IncludeFile('/inc/404.php');
				return;
			}

			$name = $templ['NAME'];
			$APPLICATION->AddChainItem($name);
			$APPLICATION->SetTitle($name);
		}
		$titleParts[] = $name;
		include('title_parts.php');
		include('templ.php');
	}
	// Проект
	else
	{
		$tabCode = $key;
		include('title_parts.php');
		include('project.php');
		$APPLICATION->SetTitle($projectName);
	}
}

$resultTitle = \Local\Utils::getTitle($titleParts);
$APPLICATION->SetPageProperty('title', $resultTitle);
