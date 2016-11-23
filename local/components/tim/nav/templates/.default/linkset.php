<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var Components\Navigaton $component */

$projectId = $component->projectId;
$project = $component->project;
$set = $component->set;

?>
<form id="linkset_detail" class="form-horizontal">
	<input type="hidden" name="pid" value="<?= $projectId ?>">
	<input type="hidden" name="sid" value="<?= $set['ID'] ?>">
	<input type="hidden" name="project_url" value="<?= $project['URL'] ?>">
	<div class="control-group">
		<label class="control-label" for="name">
			Название <i class="help" data-placement="top" data-original-title="Название набора быстрых ссылок"
			            data-content="Используется только для внутренней навигации"></i></label>
		<div class="controls">
			<input type="text" id="name" placeholder="Название" name="name" value="<?= $set['NAME'] ?>" />
			<span class="help-inline"></span><?

			if (!$set['NAME'])
			{
				?>
				<p class="examples">
					Напр.: <a href="#">Ссылки по-умолчанию</a>, <a href="#">Ссылки с контактами</a>
				</p><?
			}

			?>
		</div>
	</div>


	<div class="example">
		<div class="yandex-serp">
			<h2>
				<a class="link" target="_blank" href="http://<?= $project['URL'] ?>">
					<span class="favicon"></span>
					Заголовок <b>объявления</b>! / <?= $project['URL'] ?>
				</a>
			</h2>
			<div class="subtitle">
				<div class="path">
					<a class="link" target="_blank" href="http://<?= $project['URL'] ?>"><?= $project['URL'] ?>/<b>объявление</b>/</a>
				</div>
				<div class="lbl">Реклама</div>
			</div>
			<div class="content">
				<div class="text">Текст <b>объявления</b>. 5 лет гарантии! Звони!</div>
				<div class="sitelinks"><?

					for ($i = 1; $i < 5; $i++)
					{
						$item = $set['DATA']['ITEMS'][$i - 1];
						$hidden = $item['Title'] ? '' : ' class="hidden"';
						?>
						<div id="yandex<?= $i ?>"<?= $hidden ?>>
							<a target="_blank" href="http://<?= $project['URL'] ?><?= $item['Href'] ?>"><?= $item['Title'] ?></a>
						</div><?
					}

					?>
				</div>
				<div class="meta">
					<div>
						<a class="link" target="_blank" href="#">Контактная информация</a>
					</div><div>+7 (9999) 99-99-99</div><div>пн-пт 8:00-22:00, сб-вс 8:15-22:00</div>
				</div>
			</div>
		</div>
	</div>
	<ul class="rules">
		<li>Можно сохранить одну, две, три или четыре быстрые ссылки.</li>
		<li>Тексты ссылок должны быть разными</li>
		<li>Максимальная длина текста одной ссылки: 30 символов.</li>
		<li>Суммарная длина текста ссылок: не более 66 символов</li>
		<li>Быстрые ссылки не должны дублировать основную ссылку объявления</li>
		<li>Ссылки должны вести на разные страницы основного сайта</li>
	</ul>

	<div class="row-fluid"><?

		for ($i = 1; $i < 5; $i++)
		{
			$item = $set['DATA']['ITEMS'][$i - 1];
			?>
			<div class="link-item span3">
				<h4>Быстрая ссылка <?= $i ?></h4>
				<p>
					<label for="text<?= $i ?>">Текст</label>
					<input type="text" id="text<?= $i ?>" name="title[]" value="<?= $item['Title'] ?>" />
				</p>
				<p>
					<label for="href<?= $i ?>">Ссылка</label>
					<input type="text" name="host[]" value="<?= $project['URL'] ?>" disabled />
					<input type="text" id="href<?= $i ?>" name="href[]" value="<?= $item['Href'] ?>" />
				</p>
				<p>
					<label for="desc<?= $i ?>">Описание</label>
					<input type="text" id="desc<?= $i ?>" name="desc[]" value="<?= $item['Description'] ?>" />
				</p>
				<p class="checking">
					<span class="loader"></span>
					<span class="info text-error"></span>
				</p>
			</div><?
		}

		?>
	</div>
	<p>
		<button class="btn btn-primary" type="button">Сохранить</button>
		<button class="btn cancel" type="button">Отменить</button>
	</p>
	<div class="alerts"></div>
</form><?


?>
<script type="text/javascript">
	siteOptions.linksetPage = true;
</script><?