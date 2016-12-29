<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("tmp");


/*
function f1($key)
{
	$ar = explode('"', $key);
	$cnt = count($ar);
	if ($cnt == 5)
	{
		$tmp = f2($ar[3], $ar[4]);
		return $ar[1] . $tmp;
	}
	elseif ($cnt == 7)
	{
		debugmessage($ar);
		$tmp = f2($ar[5], $ar[6]);
		return $ar[1] . $ar[3] . $tmp;
	}

	return '';
}

function f2($tmp, $op)
{
	$ar1 = explode('.', $op);
	foreach ($ar1 as $k)
	{
		$key = substr($k, 0, 4);
		if ($key == 'spli')
		{
			$ar2 = explode("('", $k);
			if (count($ar2) == 2)
			{
				$ar3 = explode("')", $ar2[1]);
				if (count($ar3) == 2)
					$tmp = f_split($ar3[0], $tmp);
			}
		}
		elseif ($key == 'join')
		{
			$ar2 = explode("('", $k);
			if (count($ar2) == 2)
			{
				$ar3 = explode("')", $ar2[1]);
				if (count($ar3) == 2)
					$tmp = implode($ar3[0], $tmp);
			}
		}
		elseif ($key == 'conc')
		{
			$ar2 = explode("(", $k);
			if (count($ar2) == 2)
			{
				$ar3 = explode(")", $ar2[1]);
				if (count($ar3) == 2)
				{
					$ar4 = explode("^", $ar3[0]);
					if (count($ar4) == 2)
						$tmp .= intval($ar4[0]) ^ intval($ar4[1]);
				}
			}
		}
		elseif ($key == 'subs')
		{
			$ar2 = explode("(", $k);
			if (count($ar2) > 1)
			{
				$ar3 = explode(")", $ar2[1]);
				if (count($ar3) > 1)
					$tmp = substr($tmp, $ar3[0]);
			}
		}
		elseif ($key == 'reve')
		{
			$tmp = array_reverse($tmp);
		}
	}
	return $tmp;
}

function f_split($char, $tmp)
{
	if ($char === '')
		return str_split($tmp);

	$return = array();
	$l = strlen($tmp);
	$cur = '';
	for ($i = 0; $i < $l; $i++)
	{
		if ($tmp[$i] == $char)
		{
			$return[] = $cur;
			$cur = '';
		}
		else
			$cur .= $tmp[$i];
	}
	$return[] = $cur;

	return $return;
}

$key = 'var f968 = function(v219){var t519="787256e";var tv523=v219;return function(v219){return t519.concat(v219.concat(tv523))}("0d6a95b1d35")};f968("a9c".concat(972859^87560).substr(4).split(\'a\').join(\'b\').split(\'a\').join(\'b\'))';
$res = f1($key);
debugmessage($res);

$key = 'var f970 = function(v607){var t345="6052f7366";var tv198=v607;return function(v607){return t345.concat(v607.concat(tv198))}("4d")};f970("e6ef9".split(\'a\').join(\'b\').concat(937145^857569).substr(3).split(\'a\').join(\'b\'))';
$res = f1($key);
debugmessage($res);

$key = 'var f744 = function(v949){var t383="7011a7";var tv675=v949;return function(v949){return t383.concat(v949.concat(tv675))}("ceb2e19")};f744("3d89".split(\'\').reverse().join(\'\').split(\'a\').join(\'b\').split(\'a\').join(\'b\'))';
$res = f1($key);
debugmessage($res);

$key = 'var f493 = function(v399){var t902="ebc196";var tv505=v399;return function(v399){return t902.concat(v399.concat(tv505))}("739f3495")};f493("174c0".split(\'\').reverse().join(\'\').split(\'\').reverse().join(\'\').split(\'\').reverse().join(\'\'))';
$res = f1($key);
debugmessage($res);



?>
<script>
	var f490 = function(v296){var t757 = "fd096";return function(){return t757.concat(v296)}}("84e47aaf1c6a".split('a').join('b').split('a').join('b').concat(885346^868322).substr(5));
	var f600 = function(v666){var t693 = "ce26d04";return function(){return t693.concat(v666)}}("edd5087bd".split('').reverse().join('').concat(705^189211).substr(5).split('a').join('b'));f600()
	var f130 = function(v857){var t754 = "64";return function(){return t754.concat(v857)}}("c41a".split('a').join('b').split('a').join('b').concat(355816^268094).substr(3));f130()
	var f968 = function(v219){var t519="787256e";var tv523=v219;return function(v219){return t519.concat(v219.concat(tv523))}("0d6a95b1d35")};f968("a9c".concat(972859^87560).substr(4).split('a').join('b').split('a').join('b'))
	console.log("a9c".concat(972859^87560).substr(4).split('a').join('b').split('a').join('b'));
	console.log(f968("a9c".concat(972859^87560).substr(4).split('a').join('b').split('a').join('b')));
	var f970 = function(v607){var t345="6052f7366";var tv198=v607;return function(v607){return t345.concat(v607.concat(tv198))}("4d")};f970("e6ef9".split('a').join('b').concat(937145^857569).substr(3).split('a').join('b'))
	console.log(f970("e6ef9".split('a').join('b').concat(937145^857569).substr(3).split('a').join('b')));
	var f744 = function(v949){var t383="7011a7";var tv675=v949;return function(v949){return t383.concat(v949.concat(tv675))}("ceb2e19")};f744("3d89".split('').reverse().join('').split('a').join('b').split('a').join('b'))
	console.log(f744("3d89".split('').reverse().join('').split('a').join('b').split('a').join('b')));
	var f493 = function(v399){var t902="ebc196";var tv505=v399;return function(v399){return t902.concat(v399.concat(tv505))}("739f3495")};f493("174c0".split('').reverse().join('').split('').reverse().join('').split('').reverse().join(''))
	console.log(f493("174c0".split('').reverse().join('').split('').reverse().join('').split('').reverse().join('')));
</script><?*/

