<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$projects = \Local\Project::getByCurrentUser();

?>
<p>
	<a class="btn btn-primary" href="/p/new/">Новый проект</a>
</p>

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
		$href = \Local\Project::getHref($project['ID']);
		?>
		<tr>
			<td></td>
			<td><a href="<?= $href ?>"><?= $project['NAME'] ?></a></td>
			<td></td>
			<td></td>
			<td></td>
		</tr><?
	}

	?>
	</tbody>
</table><?
