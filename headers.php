<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заголовки");

$assetInstance = \Bitrix\Main\Page\Asset::getInstance();
$assetInstance->addJs(SITE_TEMPLATE_PATH . '/js/ZeroClipboard.min.js');

?>
<style>
	textarea {width:500px; height:200px;}
	#cont input {width:500px;}
	#res {width:100%; border-collapse:collapse; border-spacing:0;}
	#res td, #res th {border:1px solid #777; padding: 1px 4px;}
</style>
<form id="cont">
	<h3>Заголовки</h3>
	<p>
		<textarea name="headers"></textarea>
	</p>
	<h3>Описания (сначала самое длинное)</h3>
	<div>
		<p><input type="text" /> <span></span></p>
	</div>
</form>
<p>
	<button class="btn" id="add">Добавить</button>
	<button class="btn btn-primary">Создать</button>
</p>
<p>
	<button class="btn" id="copy">Скопировать в буфер</button>
</p>
	<p>
		<input type="text" class="replace_from" />
		<input type="text" class="replace_to" />
		<button class="btn" id="replace">Заменить</button>
	</p>
<div id="result"></div>

<script>
	$(document).ready(function() {
		var form = $('#cont');
		var cont = form.children('div');
		var buffer = '';
		var ta = form.find('textarea');

		$('#add').click(function() {
			cont.append('<p><input type="text" /> <span>0</span></p>');
		});

		form.on('click', 'input', function() {
			CalcLength($(this));
		});
		form.on('change', 'input', function() {
			CalcLength($(this));
		});
		form.on('keyup', 'input', function() {
			CalcLength($(this));
		});
		form.on('paste', 'input', function() {
			var input = $(this);
			setTimeout(function() {
				CalcLength(input);
			}, 200);

		});
		form.on('cut', 'input', function() {
			var input = $(this);
			setTimeout(function() {
				CalcLength(input);
			}, 200);
		});

		function CalcLength(input) {
			var s = input.val();
			input.next().text(s.length);
		}
		form.find('input').each(function() {
			CalcLength($(this));
		});


		$('.btn-primary').click(function() {
			var table = '<table id="res"><tr><th>№</th><th>Заголовок</th><th></th><th>Описание</th><th></th></tr>';
			buffer = '';

			var ar1 = ta.val().split('\n');
			var ar2 = [];
			form.find('input').each(function() {
				ar2.push($(this).val());
			});

			var num = 0;
			for (var i in ar1) {
				var s = ar1[i];
				if (!s)
					continue;

				var header = '';
				var desc = '';
				if (s.length > 33) {
					var ar3 = s.split(' ');
					var part1 = '';
					for (var j in ar3) {
						var s1 = ar3[j];
						if (!part1) {
							part1 = s1;
							continue;
						}

						if (!header) {
							var tmp = part1 + ' ' + s1;
							if (tmp.length > 33) {
								header = part1;
								desc = s1;
							}
							else
								part1 = tmp;
						}
						else {
							desc += ' ' + s1;
						}

					}
					desc += '!'
				}
				else {
					header = s;
					if (s.length < 33)
						header += '!';
				}

				for (var i2 in ar2) {
					var d = ar2[i2];
					if (desc)
						d = desc + ' ' + d;
					if (d.length <= 75) {
						desc = d;
						break;
					}
				}

				num++;
				table += '<tr><td>' + num + '</td><td class="h">' + header + '</td><td>' + header.length +
				'</td><td>' + desc + '</td><td>' + desc.length + '</td></tr>';
				buffer += header + '\t' + desc + '\n';

			}

			table += '</table>';
			$('#result').html(table);
		});

		$('#replace').click(function() {
			var from = $('.replace_from').val();
			var to = $('.replace_to').val();

			var ar1 = ta.val().split('\n');
			for (var i in ar1) {
				var s = ar1[i];
				var s1 = s.replace(from, to);
				ar1[i] = s1;
			}
			ta.val(ar1.join('\n'));
			$('.btn-primary').click();
		});


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