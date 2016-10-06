<?
$return = array();

$cardId = intval($_REQUEST['cid']);
$projectId = intval($_REQUEST['pid']);
$name = trim($_REQUEST['name']);
$onlyCheck = $_REQUEST['only_check'] == 'Y';

$ex = false;

$project = \Local\Project::getById($projectId);
if ($project)
{
	$cards = \Local\Vcard::getByProject($project['ID']);
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
		$data = array(
			'CITY' => $_REQUEST['city'],
		);
		$newCard = array(
			'NAME' => $name,
			'PROJECT' => $project['ID'],
			'DATA' => $data,
		);
		if ($cardId)
		{
			$card = \Local\Vcard::getById($cardId, $project['ID']);
			$card = \Local\Vcard::update($card, $newCard);
		}
		else
			$card = \Local\Vcard::add($newCard);

		$return['redirect'] = \Local\Vcard::getListHref($project['ID']);
	}
}

$return['EX'] = $ex;
