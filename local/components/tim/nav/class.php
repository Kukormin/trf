<?
namespace Components;

use Local\Main\Ad;
use Local\Main\Category;
use Local\Main\Keygroup;
use Local\Main\Linkset;
use Local\Main\Project;
use Local\Main\Templ;
use Local\Main\Vcard;
use Local\Main\View;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

class Navigaton extends \CBitrixComponent
{
	/**
	 * Разделитель для формирования титла
	 */
	const TITLE_SEPARATOR = ' - ';

	private $nav = array();
	public $tabCode = '';
	public $projectId = 0;
	public $project = array();
	public $category = array();
	public $set = array();
	public $card = array();
	public $templ = array();
	public $keygroup = array();
	public $ad = array();
	public $view = array();

	public function addNav($href, $title, $bc = false, $tab = false)
	{
		if (!$bc)
			$bc = $title;
		$this->nav[] = array(
			'href' => $href,
			'title' => $title,
			'bc' => $bc,
			'tab' => $tab,
		);
	}

	public function executeComponent()
	{
		global $APPLICATION;

		$path = $_SERVER['REQUEST_URI'];
		$tmp = explode('?', $path);
		$path = $tmp[0];
		$parts = explode('/', $path);

		$template = 'index';
		$this->addNav('/', SITE_NAME, 'Главная');
		$this->tabCode = $parts[1];

		// Проект
		if ($parts[1] == Project::URL)
		{
			$template = 'project';

			// Новый проект
			if ($parts[2] == 'new')
			{
				// для формирования меню в шапке
				$GLOBALS['CURRENT_PROJECT_ID'] = 'new';

				$this->addNav('', 'Новый проект');
			}
			else
			{
				$this->projectId = intval($parts[2]);
				$this->project = Project::getById($this->projectId);
				if (!$this->project)
				{
					$APPLICATION->IncludeFile('/inc/404.php');
					return;
				}

				// для формирования меню в шапке
				$GLOBALS['CURRENT_PROJECT_ID'] = $this->projectId;

				if ($this->project['DATA']['NEW'])
				{
					$data = $this->project['DATA'];
					$data['NEW'] = false;
					Project::update($this->project, array('DATA' => $data));
				}

				$bc = '<span class="current_project_name">' . $this->project['NAME'] . '</span>';
				$this->addNav(Project::getHref($this->projectId), $this->project['NAME'], $bc);
				$this->tabCode = $parts[3];

				// Категория
				if ($parts[3] == Category::URL)
				{
					$template = 'category';

					// Новая категория
					if ($parts[4] == 'new')
					{
						$this->addNav('', 'Новая категория');
					}
					else
					{
						$categoryId = $parts[4];
						$this->category = Category::getById($categoryId, $this->projectId);
						if (!$this->category)
						{
							$APPLICATION->IncludeFile('/inc/404.php');
							return;
						}

						if ($this->category['DATA']['NEW'])
						{
							$data = $this->category['DATA'];
							$data['NEW'] = false;
							Category::update($this->category, array('DATA' => $data));
						}

						$bc = '<span class="current_category_name">' . $this->category['NAME'] . '</span>';
						$this->addNav(Category::getHref($this->category), $this->category['NAME'], $bc);
						$this->tabCode = $parts[5];

						// Ключевая фраза
						if ($parts[5] == Keygroup::URL && $parts[6])
						{
							$template = 'keygroup';

							$keygroupId = $parts[6];
							$this->keygroup = Keygroup::getById($keygroupId, $categoryId, $this->projectId);
							if (!$this->keygroup)
							{
								$APPLICATION->IncludeFile('/inc/404.php');
								return;
							}

							$this->addNav(Keygroup::getHref($this->category, $this->keygroup), $this->keygroup['NAME']);

							// Категория
							if ($parts[7] == Ad::URL)
							{
								$template = 'ad';

								if ($parts[8] == 'ynew')
								{
									$this->ad = array(
										'YANDEX' => 1,
										'SEARCH' => 1,
									);
									$this->addNav('', 'Добавление объявления для ' . DIRECT_NAME);
								}
								elseif ($parts[8] == 'gnew')
								{
									$this->ad = array(
										'YANDEX' => 0,
										'SEARCH' => 1,
									);
									$this->addNav('', 'Добавление объявления для ' . ADWORDS_NAME);
								}
								elseif ($parts[8] == 'new')
								{
									$this->templ = Templ::getById($parts[9], $categoryId);
									if (!$this->templ)
									{
										$APPLICATION->IncludeFile('/inc/404.php');
										return;
									}

									$this->ad = Ad::generateByTemplate($this->keygroup, $this->templ,
										$this->category);
									$this->addNav('', 'Добавление объявления по шаблону "' . $this->templ['NAME'] . '"');
								}
								else
								{
									$this->ad = Ad::getById($parts[8], $keygroupId);
									if (!$this->ad)
									{
										$APPLICATION->IncludeFile('/inc/404.php');
										return;
									}

									$this->addNav('', $this->ad['TITLE']);
								}
							}
						}
						// Шаблон
						elseif ($parts[5] == Templ::URL && $parts[6])
						{
							$template = 'templ';

							$this->addNav(Templ::getListHref($this->category), 'Шаблоны объявлений');

							if ($parts[6] == 'ynew')
							{
								$this->templ = array(
									'YANDEX' => 1,
									'SEARCH' => 1,
								);
								$this->addNav('', 'Добавление шаблона для ' . DIRECT_NAME);
							}
							elseif ($parts[6] == 'gnew')
							{
								$this->templ = array(
									'YANDEX' => 0,
									'SEARCH' => 1,
								);
								$this->addNav('', 'Добавление шаблона для ' . ADWORDS_NAME);
							}
							else
							{
								$this->templ = Templ::getById($parts[6], $categoryId);
								if (!$this->templ)
								{
									$APPLICATION->IncludeFile('/inc/404.php');
									return;
								}

								$this->addNav('', $this->templ['NAME']);
							}
						}
					}
				}
				// Быстрые ссылки
				elseif ($parts[3] == Linkset::URL && $parts[4])
				{
					$template = 'linkset';

					$this->addNav(Linkset::getListHref($this->projectId), 'Быстрые ссылки');

					if ($parts[4] == 'new')
					{
						$this->addNav('', 'Добавление набора быстрых ссылок');
					}
					else
					{
						$this->set = Linkset::getById($parts[4], $this->projectId);
						if (!$this->set)
						{
							$APPLICATION->IncludeFile('/inc/404.php');
							return;
						}

						$this->addNav('', $this->set['NAME']);
					}
				}
				// Визитки
				elseif ($parts[3] == Vcard::URL && $parts[4])
				{
					$template = 'vcard';

					$this->addNav(Vcard::getListHref($this->projectId), 'Визитки');

					if ($parts[4] == 'new')
					{
						$this->addNav('', 'Добавление визитки');
					}
					else
					{
						$this->card = Vcard::getById($parts[4], $this->projectId);
						if (!$this->card)
						{
							$APPLICATION->IncludeFile('/inc/404.php');
							return;
						}

						$this->addNav('', $this->card['NAME']);
					}
				}
			}
		}
		// Виды
		elseif ($parts[1] == View::URL && $parts[2])
		{
			$template = 'view';

			$this->addNav(View::getViewsHref(), 'Виды');

			if ($parts[2] == 'new')
			{
				$this->addNav('', 'Добавление вида');
			}
			else
			{
				$this->view = View::getById($parts[2]);
				if (!$this->view)
				{
					$APPLICATION->IncludeFile('/inc/404.php');
					return;
				}

				$this->addNav('', $this->view['NAME']);
			}
		}

		$this->IncludeComponentTemplate($template);

		$this->setTitles();
	}

	private function setTitles() {
		global $APPLICATION;

		$title = '';
		$js = '';
		$last = '';
		foreach ($this->nav as $item)
		{
			if ($title)
				$title = $this::TITLE_SEPARATOR . $title;
			$title = $item['title'] . $title;
			if (!$item['tab'])
			{
				if ($js)
					$js .= ',';
				$js .= '"' . $item['title'] . '"';
				$APPLICATION->AddChainItem($item['bc'], $item['href']);
				$last = $item['bc'];
			}
		}
		$APPLICATION->SetTitle($last);
		$APPLICATION->SetPageProperty('title', $title);
		?>
		<script type="text/javascript">
			siteOptions.titleParts = [<?= $js ?>];
			siteOptions.titleSep = '<?= $this::TITLE_SEPARATOR ?>';
		</script><?
	}

}