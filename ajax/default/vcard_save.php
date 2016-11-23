<?
$return = array();

$cardId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$project = \Local\Main\Project::getById($projectId);
if ($project)
{
	$cards = \Local\Main\Vcard::getByProject($project['ID']);
	foreach ($cards as $card)
	{
		if ($card['ID'] == $cardId)
			continue;

		if ($card['NAME'] == $name)
		{
			$ex = true;
			break;
		}
	}

	if (!$ex && !$onlyCheck)
	{
		$newCard = array(
			'NAME' => $name,
			'PROJECT' => $project['ID'],
			'DATA' => $_REQUEST['data'],
		);
		if ($cardId)
		{
			$card = \Local\Main\Vcard::getById($cardId, $project['ID']);
			$card = \Local\Main\Vcard::update($card, $newCard);
		}
		else
			$card = \Local\Main\Vcard::add($newCard);

		$return['redirect'] = \Local\Main\Vcard::getListHref($project['ID']);
	}
}

$return['EX'] = $ex;