/*$ws = new \Local\Yandex\Wordstat();
$res = $ws->getStat($_REQUEST['w']);*/


$category = \Local\Main\Category::getById(17, 6527);
$items = \Local\Main\Category::combo($category);
$ws = new \Local\Yandex\Wordstat($category['ID']);
$ws->checkBaseWords($items);

/*$json = json_encode($res);

?>
	<script>
		var ret = <?= $json ?>;
		var keystring = ret.ua + ret.fuid + eval(ret.key);
		var data = ret.data;
		console.log(data["length"]);
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
	</script><?


/*?>
<h3>Кампании для пользователей директа:</h3><?
$clients = Clients::getByCurrentUser();
foreach ($clients['ITEMS'] as $client)
{
	$campaigns = \Local\Direct\Import::checkCampaigns($client);
	$showSyncButton = false;

	?>
	<h4><?= $client["NAME"] ?></h4>
	<table class="table table-striped table-hover">
	<thead>
	<tr>
		<th>Название</th>
		<th>№</th>
		<th>Статус</th>
		<th></th>
	</tr>
	</thead>
	<tbody><?

	foreach ($campaigns['ITEMS'] as $campaign)
	{
		$status = '';
		if ($campaign['ACTIVE'] != 'Y')
			$status = 'Не импортирована';
		else
		{
			if ($campaign['CHANGES'])
			{
				$status = 'Есть изменения для синхронизации';
				$showSyncButton = true;
			}
			else
				$status = 'Синхронизирована';
		}
		?>
		<tr>
		<td><?= $campaign['NAME'] ?></td>
		<td><?= $campaign['CampaignID'] ?></td>
		<td><?= $status ?></td>
		<td><?
			if ($campaign['ACTIVE'] != 'Y')
			{
				?>
				<a href="/personal/campaign/import.php?client=<?= $client['ID'] ?>&id=<?= $campaign['ID']
				?>">Импортировать</a><?
			}
			?>
		</td>
		</tr><?
	}

	?>
	</tbody>
	</table><?

	if ($showSyncButton)
	{
		?>
		<p>
			<button class="btn btn-primary client_sync" type="button" data-loading-text="В процессе..."
			        data-client="<?= $client['ID'] ?>">Синхронизировать</button>
		</p>
		<div class="results"></div><?
	}

}

?>
	<h3>Действия</h3>
	<p><a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=<?= DIRECT_API_CLIENT_ID
		?>">Подключить текущего пользователя Директа</a></p>
	<p><a href="update.php">Обновить данные</a></p>
	<p><a href="tmp.php">tmp</a></p><?*/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");