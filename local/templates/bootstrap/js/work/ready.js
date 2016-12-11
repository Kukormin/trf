/**
 * При загрузке страницы
 */
$(document).ready(function() {

	// Сохранение переключения табов в историю
	// и переключение табов при нажатии кнопок "назад" и "вперед"
	Tabs.init();

	// Управление титлами
	Titles.init();

	// Bootstrap js
	BS.init();

	// Общее
	CMN.init();

	// Главная страница
	if (siteOptions.indexPage) {
		Marks.init();
		ViewList.init();
	}

	// Страница проекта
	if (siteOptions.projectPage) {
		ProjectPage.init();
	}

	// Страница категории
	if (siteOptions.categoryPage) {
		CategoryPage.init();
		Words.init();
	}

	// Список ключевых групп с фильтрами
	if (siteOptions.keygroupFilters) {
		KeyGroupList.init();
	}

	// Страница быстрых ссылок
	if (siteOptions.linksetPage) {
		Linkset.init();
	}

	// Страница визиток
	if (siteOptions.vcardPage) {
		Vcard.init();
		Regime.init();
		PointMap.init();
	}

	// Страница шаблонов объявлений
	if (siteOptions.templPage) {
		Templ.init();
		Pic.init();
	}

	// Страница ключевых фраз
	if (siteOptions.keygroupPage) {
		KeyGroup.init();
	}

	// Страница объявлений
	if (siteOptions.adPage) {
		Ad.init();
		Pic.init();
	}

	// Страница видов
	if (siteOptions.viewPage) {
		View.init();
	}

});