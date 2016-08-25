<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Закачка регионов из яндекса
//\Local\Region::sync();
//debugmessage(\Local\Region::getAll());

$arUrls = array();
for ($i = 1; $i <= 3; $i++)
{
	$arUrls[$i] = array();
	$filename = $_SERVER["DOCUMENT_ROOT"] . '/_log/url' . $i . '.txt';
	$handle = fopen($filename, 'r');
	while (!feof($handle)) {
		$tmp = trim(fgets($handle));
		if (!isset($arUrls[$i][$tmp]))
			$arUrls[$i][$tmp] = $tmp;
	}
	fclose($handle);
}

if ($_REQUEST['url'])
{
	if (in_array($_REQUEST['url'], $arUrls))
		$arUrls[] = $_REQUEST['url'];
}

?>
<style>
	.results {width:100%; border-collapse:collapse; border-spacing:0;}
	.results td, .results th {border:1px solid #777; padding: 1px 4px;}
	.results tr.ok td {background:#bfb;}
	.results tr.error td {background:#fbb;}
	.results tr.process td {background:#ffb;}
</style>

<p>
	<input id="test_all" type="button" value="Парсить все"/>
</p>
<table class="results">
	<tr>
		<th>Действия</th>
		<th>Url</th>
		<th>Название</th>
		<th>Телефон</th>
		<th>Email</th>
		<th>ИМ?</th>
		<th>Продукт</th>
	</tr><?

	$i = 0;
	foreach ($arUrls as $ar)
		foreach ($ar as $url)
		{
			$i++;
			?>
			<tr class="test" id="r<?= $i ?>">
				<td><input type="button" value="Парсить"/></td>
				<td><?= $url ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr><?
		}
?>
</table>

<script type="text/javascript" charset="utf-8" src="/local/templates/bootstrap/js/jquery.js"></script>
<script>
	$(document).ready(function() {
		$('tr input').click(function() {
			var tr = $(this).parent().parent();
			parse(tr);
		});
		$('#test_all').click(function() {
			$('.results tr').each(function() {
				parse($(this));
			});
		});

		function parse(tr) {
			if (tr.hasClass('process'))
				return false;

			tr.addClass('process');
			var url = tr.children('td:eq(1)').text();
			$.ajax({
				type: "GET",
				url: '/ajax/parse_site_tmp.php?url=' + url,
				error: function() {

				},
				success: function(data) {
					tr.children('td:eq(2)').html(data.NAME);
					if (data.PHONE)
						tr.children('td:eq(3)').html(data.PHONE.prefix + '<br />' + data.PHONE.code + '<br />' +
						data.PHONE.number);
					else
						tr.children('td:eq(3)').html('');
					tr.children('td:eq(4)').html(data.EMAIL);
					tr.children('td:eq(5)').html(data.ESTORE ? 'да' : 'нет');
					tr.children('td:eq(6)').html(data.PRODUCT_);
				},
				complete: function() {
					tr.removeClass('process');
				}
			});
		}
	});
</script><?

