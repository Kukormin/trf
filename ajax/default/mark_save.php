<?
$return = array();

$newItems = array();
foreach ($_REQUEST['mark'] as $id => $mark)
{
	$name = trim($mark);
	$color = trim($_REQUEST['color'][$id]);
	if (substr($color, 0, 1) == '#')
		$color = substr($color, 1);
	if ($name)
		$newItems[$id] = array(
			'NAME' => $name,
			'COLOR' => $color,
		);
}

$cache = false;
$curItems = \Local\Main\Mark::getByCurrentUser();
foreach ($curItems as $cur)
{
	$new = $newItems[$cur['ID']];
	if ($new)
	{
		$new = \Local\Main\Mark::update($cur, $new);
		if ($new['UPDATED'])
			$cache = true;
		unset($newItems[$cur['ID']]);
	}
	else
	{
		\Local\Main\Mark::delete($cur['ID']);
		$cache = true;
	}
}
foreach ($newItems as $new)
{
	\Local\Main\Mark::add($new['NAME'], $new['COLOR']);
	$cache = true;
}

if ($cache)
	\Local\Main\Mark::getByCurrentUser(true);

$return['messages'] = array(
	array('<p>Успешно сохранено</p>', 'success'),
);
