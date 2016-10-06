<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$words = array();
	$req = array();
	$max = 0;
	foreach ($_POST as $k => $v)
	{
		if ($k == 'max')
		{
			$max = $v;
			continue;
		}

		$i = substr($k, 1);
		$key = substr($k, 0, 1);
		if ($key == 'c' && $v == 'on')
			$req[$i] = true;
		if ($key == 't')
		{
			$words[$i] = array();
			if (!$req[$i])
				$words[$i][] = '';
			$tok = strtok($v, "\n");
			while ($tok) {
				$tok = trim($tok);
				if ($tok)
					$words[$i][] = $tok;
				$tok = strtok("\n");
			}
		}
	}

	$pos = array();
	foreach ($words as $k => $ar)
		$pos[$k] = 0;

	function f($prev, $level, $cnt, $words, $max, &$res)
	{
		if ($max > 0 && $cnt > $max)
			return;

		$i = $level + 1;
		if ($i <= count($words))
			foreach ($words[$i] as $s)
			{
				if ($s)
				{
					$pre = $prev . ' ' . $s;
					f($pre, $i, $cnt + 1, $words, $max, $res);
				}
				else
				{
					f($prev, $i, $cnt, $words, $max, $res);
				}
			}
		else
		{
			$prev = trim($prev);
			if ($prev)
				$res[] = $prev;
		}

	}

	$res = array();
	f('', 0, 0, $words, $max, $res);

	/*if ($_GET['ws'] && $res)
	{

		$filename = $_SERVER['DOCUMENT_ROOT'] . '/_log/ws.txt';
		if (file_exists($filename))
		{
			$ljResponse = file_get_contents($filename);
		}
		else
		{

			require ($_SERVER["DOCUMENT_ROOT"] . '/local/lib/mutagen/mutagen.php');
			$ljClient = new IXR_Client('mutagen.ru', '/?xmlrpc');
			$mutagen = new mutagen($ljClient);

			$ljClient->query('mutagen.login',"lenchik37", md5("bd19m84g"), "true");
			$ljResponse = $ljClient->getResponse();

			$ljResponse = $mutagen->getWords($res, 0);

			//file_put_contents($filename, $ljResponse);
		}

		debugmessage($ljResponse);
	}*/

	?>
	<table id="res">
	<tr><th>№</th><th>Фраза</th><th>Кол-во</th></tr><?
	$i = 0;
	foreach ($res as $s)
	{
		$i++;
		?><tr><td><?= $i ?></td><td class="word"><?= $s ?></td><td id="ws<?= $i ?>" class="nn"></td></tr><?
	}
	?></table><?

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}