<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$projects = \Local\Project::getByCurrentUser();
$adding = \Local\Project::getAdding();
if ($adding)
	$newText = ' (Продолжить создание проекта "' . $adding['NAME'] . '")';

?>
<h1>Проекты</h1>
<a href="/projects/new/">Новый проект<?= $newText ?></a>

<table class="table table-striped table-hover">
	<thead>
	<tr>
		<th></th>
		<th>Проект</th>
		<th>Статистика</th>
		<th></th>
		<th></th>
	</tr>
	</thead>
	<tbody><?

	foreach ($projects as $project)
	{
		?>
		<tr>
			<td></td>
			<td><?= $project['NAME'] ?><br /><a href="/projects/<?= $project['ID'] ?>/">Профиль</a></td>
			<td></td>
			<td></td>
			<td></td>
		</tr><?
	}

	?>
	</tbody>
</table><?
