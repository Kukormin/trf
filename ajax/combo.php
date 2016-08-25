<?
//TODO: включить
//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$words = array();
	$req = array();
	foreach ($_POST as $k => $v)
	{
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

	function f($prev, $level, $words, &$res)
	{
		$i = $level + 1;
		if ($i <= count($words))
			foreach ($words[$i] as $s)
			{
				$pre = $prev . ' ' . $s;
				f($pre, $i, $words, $res);
			}
		else
		{
			$prev = trim($prev);
			if ($prev)
				$res[] = $prev;
		}

	}

	$res = array();
	f('', 0, $words, $res);

	?>
	<table id="res">
	<tr><th>Фраза</th><th>Кол-во</th></tr><?
	foreach ($res as $s)
	{
		?><tr><td><?= $s ?></td><td class="nn"></td></tr><?
	}
	?></table><?

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}