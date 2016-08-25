<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */

$id = $_REQUEST['id'];
$project = \Local\Project::getById($id);
if (!$project)
{
	$APPLICATION->IncludeFile('/inc/404.php');
	return;
}

?>
<a href="/projects/">Проекты</a><?

debugmessage($project);
