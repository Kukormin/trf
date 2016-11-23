
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
			Linkset.urlTimerId = setTimeout(Linkset.getSite, 500);
			Linkset.checkLinks();
		},
		getSite: function() {
			Linkset.current.find('.loader').addClass('inp');
			var href = Linkset.current.find('input[name="href[]"]').val();
			var info = Linkset.current.find('.info');
			info.text('');
			CMN.ajax('get_site', {
				form: Linkset.current,
				post: 'url=' + Linkset.projectUrl + href,
				strategy: 2
			}, function(data) {
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
			CMN.ajax('linkset_save', {
				form: Linkset.form,
				post: addParams,
				strategy: 2,
				overlay: save
			}, function (data) {
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
