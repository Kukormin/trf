<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */
/** @var $arParams */

$catalog = $arParams['CATALOG'];
$indexUrl = $arParams['URL'];

function printSection($catalog, $tree, $id)
{
	$item = $catalog[$id];
	$cl = $tree[$id] ? ' st-c' : ' st-e';
	?>
	<div class="r<?= $cl ?>" id="rc<?= $id ?>">
		<span class="ce"></span>
		<span class="icon-list"></span>
		<input class="sections" type="checkbox" id="cbc<?= $id ?>"/>
		<label for="cbc<?= $id ?>"><?= $item['NAME'] ?></label><?

		if ($tree[$id])
		{
			?>
			<div class="c"><?
			foreach ($tree[$id] as $child)
				printSection($catalog, $tree, $child);
			?>
			</div><?
		}
		else
		{
			?>
			<div class="c">
				<div class="r" id="rc<?= $id ?>">
					<span></span>
					<input class="products" type="checkbox" id="cbg<?= $id ?>"/>
					<label for="cbg<?= $id ?>">Товары</label>
				</div>
			</div><?
		}

	?>
	</div><?
}

function printLink($url, $name)
{
	$id = 0;
	?>
	<div class="r" id="rc<?= $id ?>">
		<span class="ce"></span>
		<span class="icon-list"></span>
		<input class="sections" type="checkbox" id="cbc<?= $id ?>"/>
		<label for="cbc<?= $id ?>"><?= $name ?> <a href="<?= $url ?>"><?= $url ?></a></label>
	</div><?
}

$tree = array();
foreach ($arParams['CATALOG'] as $item)
{
	if (!$item['ID'])
		continue;
	$tree[$item['PARENT']][] = $item['ID'];
}

?>
<div class="catalog_tree"><?
	$url = '';
	?>
	<div class="r" id="rc-3">
		<span class="ce"></span>
		<span class="icon-list"></span>
		<input class="sections" type="checkbox" id="cbc-3"/>
		<label for="cbc-3">Главная <a href="<?= $indexUrl ?>"><?= $indexUrl ?></a></label>
	</div>
	<div class="r st-c" id="rc-1">
		<span class="ce"></span>
		<span class="icon-list"></span>
		<input class="sections" type="checkbox" id="cbc-1"/>
		<label for="cbc-1">Товары</label>
		<div class="c"><?
			foreach ($tree[0] as $child)
				printSection($catalog, $tree, $child);
		?>
		</div>
	</div>
	<div class="r st-c" id="rc-2">
		<span class="ce"></span>
		<span class="icon-list"></span>
		<input class="sections" type="checkbox" id="cbc-2"/>
		<label for="cbc-2">Дополнительные ссылки</label>
		<div class="c"><?
			foreach ($arParams['LINKS'] as $url => $name)
				printLink($url, $name);
			?>
		</div>
	</div><?

?>
</div>
<ul id="catalog_menu" class="dropdown-menu" role="menu" aria-labelledby="dLabel">
	<li role="presentation">
		<a id="all_cat" href="#" tabindex="-1" role="menuitem">Выделить все подкатегории</a>
	</li>
	<li role="presentation">
		<a id="all_prod" href="#" tabindex="-1" role="menuitem">Выделить все товары</a>
	</li>
	<li role="presentation">
		<a id="add_s" href="#" tabindex="-1" role="menuitem">Добавить страницу</a>
	</li>
	<li class="divider" role="presentation"></li>
	<li role="presentation">
		<a id="to_up" href="#" tabindex="-1" role="menuitem">На уровень выше</a>
	</li>
	<li role="presentation">
		<a id="to_0" href="#" tabindex="-1" role="menuitem">На верхний уровень</a>
	</li>
	<li role="presentation">
		<a id="to_cat" href="#" tabindex="-1" role="menuitem">Переместить в...</a>
	</li>
	<li class="divider" role="presentation"></li>
	<li role="presentation">
		<a id="delete" href="#" tabindex="-1" role="menuitem">Удалить</a>
	</li>
</ul>
<?
