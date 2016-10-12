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
	 * @param form
	 * @param action
	 * @param addParams
	 * @param successCallback
	 * @param quick
	 */
	ajax: function(form, action, addParams, successCallback, quick) {
		if (form.hasClass('process'))
			return;

		form.addClass('process');

		var post = form.serialize();
		var url = 'default.php';
		if (quick)
			url = action + '.php';
		else {
			if (post)
				post += '&';
			post += 'action=' + action;
		}
		if (addParams) {
			if (post)
				post += '&';
			post += addParams;
		}

		var alerts = form.find('.alerts');
		var endless = false;
		var check = addParams && addParams.indexOf('only_check=Y') >= 0;
		var useOverlay = form.data('overlay') && !check;
		if (useOverlay)
			CMN.showOverlay();

		$.ajax({
			type: 'POST',
			url: '/ajax/' + url,
			data: post,
			error: function () {
				BS.showAlert(alerts, 'Неизвестная ошибка. Обратитесь в службу поддержки', 'error');
			},
			success: function (data) {
				if (data.endless) {
					endless = true;
				}

				if (quick) {
					if (successCallback)
						successCallback(data);
				}
				else {
					if (data.redirect) {
						location.href = data.redirect;
						endless = true;
					}
					else if (data.reload) {
						window.location.reload();
						endless = true;
					}
					else {
						if (data.alerts) {
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
				if (!endless) {
					form.removeClass('process');
					form.find('span.loader.inp').removeClass('inp');
					if (useOverlay)
						CMN.hideOverlay();
				}
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

if (siteOptions.indexPage) {
	var Marks = {
		form: false,
		rowsDiv: false,
		addRowBtn: false,
		saveBtn: false,
		init: function () {
			this.form = $('#mark-form');
			this.rowsDiv = this.form.find('.rows');
			this.addRowBtn = this.form.find('.add-row');
			this.saveBtn = this.form.find('.btn-primary');

			this.addRowBtn.click(this.addRow);
			this.saveBtn.click(this.save);
			this.form.on('input', '.color', this.changeColor);
		},
		addRow: function() {
			var html = '<div class="control-group"><input type="text" placeholder="Название" name="mark[]" />' +
						' #<input type="text" class="color" placeholder="Код цвета" name="color[]" /> <i class="mark"></i></div>';
			Marks.rowsDiv.append(html);
		},
		changeColor: function() {
			var col = $(this).val();
			if (col.substr(0, 1) == '#')
				col = col.substr(1);
			col = col ? ('#' + col) : 'none';
			var i = $(this).siblings('i');
			i.css('background', col);
		},
		save: function() {
			CMN.ajax(Marks.form, 'mark_save');
			return false;
		}
	};
}

if (siteOptions.projectPage) {
	var ProjectPage = {
		detail: false,
		settingsForm: false,
		btnSave: false,
		nameInput: false,
		urlInput: false,
		nameControlGroup: false,
		urlControlGroup: false,
		nameHelp: false,
		urlHelp: false,
		parseLoader: false,
		nameExamples: false,
		name: '',
		url: '',
		nameTimerId: 0,
		urlTimerId: 0,
		init: function() {
			this.detail = $('#project_detail');
			this.settingsForm = this.detail.find('#settings_form');
			this.btnSave = this.settingsForm.find('#save_settings');
			this.nameInput = this.settingsForm.find('#name');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.urlInput = this.settingsForm.find('#url');
			this.urlControlGroup = this.urlInput.closest('.control-group');
			this.urlHelp = this.urlControlGroup.find('.help-inline');
			this.parseLoader = this.urlControlGroup.find('span.loader');
			this.nameExamples = this.nameControlGroup.find('.examples');

			this.name = this.nameInput.val();
			this.url = this.urlInput.val();

			this.nameInput.on('input', this.checkProjectName);
			this.urlInput.on('input', this.checkProjectUrl);
			this.btnSave.click(this.saveSettings);
		},
		checkProjectName: function() {
			var newName = ProjectPage.nameInput.val();
			if (ProjectPage.name == newName)
				return;

			ProjectPage.name = newName;
			if (ProjectPage.nameTimerId)
				clearTimeout(ProjectPage.nameTimerId);
			if (ProjectPage.name) {
				ProjectPage.nameControlGroup.removeClass('error');
				ProjectPage.nameHelp.text('');
				ProjectPage.btnSave.prop('disabled', false);
				ProjectPage.nameTimerId = setTimeout(ProjectPage.saveSettingsAjax, 500);
			}
			else {
				ProjectPage.nameControlGroup.addClass('error');
				ProjectPage.nameHelp.text('Введите название проекта');
				ProjectPage.btnSave.prop('disabled', true);
			}
		},
		saveSettingsAjax: function(save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax(ProjectPage.settingsForm, 'project_save_settings', addParams, function(data) {
				if (data.EX)
				{
					ProjectPage.nameControlGroup.addClass('error');
					ProjectPage.nameHelp.text('Проект с таким именем уже существует');
					ProjectPage.btnSave.prop('disabled', true);
				}
				else
				{
					ProjectPage.nameControlGroup.removeClass('error');
					ProjectPage.nameHelp.text('');
					ProjectPage.btnSave.prop('disabled', false);
					if (data.name)
						Titles.changeProjectName(data.name);
				}
			});
		},
		checkProjectUrl: function() {
			var newUrl = ProjectPage.urlInput.val();
			if (ProjectPage.url == newUrl)
				return;

			ProjectPage.url = newUrl;
			if (ProjectPage.urlTimerId)
				clearTimeout(ProjectPage.urlTimerId);
			var host = CMN.getHost(newUrl);
			var valid = CMN.validateHost(host);
			if (valid) {
				ProjectPage.urlControlGroup.removeClass('error');
				ProjectPage.urlHelp.text('');
				ProjectPage.btnSave.prop('disabled', false);
				ProjectPage.urlTimerId = setTimeout(ProjectPage.parseSite(), 500);
			}
			else
			{
				ProjectPage.urlControlGroup.addClass('error');
				ProjectPage.urlHelp.text('Не верный формат url');
				ProjectPage.btnSave.prop('disabled', true);
			}
		},
		parseSite: function() {
			ProjectPage.parseLoader.addClass('inp');
			CMN.ajax(ProjectPage.settingsForm, 'parse_site', '', function(data) {
				if (data.error == 'load_error')
				{
					ProjectPage.urlControlGroup.addClass('warning');
					ProjectPage.urlHelp.text(ProjectPage.url + ' - ошибка загрузки сайта');
				}
				else
				{
					ProjectPage.urlControlGroup.removeClass('warning');
				}
				if (ProjectPage.name) {
					ProjectPage.nameExamples.html('<a href="#">' + data.NAME + '</a>');
				}
				else {
					ProjectPage.nameInput.val(data.NAME);
					ProjectPage.checkProjectName();
				}
				ProjectPage.settingsForm.removeClass('new');
			});
		},
		saveSettings: function() {
			if (ProjectPage.nameTimerId)
				clearTimeout(ProjectPage.nameTimerId);
			if (ProjectPage.urlTimerId)
				clearTimeout(ProjectPage.urlTimerId);

			ProjectPage.saveSettingsAjax(true);

			return false;
		}
	};
}

if (siteOptions.categoryPage) {
	var CategoryPage = {
		detail: false,
		settingsForm: false,
		btnSave: false,
		nameInput: false,
		nameControlGroup: false,
		nameHelp: false,
		name: '',
		nameTimerId: 0,
		init: function () {
			this.detail = $('#category_detail');
			this.settingsForm = this.detail.find('#settings_form');
			this.btnSave = this.settingsForm.find('#save_settings');
			this.nameInput = this.settingsForm.find('#name');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.initCheck();
			this.nameInput.on('input', this.checkCategoryName);
			this.btnSave.click(this.saveSettings);
		},
		initCheck: function() {
			this.name = this.nameInput.val();
			if (!this.name)
				CategoryPage.btnSave.prop('disabled', true);
		},
		checkCategoryName: function () {
			var newName = CategoryPage.nameInput.val();
			if (CategoryPage.name == newName)
				return;

			CategoryPage.name = newName;
			if (CategoryPage.nameTimerId)
				clearTimeout(CategoryPage.nameTimerId);
			if (CategoryPage.name) {
				CategoryPage.nameControlGroup.removeClass('error');
				CategoryPage.nameHelp.text('');
				CategoryPage.btnSave.prop('disabled', false);
				CategoryPage.nameTimerId = setTimeout(CategoryPage.saveSettingsAjax, 500);
			}
			else {
				CategoryPage.nameControlGroup.addClass('error');
				CategoryPage.nameHelp.text('Введите название категории');
				CategoryPage.btnSave.prop('disabled', true);
			}
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax(CategoryPage.settingsForm, 'category_save_settings', addParams, function (data) {
				if (data.EX) {
					CategoryPage.nameControlGroup.addClass('error');
					CategoryPage.nameHelp.text('Категория с таким именем уже существует');
					CategoryPage.btnSave.prop('disabled', true);
				}
				else {
					CategoryPage.nameControlGroup.removeClass('error');
					CategoryPage.nameHelp.text('');
					CategoryPage.btnSave.prop('disabled', false);
					if (data.name)
						Titles.changeCategoryName(data.name);
				}
			});
		},
		saveSettings: function () {
			if (CategoryPage.nameTimerId)
				clearTimeout(CategoryPage.nameTimerId);

			CategoryPage.saveSettingsAjax(true);

			return false;
		}
	};

	var Words = {
		baseForm: false,
		addForm: false,
		replaceForm: false,
		weightForm: false,
		rowsDiv: false,
		replaceDiv: false,
		weightDiv: false,
		addRowBtn: false,
		replaceRowBtn: false,
		weightRowBtn: false,
		totalSpan: false,
		cols: false,
		maxColsInput: false,
		saveBaseBtn: false,
		saveAddBtn: false,
		saveReplaceBtn: false,
		saveWeightBtn: false,
		textarea: false,
		checkbox: false,
		init: function () {
			this.baseForm = $('#base_words');
			this.addForm = $('#additional_words');
			this.replaceForm = $('#replace-form');
			this.weightForm = $('#weight-form');
			this.rowsDiv = this.baseForm.find('.rows');
			this.replaceDiv = this.replaceForm.find('.rows');
			this.weightDiv = this.weightForm.find('.rows');
			this.addRowBtn = this.baseForm.find('#add_row');
			this.replaceRowBtn = this.replaceForm.find('.add-row');
			this.weightRowBtn = this.weightForm.find('.add-row');
			this.totalSpan = this.baseForm.find('#total_cnt');
			this.cols = this.rowsDiv.find('.span2');
			this.maxColsInput = this.baseForm.find('#max');
			this.saveBaseBtn = this.baseForm.find('.btn-primary');
			this.saveAddBtn = this.addForm.find('.btn-primary');
			this.saveReplaceBtn = this.replaceForm.find('.btn-primary');
			this.saveWeightBtn = this.weightForm.find('.btn-primary');
			this.textarea = this.rowsDiv.find('textarea');
			this.checkbox = this.rowsDiv.find('input');

			this.calcComboCnt();

			this.addRowBtn.click(this.addRow);
			this.replaceRowBtn.click(this.addReplaceRow);
			this.weightRowBtn.click(this.addWeightRow);
			this.maxColsInput.on('input', this.calcComboCnt);
			this.textarea.on('input', this.calcComboCnt);
			this.checkbox.click(this.calcComboCnt);
			this.saveBaseBtn.click(this.saveBase);
			this.saveAddBtn.click(this.saveAdd);
			this.saveReplaceBtn.click(this.saveReplace);
			this.saveWeightBtn.click(this.saveWeight);
		},
		addRow: function() {
			var html = '<div class="row-fluid">';
			for (var i = 0; i < 6; i++) {
				html += '<div class="span2"><label class="checkbox"><input type="checkbox"> Обязательно</label><textarea></textarea></div>';
			}
			html += '</div>';
			Words.rowsDiv.append(html);
			Words.cols = Words.rowsDiv.find('.span2');
		},
		addReplaceRow: function() {
			var html = '<div class="control-group">' +
				'<input type="text" name="from[]" value="" /> <input type="text" name="to[]" value="" /></div>';
			Words.replaceDiv.append(html);
		},
		addWeightRow: function() {
			var html = '<div class="control-group"><input type="text" name="w[]" value="" /></div>';
			Words.weightDiv.append(html);
		},
		calcComboCnt: function() {
			var reqCnt = 0;
			var words = [];
			var result = 1;
			var reqResult = 1;
			var counts = [];
			Words.cols.each(function () {
				$(this).removeClass('error');
			});
			var disabled = false;
			Words.cols.each(function () {
				var req = $(this).find('input').prop('checked');
				if (req)
					reqCnt++;
				var ta = $(this).find('textarea').val();
				var ar = ta.split('\n');
				var cnt = 0;
				for (var i = 0; i < ar.length; i++) {
					var word = ar[i];
					if (word) {
						if (words[word]) {
							words[word].addClass('error');
							$(this).addClass('error');
							disabled = true;
						}
						else {
							words[word] = $(this);
							cnt++;
						}
					}
				}
				if (cnt) {
					if (!req) {
						cnt++;
						counts.push(cnt);
					}
					else {
						reqResult *= cnt;
					}
					result *= cnt;
				}
			});
			var max = Words.maxColsInput.val();
			if (!max)
				max = 4;
			max -= reqCnt;
			if (max < 0) {
				result = 0;
			}
			else if (max == 0) {
				result = reqResult;
			}
			else if (max > 0 && counts.length > max) {
				var x = Words.f(counts, counts.length, 0, 0, 1, max);
				result = reqResult * x;
			}
			if (!reqCnt && result)
				result--;
			Words.totalSpan.text(result);
			Words.saveBaseBtn.prop('disabled', disabled);
		},
		f: function(counts, l, level, cnt, current, max) {
			if (cnt < max && level < l) {
				var x1 = Words.f(counts, l, level + 1, cnt + 1, current * (counts[level] - 1), max);
				var x2 = Words.f(counts, l, level + 1, cnt, current, max);
				return x1 + x2;
			}
			else {
				return current;
			}
		},
		saveBase: function() {
			CMN.ajax(Words.baseForm, 'category_save_base');
			return false;
		},
		saveAdd: function() {
			CMN.ajax(Words.addForm, 'category_save_additional');
			return false;
		},
		saveReplace: function() {
			CMN.ajax(Words.replaceForm, 'category_save_replace');
			return false;
		},
		saveWeight: function() {
			CMN.ajax(Words.weightForm, 'category_save_weight');
			return false;
		}
	};
}

if (siteOptions.keygroupFilters) {
	var KeyGroupList = {
		filtersForm: false,
		btnApply: false,
		pageInput: false,
		resultCont: false,
		multiNav: false,
		selectedCount: 0,
		countSpan: false,
		allCountSpan: false,
		multiAction: false,
		thisPage: false,
		allPage: false,
		togglePage: false,
		selectInputs: false,
		table: false,
		allPageChecked: false,
		init: function () {
			this.filtersForm = $('#keygroup-form');
			this.btnApply = this.filtersForm.find('.btn-primary');
			this.pageInput = this.filtersForm.find('input[name="page"]');
			this.resultCont = $('#keygroup-table');
			this.multiNav = $('#multi-nav');
			this.countSpan = this.multiNav.find('#selected_count');
			this.allCountSpan = this.multiNav.find('#all_count');
			this.multiAction = this.multiNav.find('#multi-action');
			this.thisPage = this.multiNav.find('#this_page');
			this.allPage = this.multiNav.find('#all_page');
			this.togglePage = this.multiNav.find('#toggle_page');

			this.btnApply.click(this.applyFilters);
			this.thisPage.click(this.selectThisPage);
			this.allPage.click(this.selectAllPage);
			this.togglePage.click(this.toggleThisPage);
			this.resultCont.on('click', '.pagination a', this.toPage);
			this.resultCont.on('click', '.select_item', this.selectItem);
			this.multiNav.on('click', 'li.add_mark > ul a', this.addMark);
			this.multiNav.on('click', 'li.remove_mark > ul a', this.removeMark);
			this.multiNav.on('click', 'li.remove_all_mark > a', this.removeAllMark);

			this.getKeyGroups();
		},
		applyFilters: function () {
			KeyGroupList.pageInput.val(1);
			KeyGroupList.getKeyGroups();
			return false;
		},
		getKeyGroups: function (addParam, groupAction) {
			if (groupAction) {
				var ids = '';
				if (KeyGroupList.allPageChecked)
					ids = 'all';
				else
					KeyGroupList.selectInputs.filter(':checked').each(function () {
						if (ids)
							ids += ',';
						ids += $(this).attr('id');
					});
				addParam += '&ids=' + ids;
			}
			KeyGroupList.resultCont.addClass('process');
			KeyGroupList.resultCont.removeClass('long');
			setTimeout(KeyGroupList.showOverlay, 200);
			CMN.ajax(KeyGroupList.filtersForm, 'get_keygroups_by_filter', addParam, function (data) {
				KeyGroupList.resultCont.removeClass('process');
				KeyGroupList.resultCont.html(data);
				KeyGroupList.table = KeyGroupList.resultCont.find('table');
				KeyGroupList.selectInputs = KeyGroupList.table.find('input.select_item');
				KeyGroupList.allCountSpan.text(KeyGroupList.table.data('all'));
				if (!groupAction)
					KeyGroupList.allPageChecked = false;
				KeyGroupList.calcSelected();
			}, true);
		},
		showOverlay: function() {
			KeyGroupList.resultCont.addClass('long');
		},
		toPage: function () {
			var page = $(this).data('page');
			if (page) {
				KeyGroupList.pageInput.val(page);
				KeyGroupList.getKeyGroups();
			}
			return false;
		},
		calcSelected: function() {
			if (KeyGroupList.allPageChecked)
				KeyGroupList.selectedCount = KeyGroupList.table.data('all');
			else
				KeyGroupList.selectedCount = KeyGroupList.selectInputs.filter(':checked').length;
			KeyGroupList.countSpan.text(KeyGroupList.selectedCount);
			if (KeyGroupList.selectedCount > 0)
				KeyGroupList.multiAction.removeClass('hidden');
			else
				KeyGroupList.multiAction.addClass('hidden');
		},
		selectItem: function () {
			KeyGroupList.allPageChecked = false;
			KeyGroupList.calcSelected();
		},
		selectThisPage: function () {
			KeyGroupList.allPageChecked = false;
			KeyGroupList.selectInputs.prop('checked', true);
			KeyGroupList.calcSelected();
		},
		selectAllPage: function () {
			KeyGroupList.allPageChecked = true;
			KeyGroupList.selectInputs.prop('checked', true);
			KeyGroupList.calcSelected();
		},
		toggleThisPage: function () {
			KeyGroupList.allPageChecked = false;
			var selected = KeyGroupList.selectInputs.filter(':checked');
			var empty = KeyGroupList.selectInputs.not(':checked');
			selected.prop('checked', false);
			empty.prop('checked', true);
			KeyGroupList.calcSelected();
		},
		addMark: function () {
			var markId = $(this).data('id');
			var addParam = 'action=add_mark&add_mark=' + markId;
			KeyGroupList.getKeyGroups(addParam, true);
		},
		removeMark: function () {
			var markId = $(this).data('id');
			var addParam = 'action=remove_mark&add_mark=' + markId;
			KeyGroupList.getKeyGroups(addParam, true);
		},
		removeAllMark: function () {
			var addParam = 'action=remove_all_mark';
			KeyGroupList.getKeyGroups(addParam, true);
		}
	};
}

if (siteOptions.linksetPage) {
	var Linkset = {
		form: false,
		btnSave: false,
		btnCancel: false,
		nameInput: false,
		nameControlGroup: false,
		nameHelp: false,
		name: '',
		nameTimerId: 0,
		current: false,
		urlTimerId: 0,
		titles: false,
		hrefs: false,
		rules: false,
		nameError: false,
		linksError: false,
		projectUrl: '',
		init: function () {
			this.form = $('#linkset_detail');
			this.btnSave = this.form.find('.btn-primary');
			this.btnCancel = this.form.find('.cancel');
			this.nameInput = this.form.find('#name');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.titles = this.form.find('input[name="title[]"]');
			this.hrefs = this.form.find('input[name="href[]"]');
			this.rules = this.form.find('.rules li');
			this.projectUrl = this.form.find('input[name="project_url"]').val();
			this.initCheck();
			this.checkLinks();
			this.nameInput.on('input', this.checkName);
			this.titles.on('input', this.checkTitle);
			this.hrefs.on('input', this.checkHref);
			this.btnSave.click(this.saveSettings);
			this.btnCancel.click(CMN.historyBack);
		},
		initCheck: function() {
			this.name = this.nameInput.val();
			if (!this.name)
				Linkset.btnSave.prop('disabled', true);
		},
		checkName: function () {
			var newName = Linkset.nameInput.val();
			if (Linkset.name == newName)
				return;

			Linkset.name = newName;
			if (Linkset.nameTimerId)
				clearTimeout(Linkset.nameTimerId);
			if (Linkset.name) {
				Linkset.nameControlGroup.removeClass('error');
				Linkset.nameHelp.text('');
				Linkset.nameTimerId = setTimeout(Linkset.saveSettingsAjax, 500);
				Linkset.nameError = false;
			}
			else {
				Linkset.nameControlGroup.addClass('error');
				Linkset.nameHelp.text('Введите название');
				Linkset.nameError = true;
			}
			Linkset.btnDisabled();
		},
		checkTitle: function() {
			var val = $(this).val();
			var id = $(this).attr('id').substr(4);
			var div = $('#yandex' + id);
			var a = div.children('a');
			a.text(val);
			if (val)
				div.removeClass('hidden');
			else
				div.addClass('hidden');
			Linkset.checkLinks();
		},
		checkHref: function() {
			var id = $(this).attr('id').substr(4);
			var a = $('#yandex' + id + ' a');
			var href = 'http://' + Linkset.projectUrl + $(this).val();
			a.attr('href', href);
			if (Linkset.urlTimerId)
				clearTimeout(Linkset.urlTimerId);
			Linkset.current = $(this).closest('.link-item');
			Linkset.urlTimerId = setTimeout(Linkset.getSite(), 500);
			Linkset.checkLinks();
		},
		getSite: function() {
			Linkset.current.find('.loader').addClass('inp');
			var href = Linkset.current.find('input[name="href[]"]').val();
			var info = Linkset.current.find('.info');
			info.text('');
			var url = Linkset.projectUrl + href;
			CMN.ajax(Linkset.current, 'get_site', 'url=' + url, function(data) {
				info.text(data.error);
			});
		},
		checkLinks: function() {
			var r1 = true;
			var r2 = false;
			var r3 = 0;
			var r4 = false;
			var r5 = false;
			var r6 = false;
			Linkset.titles.removeClass('error');
			Linkset.hrefs.removeClass('error');
			Linkset.rules.removeClass('text-error');
			for (var i = 0; i < 4; i++) {
				var cur = Linkset.titles.eq(i);
				var curHref = Linkset.hrefs.eq(i);
				var val = cur.val();
				if (val) {
					r1 = false;
					var l = val.length;
					if (l > 30) {
						r2 = true;
						cur.addClass('error');
					}
					r3 += l;
					var href = curHref.val();
					if (!href || href == '/') {
						curHref.addClass('error');
						r6 = true;
					}
					for (var j = i + 1; j < 4; j++) {
						var that = Linkset.titles.eq(j);
						if (that.val() == val) {
							r5 = true;
							cur.addClass('error');
							that.addClass('error');
						}
						if (href) {
							var thatHref = Linkset.hrefs.eq(j);
							if (thatHref.val() == href) {
								r4 = true;
								curHref.addClass('error');
								thatHref.addClass('error');
							}
						}
					}
				}
			}
			if (r1) {
				Linkset.rules.eq(0).addClass('text-error');
				Linkset.titles.addClass('error');
			}
			if (r5) {
				Linkset.rules.eq(1).addClass('text-error');
			}
			if (r2) {
				Linkset.rules.eq(2).addClass('text-error');
			}
			if (r3 > 66) {
				Linkset.rules.eq(3).addClass('text-error');
				Linkset.titles.addClass('error');
			}
			if (r6) {
				Linkset.rules.eq(4).addClass('text-error');
			}
			if (r4) {
				Linkset.rules.eq(5).addClass('text-error');
			}

			Linkset.linksError = r1 || r2 || r3 > 66 || r4 || r5 || r6;
			Linkset.btnDisabled();
		},
		btnDisabled: function() {
			Linkset.btnSave.prop('disabled', Linkset.linksError || Linkset.nameError);
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax(Linkset.form, 'linkset_save', addParams, function (data) {
				if (data.EX) {
					Linkset.nameControlGroup.addClass('error');
					Linkset.nameHelp.text('Набор с таким именем уже существует');
					Linkset.nameError = true;
				}
				else {
					Linkset.nameControlGroup.removeClass('error');
					Linkset.nameHelp.text('');
					Linkset.btnSave.prop('disabled', false);
					Linkset.nameError = false;
				}
				Linkset.btnDisabled();
			});
		},
		saveSettings: function () {
			if (Linkset.nameTimerId)
				clearTimeout(Linkset.nameTimerId);

			Linkset.saveSettingsAjax(true);

			return false;
		}
	};
}

if (siteOptions.vcardPage) {
	var Vcard = {
		form: false,
		btnSave: false,
		btnCancel: false,
		nameInput: false,
		nameControlGroup: false,
		nameHelp: false,
		name: '',
		nameTimerId: 0,
		init: function () {
			this.form = $('#vcard_detail');
			this.btnSave = this.form.find('.btn-primary');
			this.btnCancel = this.form.find('.cancel');
			this.nameInput = this.form.find('#name');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.initCheck();
			this.nameInput.on('input', this.checkName);
			this.btnSave.click(this.saveSettings);
			this.btnCancel.click(CMN.historyBack);
		},
		initCheck: function() {
			this.name = this.nameInput.val();
			if (!this.name)
				Vcard.btnSave.prop('disabled', true);
		},
		checkName: function () {
			var newName = Vcard.nameInput.val();
			if (Vcard.name == newName)
				return;

			Vcard.name = newName;
			if (Vcard.nameTimerId)
				clearTimeout(Vcard.nameTimerId);
			if (Vcard.name) {
				Vcard.nameControlGroup.removeClass('error');
				Vcard.nameHelp.text('');
				Vcard.btnSave.prop('disabled', false);
				Vcard.nameTimerId = setTimeout(Vcard.saveSettingsAjax, 500);
			}
			else {
				Vcard.nameControlGroup.addClass('error');
				Vcard.nameHelp.text('Введите название визитки');
				Vcard.btnSave.prop('disabled', true);
			}
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax(Vcard.form, 'vcard_save', addParams, function (data) {
				if (data.EX) {
					Vcard.nameControlGroup.addClass('error');
					Vcard.nameHelp.text('Визитка с таким именем уже существует');
					Vcard.btnSave.prop('disabled', true);
				}
				else {
					Vcard.nameControlGroup.removeClass('error');
					Vcard.nameHelp.text('');
					Vcard.btnSave.prop('disabled', false);

				}
			});
		},
		saveSettings: function () {
			if (Vcard.nameTimerId)
				clearTimeout(Vcard.nameTimerId);

			Vcard.saveSettingsAjax(true);

			return false;
		}
	};
}

if (siteOptions.templPage) {
	var Templ = {
		form: false,
		btnSave: false,
		btnCancel: false,
		nameInput: false,
		nameControlGroup: false,
		nameHelp: false,
		name: '',
		nameTimerId: 0,
		init: function () {
			this.form = $('#templ_detail');
			this.btnSave = this.form.find('.btn-primary');
			this.btnCancel = this.form.find('.cancel');
			this.nameInput = this.form.find('#name');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.initCheck();
			this.nameInput.on('input', this.checkName);
			this.btnSave.click(this.saveSettings);
			this.btnCancel.click(CMN.historyBack);
		},
		initCheck: function() {
			this.name = this.nameInput.val();
			if (!this.name)
				Templ.btnSave.prop('disabled', true);
		},
		checkName: function () {
			var newName = Templ.nameInput.val();
			if (Templ.name == newName)
				return;

			Templ.name = newName;
			if (Templ.nameTimerId)
				clearTimeout(Templ.nameTimerId);
			if (Templ.name) {
				Templ.nameControlGroup.removeClass('error');
				Templ.nameHelp.text('');
				Templ.btnSave.prop('disabled', false);
				Templ.nameTimerId = setTimeout(Templ.saveSettingsAjax, 500);
			}
			else {
				Templ.nameControlGroup.addClass('error');
				Templ.nameHelp.text('Введите название шаблона');
				Templ.btnSave.prop('disabled', true);
			}
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax(Templ.form, 'templ_save', addParams, function (data) {
				if (data.EX) {
					Templ.nameControlGroup.addClass('error');
					Templ.nameHelp.text('Шаблон с таким именем уже существует');
					Templ.btnSave.prop('disabled', true);
				}
				else {
					Templ.nameControlGroup.removeClass('error');
					Templ.nameHelp.text('');
					Templ.btnSave.prop('disabled', false);

				}
			});
		},
		saveSettings: function () {
			if (Templ.nameTimerId)
				clearTimeout(Templ.nameTimerId);

			Templ.saveSettingsAjax(true);

			return false;
		}
	};
}

if (siteOptions.keygroupPage) {
	var KeyGroup = {
		form: false,
		btnSave: false,
		btnCancel: false,
		init: function () {
			this.form = $('#keygroup_detail');
			this.btnSave = this.form.find('.btn-primary');
			this.btnCancel = this.form.find('.cancel');
			this.btnSave.click(this.saveSettings);
			this.btnCancel.click(CMN.historyBack);
		},
		saveSettingsAjax: function () {
			CMN.ajax(KeyGroup.form, 'keygroup_save', '', CMN.historyBack);
		},
		saveSettings: function () {
			KeyGroup.saveSettingsAjax();
			return false;
		}
	};
}

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
	}

	// Страница шаблонов объявлений
	if (siteOptions.templPage) {
		Templ.init();
	}

	// Страница ключевых фраз
	if (siteOptions.keygroupPage) {
		KeyGroup.init();
	}

});