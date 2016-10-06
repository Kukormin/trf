<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Цены");

$assetInstance = \Bitrix\Main\Page\Asset::getInstance();
$assetInstance->addJs(SITE_TEMPLATE_PATH . '/js/ZeroClipboard.min.js');

?>
<style>
	textarea {width:500px; height:200px;}
	#res {width:100%; border-collapse:collapse; border-spacing:0;}
	#res td, #res th {border:1px solid #777; padding: 1px 4px;}
	#res .process {background:url('/i/loader.gif') no-repeat;}
</style>
<form id="cont">
	<p>
		<label>Url сайта</label>
		<input name="url" type="text" />
		<label>Регионы</label>
		<input name="regions" type="hidden" />
		<a href="#regionsModal" id="regionsPopup" role="button" class="btn">Выбрать</a>
		<div id="regionsModal" class="modal hide fade" tabindex="-1" role="dialog"
		     aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="regionsModalLabel">Выбор регионов</h3>
				<div class="input-append">
					<input id="region_search" type="text" autocomplete="off"
					       placeholder="Начните вводить название региона" />
					<button class="btn" type="button" id="region_search_btn">Найти</button>
				</div>
			</div>
			<div class="modal-body"><?
				$APPLICATION->IncludeComponent('tim:empty', 'regions', array());?>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Отменить</button>
				<button class="btn btn-primary" data-dismiss="modal">Сохранить и закрыть</button>
			</div>
		</div>
		<span id="regions_text"></span>
	</p>
	<h3>Фразы</h3>
	<p>
		<textarea id="keywords" name="keywords"></textarea>
	</p>
</form>
<button class="btn btn-primary">Начать</button>
<div id="result"></div>

<script>
	$(document).ready(function() {

		var region = '';
		var url = '';
		$('.btn-primary').click(function() {
			var table = '<table id="res"><tr><th>№</th><th>Фраза</th><th>11</th><th>12</th><th>13</th><th>21</th>' +
				'<th>22</th><th>23</th><th>24</th></tr>';

			var words = $('#keywords').val();
			var ar = words.split('\n');
			var num = 0;
			for (var i in ar) {
				var word = ar[i];
				if (!word)
					continue;

				num++;
				table += '<tr><td>' + num + '</td><td class="nn">' + word + '</td>' +
				'<td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
			}

			table += '</table>';
			$('#result').html(table);

			region = $('input[name=regions]').val();
			url = $('input[name=url]').val();

			Next();

			return false;
		});

		function Next() {
			var td = $('#result').find('.nn:first');
			if (!td.length)
				return;

			Process(td);
		}

		function Process(td) {
			if (td.hasClass('process'))
				return;

			var tr = td.parent();
			var word = td.text();
			if (!word)
				return;

			var s = '&w[]=' + word;
			for (var i = 1; i < 200; i++) {
				tr = tr.next();
				if (!tr.length)
					break;
				word = tr.children('.nn').text();
				s += '&w[]=' + word;
			}

			td.addClass('process');
			$.ajax({
				type: "POST",
				url: '/ajax/prices.php',
				data: 'url=' + url + '&regions=' + region + s,
				error: function() {

				},
				success: function(ret) {
					if (ret.ITEMS) {
						tr = td.parent();
						for (var i in ret.ITEMS) {
							var item = ret.ITEMS[i];
							if (tr.length) {
								if (item.P11)
									tr.children('td:eq(2)').text(item.P11);
								if (item.P12)
									tr.children('td:eq(3)').text(item.P12);
								if (item.P13)
									tr.children('td:eq(4)').text(item.P13);
								if (item.P21)
									tr.children('td:eq(5)').text(item.P21);
								if (item.P22)
									tr.children('td:eq(6)').text(item.P22);
								if (item.P23)
									tr.children('td:eq(7)').text(item.P23);
								if (item.P24)
									tr.children('td:eq(8)').text(item.P24);
								tr.children('td:eq(1)').removeClass('nn');
								tr = tr.next();
							}
							else
								break;
						}
						setTimeout(function() { Next() }, 100);
					}
				},
				complete: function() {
					td.removeAttr('class');
				}
			});
		}

		var client = new ZeroClipboard($('#copy').get(0));
		client.on( "ready", function() {
			client.on( "copy", function( event ) {
				var clipboard = event.clipboardData;
				clipboard.setData("text/plain", buffer);
			} );
		} );


	});
</script><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>