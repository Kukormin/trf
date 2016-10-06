<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Комбинации слов");

$assetInstance = \Bitrix\Main\Page\Asset::getInstance();
$assetInstance->addJs(SITE_TEMPLATE_PATH . '/js/ZeroClipboard.min.js');

?>
<style>
	textarea {width:500px;}
	#res {width:100%; border-collapse:collapse; border-spacing:0;}
	#res td, #res th {border:1px solid #777; padding: 1px 4px;}
	#res .process {background:url('/i/loader.gif') no-repeat;}
</style>
<form id="cont">
	<p>
		Максимальное количество колонок: <input name="max" type="text" />
	</p>
	<div>
		<input name="c1" type="checkbox" />
		<textarea name="t1"></textarea>
	</div>
</form>
<p>
	<button class="btn" id="add">Добавить</button>
	<button class="btn btn-primary">Комбинировать</button>
</p>
	<p><?

		/*?>
		<button class="btn" id="copy" data-clipboard-target="#buffer">Скопировать в буфер</button>
		<button class="btn" id="copy_stat" data-clipboard-target="#buffer_stat">Скопировать в буфер с
			частотой</button><?*/

		?>
		<button class="btn" id="copy">Скопировать в буфер</button>
		<button class="btn" id="copy_stat">Скопировать в буфер с
			частотой</button><?

		?>
	</p>
<div id="result"></div>

<script>
	$(document).ready(function() {
		var i = 1;
		var form = $('#cont');
		var result = $('#result');
		var Main = {};
		var buffer = '';

		$('#add').click(function() {
			i++;
			form.append('<div><input name="c' + i + '" type="checkbox" /> <textarea name="t' + i +
			'"></textarea></div>');
		});

		$('[name=t1]').val('прокат\nаренда\nзаказать\nвзять\nнапрокат');
		$('[name=c1]').prop('checked', true);
		$('#add').click();
		$('[name=t2]').val('мерседес\nмерс\nmercedes');
		$('[name=c2]').prop('checked', true);
		$('#add').click();
		$('[name=t3]').val('черный\nбелый\nblack\nwhite');

		$('.btn-primary').click(function() {
			if (form.hasClass('process'))
				return false;

			var post = form.serialize();
			form.addClass('process');
			result.html('');
			Main = {};
			$.ajax({
				type: "POST",
				url: '/ajax/combo.php',
				data: post,
				error: function() {

				},
				success: function(data) {
					result.html(data);
					buffer = '';
					result.find('.word').each(function() {
						var orig = $(this).text();
						var text = normal(orig);
						Main[text] = $(this).next();
						buffer += orig + '\n';
					});
					$('#buffer').val(buffer);
					//Mutagen();
					//Next();
				},
				complete: function() {
					form.removeClass('process');
				}
			});
		});

		function normal(text) {
			var ar = text.split(' ');
			return ar.sort().join(' ');
		}

		function Next() {
			var td = result.find('.nn:first');
			if (!td.length)
				return;

			Process(td);
		}

		result.on('click', 'td.nn', function() {
			Process($(this));
		});

		function Process(td) {
			if (td.hasClass('process'))
				return;

			var word = td.prev().text();
			td.addClass('process');
			$.ajax({
				type: "GET",
				url: '/ajax/wordstat.php?word=' + word,
				error: function() {

				},
				success: function(ret) {
					var keystring = ret.ua + ret.fuid + eval(ret.key);
					var data = ret.data;
					var res = "";
					for (var i = 0; i < data["length"]; i++) {
						res = res + String["fromCharCode"](data["charCodeAt"](i) ^ keystring["charCodeAt"](i % keystring["length"]))
					}
					var ar = {content: ""};
					if (res["match"]("^%7B.*%7D$")) {
						try {
							res = decodeURIComponent(res);
							ar = $["parseJSON"](res)
						} catch (e) {
						}
					}
					if (ar.content.includingPhrases) {
						var txt = ar.content.includingPhrases.info[2];
						var ar1 = txt.split(' ');
						var num = 0;
						if (ar1.length)
							num = ar1[0];
						td.text(num).removeClass('nn');
						var first = true;
						for (var j1 in ar.content.includingPhrases.items) {
							var obj1 = ar.content.includingPhrases.items[j1];
							if (first) {
								first = false;
							}
							else
								CheckPhrase(obj1.phrase, obj1.number);
						}
						for (var j2 in ar.content.phrasesAssociations.items) {
							var obj2 = ar.content.phrasesAssociations.items[j2];
							CheckPhrase(obj2.phrase, obj2.number);
						}

						//setTimeout(function() { Next() }, 5000);
					}
				},
				complete: function() {
					td.removeAttr('class');
				}
			});
		}

		function CheckPhrase(phrase, number) {
			var text = normal(phrase);
			if (Main[text])
				Main[text].text(number).removeClass('nn');
		}

		function Mutagen() {
			$.ajax({
				type: "POST",
				url: '/ajax/combo.php?ws=1',
				data: post,
				error: function() {
				},
				success: function(data) {
					result.html(data);
				},
				complete: function() {
					form.removeClass('process');
				}
			});
		}

		var client = new ZeroClipboard($('#copy').get(0));
		client.on( "ready", function( readyEvent ) {
			client.on( "copy", function( event ) {
				var clipboard = event.clipboardData;
				clipboard.setData("text/plain", buffer);
			} );
		} );

		var client1 = new ZeroClipboard($('#copy_stat').get(0));
		client1.on( "ready", function( readyEvent ) {
			client1.on( "copy", function( event ) {
				var buffer = '';
				result.find('.word').each(function() {
					buffer += $(this).text() + '\t' + $(this).next().text() + '\n';
				});

				var clipboard = event.clipboardData;
				clipboard.setData("text/plain", buffer);
			} );
		} );

	});
</script><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>