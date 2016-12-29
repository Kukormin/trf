if (siteOptions.categoryPage) {
	var CategoryPage = {
		detail: false,
		settingsForm: false,
		btnSave: false,
		nameInput: false,
		schemeInput: false,
		host: '',
		pathInput: false,
		resInput: false,
		nameControlGroup: false,
		nameHelp: false,
		resControlGroup: false,
		resHelp: false,
		name: '',
		nameTimerId: 0,
		urlTimerId: 0,
		init: function () {
			this.detail = $('#category_detail');
			this.settingsForm = this.detail.find('#settings_form');
			this.btnSave = this.settingsForm.find('#save_settings');
			this.nameInput = this.settingsForm.find('#name');
			this.schemeInput = this.settingsForm.find('#scheme');
			this.host = this.settingsForm.find('#host').val();
			this.pathInput = this.settingsForm.find('#path');
			this.resInput = this.settingsForm.find('#res');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.resControlGroup = this.resInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.resHelp = this.resControlGroup.find('.help-inline');
			this.initCheck();
			this.nameInput.on('input', this.checkCategoryName);
			this.schemeInput.on('input', this.checkHref);
			this.pathInput.on('input', this.checkHref);
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
		checkHref: function() {
			var res = CategoryPage.schemeInput.val() + '://' + CategoryPage.host + CategoryPage.pathInput.val();
			CategoryPage.resInput.val(res);
			if (CategoryPage.urlTimerId)
				clearTimeout(CategoryPage.urlTimerId);
			CategoryPage.urlTimerId = setTimeout(CategoryPage.getSite, 500);
		},
		getSite: function() {
			CategoryPage.resControlGroup.find('.loader').addClass('inp');
			CategoryPage.resHelp.text('');
			CMN.ajax('get_site', {
				form: CategoryPage.resControlGroup,
				post: 'url=' + CategoryPage.resInput.val(),
				strategy: 2
			}, function(data) {
				if (data.error)
				{
					CategoryPage.resControlGroup.addClass('warning');
					CategoryPage.resHelp.text(data.error);
				}
				else
				{
					CategoryPage.resControlGroup.removeClass('warning');
				}
			});
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax('category_save_settings', {
				form: CategoryPage.settingsForm,
				post: addParams,
				strategy: 2,
				overlay: save
			}, function (data) {
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
}