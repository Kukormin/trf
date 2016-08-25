<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Комбинации слов");

?>
<style>
	textarea {width:500px;}
	#res {width:100%; border-collapse:collapse; border-spacing:0;}
	#res td, #res th {border:1px solid #777; padding: 1px 4px;}
	#res .process {background:url('/i/loader.gif') no-repeat;}
</style>
<form id="cont">
	<div>
		<input name="c1" type="checkbox" />
		<textarea name="t1"></textarea>
	</div>
</form>
<p>
	<button class="btn" id="add">Добавить</button>
	<button class="btn btn-primary">Комбинировать</button>
</p>
<div id="result"></div>

<script>
	$(document).ready(function() {
		var i = 1;
		var form = $('#cont');
		var result = $('#result');
		$('#add').click(function() {
			i++;
			form.append('<div><input name="c' + i + '" type="checkbox" /> <textarea name="t' + i +
			'"></textarea></div>');
		});
		$('.btn-primary').click(function() {
			if (form.hasClass('process'))
				return false;

			form.addClass('process');
			result.html('');
			$.ajax({
				type: "POST",
				url: '/ajax/combo.php',
				data: form.serialize(),
				error: function() {
				},
				success: function(data) {
					result.html(data);
					Next();
				},
				complete: function() {
					form.removeClass('process');
				}
			});
		});

		function Next() {
			var td = result.find('.nn:first');
			if (!td.length)
				return;

			var word = td.prev().text();
			td.addClass('process')
			$.ajax({
				type: "GET",
				url: '/ajax/wordstat.php?word=' + word,
				error: function() {

				},
				success: function(data) {
					td.html(data);
				},
				complete: function() {
					td.removeAttr('class');
					//Next();
				}
			});
		}

	});
</script><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>