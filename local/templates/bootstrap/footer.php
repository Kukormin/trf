			</div>
		</div>
	</div>
	<footer>
		Футер
	</footer>
	<div id="site_overlay"></div>
	<div id="alerts_cont"><?

		$alerts = \Local\System\Alerts::getActive();
		foreach ($alerts as $alert)
			echo \Local\System\Alerts::getHtml($alert);

		?>
	</div><?

	if ($GLOBALS['COMPONENTS']['YMAP'])
	{
		?>
		<script src="https://api-maps.yandex.ru/2.1/?load=package.standard,package.geoObjects&lang=ru-RU"></script><?
	}

	// TODO: WORK
			$v = '?v=2';
	?>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/jquery.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/bootstrap.min.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/common.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/category.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/combo.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/add_words.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/index.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/keygroup.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/keygrouplist.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/linkset.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/map.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/project.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/regime.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/templ.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/ad.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/pic.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/vcard.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/view.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/task.js<?= $v ?>"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/ready.js<?= $v ?>"></script><?

	$task = \Local\System\Task::getByCurrentUser();
	if ($task)
	{
		?>
		<script type="text/javascript">Task.on();</script><?
	}

	?>

</body>
</html>
