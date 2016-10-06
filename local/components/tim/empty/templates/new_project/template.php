<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */

$step = 10;
$project = \Local\Project::getAdding();
if ($project)
	$step = $project['STEP'];

if ($project && $_REQUEST['new'] == 'Y')
{
	\Local\Project::delete($project['ID']);
	LocalRedirect('/projects/new/');
}

$user = \Local\ExtUser::getCurrentUser();
if ($user['INTERFACE']['EXTENDED'])
	include('extended.php');
else
	include('simple.php');
