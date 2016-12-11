<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var bool $isSearch */
/** @var int $picId */

$style = $isSearch ? ' style="display:none;"' : '';
$src = '/i/no-pic.png';
if ($picId)
{
	$picture = \Local\Main\Picture::getPreview($picId);
	if ($picture['src'])
		$src = $picture['src'];
}

if (!$src)
	$picId = 0;

?>
<div class="control-group image-group"<?= $style ?>>
	<label class="control-label" for="pic">Картинка</label>
	<div class="controls">
		<div class="img-polaroid">
			<img src="<?= $src ?>" />
		</div>
		<input type="hidden" name="picture" value="<?= $picId ?>" />
		<div class="btn-group">
			<div class="btn">Загрузить с компьютера<input id="pic" name="pic" type="file" accept="image/*"
					/></div>
			<button class="btn dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu pull-right">
				<li>
					<a id="load_url"  href="javascript:void(0)">Указать url</a>
				</li>
				<li>
					<a id="loaded_pictures" href="javascript:void(0)">Выбрать из ранее загруженных</a>
				</li>
			</ul>
		</div>
		<div class="input-append" style="display:none;">
			<input class="span4" name="pic_url" type="text" />
			<button class="btn" type="button">Загрузить</button>
		</div>
		<div id="loaded_modal" class="modal hide fade" role="dialog" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel">Выберите картинку</h3>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</button>
				<button class="btn btn-primary">Выбрать</button>
			</div>
		</div>
	</div>
</div><?