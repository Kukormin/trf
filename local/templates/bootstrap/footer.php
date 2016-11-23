			</div>
		</div>
	</div>
	<footer>
		Футер
	</footer>
	<div id="site_overlay"></div><?

	if ($GLOBALS['COMPONENTS']['YMAP'])
	{
		?>
		<script src="https://api-maps.yandex.ru/2.1/?load=package.standard,package.geoObjects&lang=ru-RU"></script><?
	}

	// TODO: WORK
	?>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/jquery.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/bootstrap.min.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/common.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/category.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/index.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/keygroup.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/keygrouplist.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/linkset.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/map.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/project.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/regime.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/templ.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/ad.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/vcard.js"></script>
	<script src="<?= SITE_TEMPLATE_PATH ?>/js/work/ready.js"></script>

</body>
</html>
