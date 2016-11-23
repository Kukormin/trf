/**
 * Сохранение переключения табов в историю
 * и переключение табов при нажатии кнопок "назад" и "вперед"
 * @type {{init: Function, show: Function}}
 */
var Tabs = {
	init: function() {
		var historyTabs = $('.history-tabs');
		if (historyTabs.length) {
			historyTabs.find('a').click(function () {
				var li = $(this).parent();
				if (!li.is('.active')) {
					var url = $(this).attr('href');
					var newTitle = Titles.setTitle($(this).text());
					history.pushState('', newTitle, url);
					Tabs.show($(this), li);
				}

				return false;
			});
			// Событие хождения по истории
			$(window).on('popstate', function (e) {
				var href = e.target.location.pathname;
				var a = $('ul.nav-tabs a[href="' + href + '"]');
				var li = a.parent();
				if (!li.is('.active')) {
					Titles.setTitle(a.text());
					Tabs.show(a, li);
				}
			});
		}
	},
	show: function(a, li) {
		var id = a.data('id');
		var tab = $(id);
		li.addClass('active').siblings('.active').removeClass('active');
		tab.addClass('active').siblings('.active').removeClass('active');
	}
};

/**
 * Управление заголовками браузера
 * @type {{base: string, sep: string, parts: Array, init: Function, setBase: Function, setTitle: Function}}
 */
var Titles = {
	base: '',
	sep: ' - ',
	parts: [],
	init: function() {
		this.parts = siteOptions.titleParts;
		this.sep = siteOptions.titleSep;
		this.setBase();
	},
	setBase: function() {
		this.base = '';
		var l = this.parts.length;
		for (var i = 0; i < l; i++) {
			if (this.base)
				this.base = this.sep + this.base;
			this.base = this.parts[i] + this.base;
		}
	},
	setTitle: function(part) {
		var title = part + this.sep + this.base;
		document.title = title;
		return title;
	},
	updateTitle: function() {
		this.setBase();
		var txt = $('ul.history-tabs li.active a').text();
		this.setTitle(txt);
	},
	changeProjectName: function(name) {
		$('.current_project_name').text(name);
		this.parts[1] = name;
		this.updateTitle();
	},
	changeCategoryName: function(name) {
		$('.current_category_name').text(name);
		this.parts[2] = name;
		this.setBase();
		this.updateTitle();
	}
};

var CMN = {
	overlay: false,
	body: false,
	request: {},
	init: function() {
		this.overlay = $('#site_overlay');
		this.body = $('body');
		// Смена режима Начинающий/Эксперт
		$('.user-settings-regime > a').click(function() {
			$.ajax({
				type: "POST",
				url: '/ajax/user_settings.php',
				data: 'interface=' + $(this).data('id'),
				success: function () {
					window.location.reload();
				}
			});
			return false;
		});
		// Подстановка значения из подсказки
		this.body.on('click', 'p.examples a', function() {
			var input = $(this).parent().siblings('input');
			if (input.length) {
				input.val($(this).text());
				input.trigger('input');
				input.focus();
			}
			return false;
		});
	},
	/**
	 * Аякс запрос по-умолчанию
	 * @param action
	 * @param userOptions
	 * @param successCallback
	 */
	ajax: function(action, userOptions, successCallback) {
		var options = {
			form: false,
			post: '',
			strategy: 0,
			custom: false,
			overlay: false,
			endless: false,
			quick: false,
			hideloader: false
		};
		if (userOptions) {
			if (userOptions.form)
				options.form = userOptions.form;
			if (userOptions.post)
				options.post = userOptions.post;
			if (userOptions.strategy)
				options.strategy = userOptions.strategy;
			if (userOptions.custom)
				options.custom = userOptions.custom;
			if (userOptions.overlay)
				options.overlay = userOptions.overlay;
			if (userOptions.endless)
				options.endless = userOptions.endless;
			if (userOptions.quick)
				options.quick = userOptions.quick;
			if (userOptions.hideloader)
				options.hideloader = userOptions.hideloader;
		}

		if (CMN.request[action]) {
			// Не давать запускать запрос, пока ответ по прошлому не пришел
			if (options.strategy == 1)
				return;
			// Прерывать старый и запускать новый
			else if (options.strategy == 2)
				CMN.request[action].abort();
		}

		var post = '';
		var alerts = false;
		if (options.form) {
			options.form.addClass('process');
			if (!options.hideloader)
				options.form.addClass('loaders');
			post = options.form.serialize();
			alerts = options.form.find('.alerts');
		}

		var url = action;
		if (!options.custom) {
			url = 'default';
			if (post)
				post += '&';
			post += 'action=' + action;
		}
		if (options.post) {
			if (post)
				post += '&';
			post += options.post;
		}

		if (options.overlay)
			CMN.showOverlay();

		CMN.request[action] = $.ajax({
			type: 'POST',
			url: '/ajax/' + url + '.php',
			data: post,
			error: function () {
				if (alerts)
					BS.showAlert(alerts, 'Неизвестная ошибка. Обратитесь в службу поддержки', 'error');
			},
			success: function (data) {
				if (data.endless) {
					options.endless = true;
				}

				if (options.quick) {
					if (successCallback)
						successCallback(data);
				}
				else {
					if (data.redirect) {
						location.href = data.redirect;
						options.endless = true;
					}
					else if (data.reload) {
						window.location.reload();
						options.endless = true;
					}
					else {
						if (data.alerts && alerts) {
							var l = data.alerts.length;
							for (var i = 0; i < l; i++)
								BS.showAlert(alerts, data.alerts[i][0], data.alerts[i][1]);
						}
						if (successCallback)
							successCallback(data);
					}
				}
			},
			complete: function () {
				if (!options.endless) {
					if (options.form) {
						options.form.removeClass('process');
						options.form.removeClass('loaders');
						options.form.find('span.loader.inp').removeClass('inp');
					}
					if (options.overlay)
						CMN.hideOverlay();
				}
				CMN.request[action] = false;
			}
		});
	},
	showOverlay: function() {
		var h = CMN.body.height();
		CMN.overlay.height(h);
		CMN.overlay.show();
	},
	hideOverlay: function() {
		CMN.overlay.hide();
	},
	getHost: function(value) {
		if (!value)
			return false;

		var ar = value.split('://');
		if (ar.length > 1)
			value = ar[1];

		ar = value.split('/');
		value = ar[0];

		ar = value.split('@');
		if (ar.length > 1)
			value = ar[1];

		ar = value.split(':');
		value = ar[0];

		return value;
	},
	validateHost: function(value) {
		if (!value)
			return false;

		var ar = value.split('.');
		var l = ar.length;
		return l > 1 && ar[l - 1].length > 1;
	},
	historyBack: function() {
		history.back();
		// history.back() - непонятно есть ли страница раньше в истории
		// а если вызывать назад по хлебным крошкам сразу, то back не срабатывает
		setTimeout(CMN.breadCrumbBack, 100);
		return false;
	},
	breadCrumbBack: function() {
		location.href = $('.breadcrumb a:last').attr('href');
	}
};

var BS = {
	closeBtn: '<button type="button" class="close" data-dismiss="alert">×</button>',
	init: function() {
		// Выпадающие списки
		$('.dropdown-toggle').dropdown();
		// Всплывашки на "вопросиках"
		$('i.help').popover({trigger: 'hover'});
	},
	showAlert: function(cont, html, style) {
		if (cont.length && html) {
			var cl = '';
			if (style)
				cl = ' alert-' + style;
			html = '<div class="alert' + cl + '">' + this.closeBtn + html + '</div>';
			cont.append(html);
		}
	}
};
