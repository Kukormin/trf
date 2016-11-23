if (siteOptions.projectPage) {
	var ProjectPage = {
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
			else {
				var validHost = CMN.getHost(ProjectPage.url);
				addParams = 'host=' + validHost;
			}
			CMN.ajax('project_save_settings', {
				form: ProjectPage.settingsForm,
				post: addParams,
				strategy: 2,
				overlay: save
			}, function(data) {
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
			CMN.ajax('parse_site', {
				form: ProjectPage.settingsForm,
				strategy: 2
			}, function(data) {
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