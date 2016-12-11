if (siteOptions.adPage) {
	var Ad = {
		previewTimerId: 0,
		urlTimerId: 0,
		isYandex: false,
		init: function () {
			this.form = $('#ad_detail');
			this.btnSave = this.form.find('.save-btn');
			this.btnCancel = this.form.find('.cancel');
			this.example = this.form.find('.example');
			this.urlInput = this.form.find('#url');
			this.urlLoader = this.urlInput.siblings('.loader');
			this.urlControlGroup = this.urlInput.closest('.control-group');
			this.urlHelp = this.urlInput.siblings('.help-inline');
			this.host = this.form.find('#host').val();
			this.isYandex = this.form.find('input[name=yandex]').val() == 1;

			this.names = ['title', 'title_2', 'text', 'url', 'link', 'link_2'];
			this.inputs = [];
			for (var i = 0; i < 6; i++) {
				var input = this.form.find('#' + Ad.names[i]);
				if (input.length) {
					input.data('id', i);
					this.inputs[i] = {
						input: input,
						cg: input.closest('.control-group'),
						help: input.siblings('.help-inline'),
						max: input.data('max'),
						req: input.prop('required'),
						error: false
					};
					Ad.updateInput(input);
					input.on('input', Ad.inputChange);
				}
			}

			this.preview(true);
			this.checkFields();
			this.btnSave.click(this.saveSettings);
			this.btnCancel.click(CMN.historyBack);
			this.form.find('input[name="search"]').click(this.changeType);
			this.form.on('change', 'select', this.preview);
			this.urlInput.on('input', this.checkHref);
		},
		changeType: function () {
			if (Ad.isYandex)
				Pic.toggle($(this).val());
			Ad.preview();
		},
		checkFields: function () {
			var fieldsError = false;
			for (var i = 0; i < 6; i++) {
				if (Ad.inputs[i] && Ad.inputs[i].error)
					fieldsError = true;
			}
			Ad.btnSave.prop('disabled', fieldsError);
		},
		saveSettings: function () {
			if (!Ad.checked) {
				Ad.checked = true;
				var firstInput = Ad.checkFields();
				if (firstInput)
					firstInput.focus();
			}
			if (Ad.fieldsError)
				return false;

			CMN.ajax('ad_save', {
				form: Ad.form,
				strategy: 2,
				overlay: true
			}, false);

			return false;
		},
		previewAjax: function () {
			CMN.ajax('ad_preview', {
				form: Ad.form,
				strategy: 2,
				quick: true
			}, function (data) {
				Ad.example.html(data);
			});
		},
		preview: function (now) {
			if (Ad.previewTimerId)
				clearTimeout(Ad.previewTimerId);

			if (now)
				Ad.previewAjax();
			else
				Ad.previewTimerId = setTimeout(Ad.previewAjax, 1000);
		},
		inputChange: function() {
			Ad.updateInput($(this));
			Ad.checkFields();
			Ad.preview(false);
		},
		updateInput: function(input) {
			var i = input.data('id');
			var obj = Ad.inputs[i];
			var val = input.val();
			var l = val.length;
			var r = obj.max - l;
			obj.help.text(r);
			obj.error = r < 0 || (l == 0 && obj.req);

			if (obj.error)
				obj.cg.addClass('error');
			else
				obj.cg.removeClass('error');
		},
		checkHref: function() {
			if (Ad.urlTimerId)
				clearTimeout(Ad.urlTimerId);
			Ad.urlTimerId = setTimeout(Ad.getSite, 500);
		},
		getSite: function() {
			Ad.urlLoader.addClass('inp');
			Ad.urlHelp.text('');
			CMN.ajax('get_site', {
				form: Ad.urlControlGroup,
				post: 'url=' + Ad.host + Ad.urlInput.val(),
				strategy: 2
			}, function(data) {
				if (data.error)
				{
					Ad.urlControlGroup.addClass('warning');
					Ad.urlHelp.text(data.error);
				}
				else
				{
					Ad.urlControlGroup.removeClass('warning');
				}
			});
		}
	};
}
